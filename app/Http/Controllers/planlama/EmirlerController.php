<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;
use Illuminate\Auth\Events\Validated;
use Carbon\Carbon;

class EmirlerController extends Controller
{

  protected function parseTarih($tarih, $default = null): Carbon
  {
    if ($tarih && strtotime($tarih)) {
      return Carbon::parse($tarih);
    }

    return $default instanceof Carbon ? $default : Carbon::now();
  }

  public function getData(Request $request)
  {
    // Normalize and validate incoming params
    $tabloRaw = strtoupper((string) $request->input('tablo', 'DETAY'));
    $isMerkezi = $request->input('isMerkezi', []);

    // Map to actual table names to avoid missing-table errors
    $tableMap = [
      'DETAY' => 'uyumsoft.OFTV_ISEMIRLERI_DETAY',
      'MASTER' => 'uyumsoft.OFTV_ISEMIRLERI_MASTER2',
      'KAPANMISLAR' => 'uyumsoft.OFTV_ISEMIRLERI_KAPANMISLAR',
    ];

    if (!array_key_exists($tabloRaw, $tableMap)) {
      return response()->json([
        'error' => 'Geçersiz tablo parametresi',
        'allowed' => array_keys($tableMap),
      ], 400);
    }

    $query = DB::connection('pgsql')->table($tableMap[$tabloRaw]);

    if ($tabloRaw === 'DETAY' && $isMerkezi !== ['Tümü']) {
      if ($isMerkezi === ['1100', '1200', '1500', '2200', '3200', '4001'])
        $query->whereNotIn('IS_MERKEZI_KODU', $isMerkezi);
      else
        $query->whereIn('IS_MERKEZI_KODU', $isMerkezi);

      $emirler = $query
        ->orderBy('hafta', 'asc')
        ->orderBy('IS_ISTASYONU_KODU', 'asc')
        ->orderBy('planlanan_baslangic', 'asc')
        ->distinct() //->limit(100)
        ->get();
    } else {
      // For non-DETAY sources, avoid ordering by columns that may not exist
      $emirler = $query
        ->distinct()
        ->get();
    }

    // Log::info('Emirler: ' . json_encode($emirler));

    if ($tabloRaw === 'MASTER') {

      $siparisler = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_ISEMIRLERI_MASTER2')
        ->get();
      // ->map(function ($siparis) {
      //   $siparis->isemri_id = trim((string) $siparis->isemri_id); // Normalize ediyoruz
      //   return $siparis;
      // });

      // Döngü içinde eşleşme yapıyoruz
      foreach ($emirler as $record) {
        // `satir_no`yu normalize ediyoruz
        $satirNo = trim((string)$record->satir_id);

        // Bellekte eşleşme yapıyoruz
        $siparis = $siparisler->first(function ($item) use ($satirNo) {
          return $item->isemri_id == $satirNo;
        });

        // Log::info('-'.$satirNo.'-');
        // Eşleşme varsa alanları ekliyoruz
        if ($siparis) {
          $record->sevk_tarihi = $siparis->sevk_tarihi;
          $record->siparis_belge_no = $siparis->siparis_belge_no;
          $record->isemri_id = $siparis->isemri_id;
          $record->sip_not1 = $siparis->sip_not1;
          $record->sip_not2 = $siparis->sip_not2;
          $record->sip_not3 = $siparis->sip_not3;
          $record->sip_not4 = $siparis->sip_not4;
          $record->sip_ozelkod1 = $siparis->sip_ozelkod1;
          $record->sip_ozelkod2 = $siparis->sip_ozelkod2;
          $record->cari_ad = $siparis->cari_ad;
        } else {
          $record->sevk_tarihi = null;
          $record->isemri_id = null;
          $record->sip_not1 = null;
          $record->sip_not2 = null;
          $record->sip_not3 = null;
          $record->sip_not4 = null;
          $record->sip_ozelkod1 = null;
          $record->sip_ozelkod2 = null;
          $record->cari_ad = null;
        }
      }
    }

    $notlar = DB::connection('pgsql_oft')
      ->table('oftt_param_notlar')
      ->get();

    return response()->json([
      'emirler' => $emirler,
      'notlar' => $notlar,
    ], 200);
  }

  public function getIsEmriKapanmislar(Request $request)
  {
    $query = DB::connection('pgsql')->table('uyumsoft.OFTV_ISEMIRLERI_KAPANMISLAR');

    $emirler = $query
      ->whereDate('kapanma_tarihi', '>=', $request->filterValue)
      ->whereDate('kapanma_tarihi', '<=', $request->filterValue1)
      ->orderBy('hafta', 'asc')
      ->orderBy('IS_ISTASYONU_KODU', 'asc')
      ->orderBy('OPERASYON_KODU', 'asc')
      ->get();

    return response()->json([
      'emirler' => $emirler,
    ], 200);
  }

  public function saveGrup(Request $request)
  {
    $satirId = $request->input('satirId');
    $grupId = $request->input('grupId');

    DB::beginTransaction();

    try {
      DB::connection('pgsql')->table('uyumsoft.prdt_worder_op_d')
        ->where('worder_op_d_id', $satirId)
        ->update(['note1' => $grupId]);

      DB::commit();
      return response()->json(['message' => 'Kayıt başarıyla yapıldı.'], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => 'Kayıt sırasında hata oluştu.', 'error' => $e->getMessage()], 500);
    }
  }

  public function updatePlanlananBaslangic(Request $request)
  {

    Log::info($request->all());

    try {
      // Gelen parametreleri al
      $updateData = $request->input('updateData');

      if (empty($updateData) || !is_array($updateData)) {
        return response()->json(['error' => 'Geçersiz veri!'], 400);
      }

      // İşlemleri başlat
      DB::beginTransaction();

      foreach ($updateData as $data) {
        if ($data['tur'] === 't') {
          DB::connection('pgsql')->table('uyumsoft.prdt_worder_op_d')
            ->where('worder_op_d_id', $data['satir_id'])
            ->update([
              'plan_start_date' => $data['planlanan_baslangic'],
              'plan_end_date' => $data['planlanan_bitis']
            ]);
        } else if ($data['tur'] === 's') {
          if (isset($data['isemriNo'])) {
            $updateFields = ['delivery_date' => $data['teslimTarih']];

            if ($data['birlesik'] === 1) {
              // Log::info(date('Y-m-d', strtotime($data['teslimTarih'] . ' -' . $data['gun'] . ' days')));
              DB::connection('pgsql')->table('uyumsoft.prdt_worder_op_d')
                ->where('worder_op_d_id', $data['satir_id'])
                ->update([
                  'plan_start_date' => date('Y-m-d', strtotime($data['teslimTarih'] . ' -' . $data['gun'] . ' days')),
                  'plan_end_date' => date('Y-m-d', strtotime($data['teslimTarih'] . ' -' . $data['gun'] . ' days'))
                ]);
            }

            DB::connection('pgsql')->table('uyumsoft.psmt_order_d')
              ->where('order_d_id', function ($query) use ($data) {
                $query->select('order_d_id')
                  ->from('uyumsoft.prdt_worder_sales_rel as wsr')
                  ->leftJoin('uyumsoft.prdt_worder_m as wm', 'wm.worder_m_id', '=', 'wsr.worder_m_id')
                  ->where('worder_no', $data['isemriNo'])
                  ->limit(1);
              })
              ->update($updateFields);
          } else {
            Log::error('isemriNo bulunamadı!', $data);
          }
        }
        try {
          // 1. Master tablodaki tarihleri de gğnxellemek için önce basla ve bitir verilerini alıyoruz
          $planDates = DB::connection('pgsql')->table('uyumsoft.prdt_worder_op_d')
            ->selectRaw('MIN(plan_start_date) as basla, MAX(plan_end_date) as bitir')
            ->where('worder_m_id', $data['isEmriID'])
            ->first();

          // Eğer veri bulunamadıysa
          if (!$planDates || (!$planDates->basla && !$planDates->bitir)) {
            return response()->json(['message' => 'Ana Kayıt bulunamadı veya tarih bilgileri eksik.'], 404);
          }

          // 2. Güncelleme işlemi
          DB::connection('pgsql')->table('uyumsoft.prdt_worder_m')
            ->where('worder_m_id', $data['isEmriID'])
            ->update([
              'plan_start_date' => $planDates->basla,
              'plan_end_date' => $planDates->bitir,
            ]);
        } catch (\Exception $e) {
          Log::info($e->getMessage());
        }
      }

      // $logService->LogKaydet("İş Emirleri", "Plan güncelleme", $request->userID, $request->ip());

      DB::commit();

      return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
    }
  }

  public function istasyonKaydet(Request $request)
  {
    try {
      $updateData = $request->input('updateData');

      if (empty($updateData) || !is_iterable($updateData)) {
        return response()->json(['error' => 'Geçersiz veri!'], 400);
      }

      DB::beginTransaction();
      // Log::info(json_encode($updateData));

      foreach ($updateData as $data) {
        $yeni_merkez = $data['ismerkezi_kodu'] ?? null;
        $yeni_istasyon = $data['istasyon_kodu'] ?? null;
        $eski_merkez = $data['isMerkeziID'] ?? null;
        $eski_istasyon = $data['istasyonID'] ?? null;
        $itemID = $data['item_id'] ?? null;
        $isemriID = $data['isemriID'] ?? null;
        $operasyonID = $data['operasyonID'] ?? null;
        $satirID = $data['satir_id'] ?? null;

        if (!$satirID || !$yeni_merkez || !$yeni_istasyon) {
          Log::error("Eksik veri: " . json_encode($data));
          continue; // Eksik veri varsa o kaydı atla
        }

        $updated = DB::connection('pgsql')->table('uyumsoft.prdt_worder_op_d')
          ->where('worder_op_d_id', $satirID)
          ->update([
            'wstation_id' => $yeni_istasyon,
            'wcenter_id' => $yeni_merkez,
          ]);

        if ($data['rotaGuncelle'] === true) {
          // Log::info("Eski: $eski_merkez - $eski_istasyon, Yeni: $yeni_merkez - $yeni_istasyon");
          // Log::info("İş Emri ID: $isemriID - $operasyonID");

          $orjKayit = DB::connection('pgsql')
            ->table('uyumsoft.prdt_worder_op_d_orj')
            ->select('wcenter_id', 'wstation_id', 'operation_id')
            ->where('worder_m_id', $isemriID)
            ->where('operation_id', $operasyonID)
            ->first();

          if (!$orjKayit) {
            Log::error("Orijinal kayıt bulunamadı! isemri_id: $isemriID, operasyon_id: $operasyonID");
            continue;
          }

          // Log::info(json_encode($orjKayit));

          $rotaID = DB::connection('pgsql')->table('uyumsoft.prdd_product_route_m')
            ->where('item_id', $itemID)
            ->value('product_route_m_id');

          if (!$rotaID) {
            Log::error("Ürün rotası bulunamadı! item_id: $itemID");
            continue;
          }

          $rota = DB::connection('pgsql')->table('uyumsoft.prdd_product_route_d')
            ->where('product_route_m_id', $rotaID)
            ->where('wcenter_id', $orjKayit->wcenter_id)
            ->where('wstation_id', $orjKayit->wstation_id)
            ->update([
              'wcenter_id' => $yeni_merkez,
              'wstation_id' => $yeni_istasyon
            ]);

          $orjDuzelt = DB::connection('pgsql')->table('uyumsoft.prdt_worder_op_d_orj')
            ->where('worder_m_id', $isemriID)
            ->where('operation_id', $operasyonID)
            ->update([
              'wcenter_id' => $yeni_merkez,
              'wstation_id' => $yeni_istasyon
            ]);
          // Log::info("Güncellendi: $rota satır.");
        }
      }

      // $logService->LogKaydet("İş Emirleri", "İstasyon Atama", $request->userID, $request->ip());

      DB::commit();
      return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Hata oluştu: " . $e->getMessage());
      return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
    }
  }

  public function RalGuncelle(Request $request)
  {
    ini_set('max_execution_time', 1500); // 5 dakika

    // $logService->LogKaydet("İş Emirleri", "RAL Kodları güncellendi", $request->userID, $request->ip());

    $records = DB::connection('pgsql')->select("
            SELECT 
                isemri_id, renk_id, renk_kodu, item_id, stok_kodu, stok_adi, upper((regexp_match(upper(stok_adi), 'RAL\\d{4}'))[1]) AS renk_code 
            FROM 
                uyumsoft.\"OFTV_ISEMIRLERI_DETAY\" 
            WHERE 
                stok_adi ~* 'RAL\\d{4}' AND renk_id ISNULL");

    foreach ($records as $record) {
      if (!empty($record->renk_code)) {
        DB::connection('pgsql')->update("
            UPDATE 
                uyumsoft.prdt_worder_m 
            SET 
                color_id = COALESCE((SELECT color_id FROM uyumsoft.invd_color WHERE regexp_replace(color_code, '\\s+', '', 'g') = ? LIMIT 1), 0)
            WHERE 
                worder_m_id = ?", [$record->renk_code, $record->isemri_id]);
      }
    }

    $recordItems = DB::connection('pgsql')->select("
            select ii.item_id , ii.item_name,id.color_id, 
            upper((regexp_match(upper(ii.item_name), 'RAL\\d{4}'))[1]) AS renk_code  from uyumsoft.invd_item ii
            left join uyumsoft.invd_bwh_item_d id on id.item_id = ii.item_id 
            where ii.item_name ~* 'RAL\\d{4}' and id.color_id = 0");

    $kayit = 0;

    foreach ($recordItems as $record) {
      if (!empty($record->renk_code)) {
        DB::connection('pgsql')->update("
            UPDATE 
                uyumsoft.invd_bwh_item_d 
            SET 
                color_id = COALESCE((SELECT color_id FROM uyumsoft.invd_color WHERE regexp_replace(color_code, '\\s+', '', 'g') = ? LIMIT 1), 0)
            WHERE 
                item_id = ?", [$record->renk_code, $record->item_id]);

        $kayit++;
      }
    }


    // Log::info("Toplam kayıt: $kayit");

    return response()->json(['message' => 'Güncelleme tamamlandı', 'updated_records' => count($records)]);
  }

  public function AksesuarKaydet(Request $request)
  {
    try {
      // Gelen parametreleri al
      $updateData = $request->input('updateData');
      Log::info($updateData);
      if (empty($updateData) || !is_array($updateData)) {
        return response()->json(['error' => 'Geçersiz veri!'], 400);
      }

      // İşlemleri başlat
      DB::beginTransaction();
      foreach ($updateData as $data) {
        if (isset($data['isemriNo'])) {
          DB::connection('pgsql')->table('uyumsoft.psmt_order_d')
            ->where('order_d_id', function ($query) use ($data) {
              $query->select('order_d_id')
                ->from('uyumsoft.prdt_worder_sales_rel as wsr')
                ->leftJoin('uyumsoft.prdt_worder_m as wm', 'wm.worder_m_id', '=', 'wsr.worder_m_id')
                ->where('worder_no', $data['isemriNo'])
                ->limit(1);
            })
            ->update(['zz_aksesuar_secim_d' => $data['aksesuar']]);
          Log::info("Aksesuar güncellendi: " . $data['aksesuar']);
        } else {
          Log::error('isemriNo bulunamadı!', $data);
        }
      }
      // $logService->LogKaydet("İş Emirleri", "Aksesuar Değişikliği", $request->userID, $request->ip());
      DB::commit();

      return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
    }
  }

  public function notKaydet(Request $request)
  {
    // Log::info($request->all());
    $satir = $request->satir_id;
    $not = $request->not;

    if ($request->alan === 'technical_note2')
      DB::connection('pgsql')->table('uyumsoft.prdt_worder_m')
        ->where('worder_no', $satir)
        ->update(['technical_note2' => $not]);
    else
      DB::connection('pgsql')->table('uyumsoft.prdt_worder_m')
        ->where('worder_no', $satir)
        ->update(['technical_note1' => $not]);

    return response()->json(['message' => 'Güncelleme başarılı'], 200);
  }

  public function AktifPasif(Request $request)
  {
    $result = $this->handleAktifPasif($request->all());

    if ($result['success']) {
      return response()->json(['message' => $result['message']], 200);
    } else {
      return response()->json(['error' => $result['error']], 422);
    }
  }

  private function handleAktifPasif(array $data)
  {
    try {
      if ($data['aktif'] === 1) {
        // Doğrulama
        $validated = validator($data, [
          'isemriID'   => 'required|integer',
          'isemriNo'   => 'required',
          'urunKodu'   => 'required',
          'urunAdi'   => 'required',
          'istasyonKodu' => 'required',
          'userID' => 'required|integer',
          'isemriMiktari' => 'required|numeric',
        ])->validate();

        // Var mı kontrol et
        $isemri = DB::connection('pgsql_oft')
          ->table('OFTT_CALISMA_BILGILERI_MONTAJ')
          ->where('IS_EMRI_ID', $validated['isemriID'])
          ->where('ISTASYON', $validated['istasyonKodu'])
          ->first();

        if ($isemri) {
          DB::connection('pgsql_oft')
            ->table('OFTT_CALISMA_BILGILERI_MONTAJ')
            ->where('IS_EMRI_ID', $validated['isemriID'])
            ->where('ISTASYON', $validated['istasyonKodu'])
            ->update([
              'IS_EMRI_NO' => $validated['isemriNo'],
              'PERSONEL' => $validated['userID'],
              'URUN_KODU' => $validated['urunKodu'],
              'URUN_ADI' => $validated['urunAdi'],
              'IS_EMRI_MIKTARI' => $validated['isemriMiktari'],
            ]);
        } else {
          DB::connection('pgsql_oft')
            ->table('OFTT_CALISMA_BILGILERI_MONTAJ')
            ->insert([
              'IS_EMRI_NO' => $validated['isemriNo'],
              'IS_EMRI_ID'   => $validated['isemriID'],
              'ISTASYON' => $validated['istasyonKodu'],
              'PERSONEL' => $validated['userID'],
              'URUN_KODU' => $validated['urunKodu'],
              'URUN_ADI' => $validated['urunAdi'],
              'TARIH' => now(),
              'IS_EMRI_MIKTARI' => $validated['isemriMiktari'],
            ]);
        }
      } else {
        DB::connection('pgsql_oft')
          ->table('OFTT_CALISMA_BILGILERI_MONTAJ')
          ->where('IS_EMRI_ID', $data['isemriID'])
          ->where('ISTASYON', $data['istasyonKodu'])
          ->delete();
      }
      return ['success' => true, 'message' => 'Güncelleme başarılı'];
    } catch (\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  public function AktifleriAl(Request $request)
  {
    $simdi = now();
    $saat1800 = Carbon::today()->setTime(23, 59);

    // Geçmişe ait açık kayıtları kapat
    $kayitlar = DB::connection('pgsql_oft')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->whereNull('DURUM_BIT_TARIHI')
      ->where(function ($query) use ($simdi, $saat1800) {
        $query->whereRaw("CAST(DURUM_BAS_TARIHI AS DATE) < ?", [$saat1800->format('Y-m-d')]);

        if ($simdi->gte($saat1800)) {
          $query->orWhere(function ($sub) use ($saat1800) {
            $sub->whereRaw("CAST(DURUM_BAS_TARIHI AS DATE) = ?", [$saat1800->format('Y-m-d')])
              ->whereRaw("DURUM_BAS_TARIHI < ?", [$saat1800->format('Y-m-d H:i:s')]);
          });
        }
      })
      ->get();

    foreach ($kayitlar as $kayit) {
      $bitTarihi = Carbon::parse($kayit->DURUM_BAS_TARIHI)->setTime(23, 59, 59);
      if ($bitTarihi > $simdi) $bitTarihi = $simdi;

      DB::connection('pgsql_oft')
        ->table('oftt_calisma_sureleri_montaj')
        ->where('id', $kayit->ID)
        ->update([
          'durum_bit_tarihi' => $bitTarihi,
          'durum_suresi' => DB::raw("EXTRACT(EPOCH FROM ('{$bitTarihi->format('Y-m-d H:i:s')}'::timestamp - DURUM_BAS_TARIHI))")
        ]);
    }

    $data = [];
    $istasyonArray = explode(',', $request->istasyon);
    Log::info($istasyonArray);
    $aktifler = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->select(
        'is_emri_id',
        'is_emri_no',
        'urun_id',
        'urun_kodu',
        'urun_adi',
        'istasyon',
        'personel_id',
        'durum',
        'durus_sebebi',
        'durum_bas_tarihi',
        'personel_sayisi',
        'guid'
      )
      ->whereIn('istasyon', $istasyonArray)
      ->whereDate('durum_bas_tarihi', now()->toDateString())
      ->whereNull('durum_bit_tarihi')
      ->orderBy('is_emri_no', 'asc')
      ->get();

    $bugun_sureler = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->select(
        'is_emri_id',
        DB::raw("SUM(CASE WHEN durum = 'ÇALIŞIYOR' THEN durum_suresi ELSE 0 END) AS cal_sure"),
        DB::raw("SUM(CASE WHEN durum = 'DURUYOR' THEN durum_suresi ELSE 0 END) AS dur_sure")
      )
      ->whereDate('durum_bas_tarihi', now()->toDateString())
      ->groupBy('is_emri_id')
      ->get()
      ->keyBy('is_emri_id');

    foreach ($aktifler as $aktif) {
      $sure = $bugun_sureler->get($aktif->is_emri_id);
      $cal_sure = $sure->cal_sure ?? 0;
      $dur_sure = $sure->dur_sure ?? 0;

      $tpl_sure = DB::connection('pgsql_oft')
        ->table('oftt_calisma_sureleri_montaj')
        ->where('is_emri_id', $aktif->is_emri_id)
        ->selectRaw("ISNULL(SUM(durum_suresi), 0) AS SURE")
        ->value('SURE');

      $veri = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
        ->where('isemri_id', $aktif->is_emri_id)
        ->selectRaw("COALESCE(operasyon_hazirlik_suresi + operasyon_suresi, 0) * 60 AS SURE, teknik_not1, teknik_not2")
        ->first();

      $kontrol = DB::connection('pgsql_oft')
        ->table('oftt_kontrol_isemri')
        ->select('is_use_quality', 'is_check_quality_opr')
        ->where('isemri_id', $aktif->IS_EMRI_ID)
        ->first() ?? (object)[
          'is_use_quality' => "0",
          'is_check_quality_opr' => "0"
        ];

      $data[] = [
        'ISTASYON' => $aktif->ISTASYON,
        'IS_EMRI_ID' => $aktif->IS_EMRI_ID,
        'IS_EMRI_NO' => $aktif->IS_EMRI_NO,
        'URUN_ID' => $aktif->URUN_ID,
        'URUN_KODU' => $aktif->URUN_KODU,
        'URUN_ADI' => $aktif->URUN_ADI,
        'PERSONEL_SAYISI' => $aktif->PERSONEL_SAYISI,
        'PLANLAMA_NOTU' => $veri->teknik_not1 ?? '',
        'URETIM_NOTU' => $veri->teknik_not2 ?? '',
        'CAL_SURE' => (int)$cal_sure,
        'DUR_SURE' => (int)$dur_sure,
        'TPL_SURE' => (int)$tpl_sure,
        'PLN_SURE' => (int)($veri->sure ?? 0),
        'SURE' => 0,
        'DURUM_BAS_TARIHI' => $aktif->DURUM_BAS_TARIHI,
        'DURUM' => $aktif->DURUM,
        'SEBEP' => $aktif->DURUS_SEBEBI,
        'PERSONEL_ID' => $aktif->PERSONEL_ID,
        'KONTROL_GEREKLI' => $kontrol->is_use_quality,
        'KONTROLCU_CAGIR' => $kontrol->is_check_quality_opr,
        'GUID' => $aktif->GUID,
      ];
    }

    return response()->json([
      'data' => $data,
    ], 200);
  }

  public function getIsEmriDetay(Request $request)
  {
    $isemri_id = $request->isemri_id;
    $depo = $request->depo;

    $query = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->select(
        'satir_id',
        'stok_adi',
        'surec',
        'planlanan_baslangic',
        'planlanan_bitis_tarihi',
        'kalan_miktar',
        'IS_ISTASYONU',
        'OPERASYON',
        'operasyon_hazirlik_suresi',
        'operasyon_suresi',
        'aksesuar',
        'CIKIS_DEPO'
      )
      ->where('isemri_id', $isemri_id);

    if ($depo != 0) {
      $query->where('CIKIS_DEPO', $depo);
    }

    $data = $query->first();

    if (!$data) {
      return response()->json([
        'message' => 'İş emri detayı bulunamadı.'
      ], 404);
    }

    $toplamSure = ($data->operasyon_hazirlik_suresi ?? 0) + ($data->operasyon_suresi ?? 0);
    $toplamIsEmri = 1;

    $malzemeler = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_BOM')
      ->select(
        'tipi',
        'user_line_no',
        'item_id',
        'stok_kodu',
        'stok_adi',
        'worder_m_id',
        'qty_base_bom',
        'qty_net',
        DB::raw('0 AS qty_prm'),
        DB::raw('0 AS qty_min_inv'),
        DB::raw('0 AS qty_max_inv'),
        DB::raw('0 AS bakiye')
      )
      ->where('worder_m_id', $isemri_id)
      ->get();

    // keyBy kullanmadan sadece dizi içinden eşleşmeleri bulmak için indexlemeden döneceğiz
    $stoklar = DB::connection('pgsql')
      ->table('uyumsoft.prdt_worder_bom_d AS wbd')
      ->join('uyumsoft.invd_item AS ite', 'wbd.item_id', '=', 'ite.item_id')
      ->leftJoin('uyumsoft.invd_bwh_item AS ibi', 'ite.item_id', '=', 'ibi.item_id')
      ->leftJoin('uyumsoft.invd_whouse AS iw', 'iw.whouse_id', '=', 'ibi.whouse_id')
      ->leftJoin('uyumsoft.prdt_worder_m AS pwm', 'pwm.worder_m_id', '=', 'wbd.worder_m_id')
      ->select(
        'wbd.worder_bom_d_id',
        'wbd.worder_m_id',
        'ite.item_id',
        'ibi.whouse_id',
        'wbd.qty_base_bom',
        'ibi.qty_prm',
        'ibi.qty_max_inv',
        'ibi.qty_min_inv',
        DB::raw('(pwm.qty - pwm.qty_man_net) AS kalan')
      )
      ->where('wbd.worder_m_id', $isemri_id)
      ->whereIn('iw.whouse_width', [1, 2, 3, 4])
      ->get();

    foreach ($malzemeler as $malzeme) {
      foreach ($stoklar as $stok) {
        if (
          $stok->whouse_id == $data->CIKIS_DEPO &&
          $stok->item_id == $malzeme->item_id
        ) {
          $malzeme->qty_prm = $stok->qty_prm;
          $malzeme->qty_min_inv = $stok->qty_min_inv;
          $malzeme->qty_max_inv = $stok->qty_max_inv;

          $bakiye = ($stok->qty_prm ?? 0) - (($stok->kalan ?? 0) * ($stok->qty_base_bom ?? 0));
          $malzeme->bakiye = round($bakiye, 2);
        }
      }
    }

    return response()->json([
      'data' => $data,
      'malzemeler' => $malzemeler,
      'toplamSure' => $toplamSure,
      'toplamIsEmri' => $toplamIsEmri,
      'toplamMalzeme' => count($malzemeler),
    ], 200);
  }

  public function getSiparisler()
  {
    $siparisler = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_MUSTERI_SIPARISLERI')
      ->select('BELGE_NO')
      ->where('ACIK_KAPALI', 'Açık')
      ->orderBy('BELGE_NO')
      ->distinct()
      ->get();

    $cariler = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_MUSTERI_SIPARISLERI')
      ->select('CARI_AD')
      ->where('ACIK_KAPALI', 'Açık')
      ->orderBy('CARI_AD')
      ->distinct()
      ->get();

    return response()->json([
      'siparisler' => $siparisler,
      'cariler' => $cariler,
      'message' => 'Veriler başarıyla alındı',
      'success' => true,
    ]);
  }

  public function getIstasyonlar()
  {
    $istasyonlar = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_IS_ISTASYONLARI')
      ->select(
        'istasyon_id',
        'istasyon_kodu',
        DB::raw("concat_ws('-', istasyon_kodu, istasyon_adi) as ist_adi") // 1200 ve istasyon_adi birleştirildi
      )
      ->where('firma_id', 2715)
      ->orderBy('istasyon_kodu')
      ->distinct()
      ->get();

    return response()->json([
      'istasyonlar' => $istasyonlar,
      'message' => 'Veriler başarıyla alındı',
      'success' => true,
    ]);
  }

  public function getFirmalar(Request $request)
  {
    // Log::info($data);

    $firmalar = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_SATINALMALAR')
      ->select('FIRMA')
      ->distinct()
      ->get();

    return response()->json([
      'firmalar' => $firmalar,
      'message' => 'Veriler başarıyla alındı',
      'success' => true,
    ]);
  }

  public function edit(string $id)
  {
    $emirler1 = DB::table('OFTV_01_EMIRLERIS')->where('ID', $id)->get();
    return response()->json($emirler1);
  }

  public function getEmirAgac(Request $request)
  {
    $query = DB::connection('pgsql')->table('uyumsoft.prdt_worder_sub');
    $query->where('worder_m_id', 7432);
    $emirler = $query->get();

    return response()->json([
      'emirler' => $emirler,
      // 'siparisler' => $siparisler,
    ], 200);
  }

  public function haftalikPivot()
  {
    $results = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->selectRaw('
            "IS_MERKEZI_ADI",
            "IS_ISTASYONU",
            EXTRACT(WEEK FROM "planlanan_bitis_tarihi")::int AS hafta,
            SUM(COALESCE("operasyon_suresi", 0)) AS toplam_dakika
        ')
      ->whereNotNull('planlanan_bitis_tarihi')
      ->whereRaw('EXTRACT(YEAR FROM "planlanan_baslangic") = 2025')
      ->groupBy('IS_MERKEZI_ADI', 'IS_ISTASYONU', DB::raw('EXTRACT(WEEK FROM "planlanan_bitis_tarihi")::int'))
      ->orderBy('IS_MERKEZI_ADI')
      ->orderBy('IS_ISTASYONU')
      ->orderBy(DB::raw('EXTRACT(WEEK FROM "planlanan_bitis_tarihi")::int'))
      ->get();

    $pivot = [];
    $idCounter = 1;

    foreach ($results as $row) {
      $key = $row->IS_MERKEZI_ADI . '-' . $row->IS_ISTASYONU;

      if (!isset($pivot[$key])) {
        $pivot[$key] = [
          'id' => $idCounter++, // ✅ Benzersiz ID
          'is_merkezi_adi' => $row->IS_MERKEZI_ADI,
          'is_istasyonu' => $row->IS_ISTASYONU,
        ];

        for ($i = 1; $i <= 52; $i++) {
          $pivot[$key]["hafta_$i"] = 0;
        }
      }

      $pivot[$key]["hafta_" . $row->hafta] = (int) $row->toplam_dakika;
    }

    return array_values($pivot); // indexed array olarak dön
  }
}
