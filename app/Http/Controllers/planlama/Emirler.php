<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;
use Illuminate\Auth\Events\Validated;
use Carbon\Carbon;

class Emirler extends Controller
{
  public function insertWorkOrder(Request $request)
  {
    // Log::info($request->all());

    try {
      DB::beginTransaction();

      // $guid = (string) \Illuminate\Support\Str::uuid();

      // 1. Troc_WorkOrder tablosuna ekle
      $isemri = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_OFTV_ISEMIRLERI_DETAY')
        ->where('isemri_id', $request->WorderMId)
        ->first();

      $cikisDepo = DB::connection('pgsql')
        ->table('uyumsoft.invd_whouse')
        ->where('whouse_id', $isemri->CIKIS_DEPO)
        ->first();

      $calismaTarih = DB::table('OFTT_CALISMA_SURELERI_MONTAJ')
        ->selectRaw('MIN(DURUM_BAS_TARIHI) as ilk_tarih, MAX(DURUM_BIT_TARIHI) as son_tarih')
        ->where('GUID', $request->guid)
        ->first();

      $startDate = $calismaTarih?->ilk_tarih ? Carbon::parse($calismaTarih->ilk_tarih) : null;

      $endDate   = $calismaTarih?->son_tarih ? Carbon::parse($calismaTarih->son_tarih) : Carbon::now();

      if ($isemri) {
        DB::table('Troc_WorkOrder')->insert([
          'Guid' => $request->guid,
          'BranchCode' => $isemri->firma_kodu,
          'worderMId' => $isemri->isemri_id,
          'WorderNo' => $isemri->isemri_no,
          'OperationNo' => $isemri->Operasyon_no ?? 0,
          'ItemCode' => $isemri->stok_kodu ?? '',
          'WstationCode' => $isemri->IS_ISTASYONU_KODU ?? '',
          'AWstationCode' => $isemri->IS_ISTASYONU_KODU ?? '',
          'StartDate' => $startDate,
          'EndDate' => $endDate,
          'QtyNet' => $request->uretimMiktari ?? 0,
          'Qty' => $request->uretimMiktari ?? 0,
          'UnitCode' => $isemri->birim_kodu,
          'ProductInputWhouseCode' => 500,
          'MaterialOutputWhouseCode' => $cikisDepo->whouse_code ?? '',
          'SemiPrdMtrWhouseCode' => $cikisDepo->whouse_code ?? '',
          'Note1' => $isemri->sip_not1 ?? '',
        ]);
      }

      // Log::info('..: ' . json_encode($isemri));
      // 2. Troc_EmployeeRuntime tablosuna ekle
      if ($request->guid) {

        $personeller = DB::table('oftt_aktif_ekipler')
          ->where('guid', $request->guid)
          ->get();

        foreach ($personeller as $personel) {
          $startTime = $this->parseTarih($personel->start_work_time);
          $endTime = $this->parseTarih($personel->end_work_time, Carbon::now());

          DB::table('Troc_EmployeeRuntime')->insert([
            'WorkOrderGuid' => $request->guid,
            'EmployeeNo' => $personel->sicil_no,
            'EmployeeName' => $personel->personel_full_name,
            'StartDate' => $startTime,
            'EndDate' => $endTime,
            'EmpWorkTimeAsSeconds' => (int) $personel->duration,
          ]);
        }
      }
      // 3. Troc_WorderBreak tablosuna ekle
      if ($request->guid) {
        // Log::info('..: ' . $request->guid);
        $duruslar = DB::table('OFTT_CALISMA_SURELERI_MONTAJ')
          ->where('GUID', $request->guid)
          ->where('DURUM', 'DURUYOR')
          ->orderBy('DURUM_BAS_TARIHI', 'asc')
          ->get();

        // Log::info('Duruşlar: ' . json_encode($duruslar));

        if (!$duruslar->isEmpty()) {
          foreach ($duruslar as $durus) {
            // $startTimeBreak = $this->parseTarih($durus->DURUM_BAS_TARIHI);
            // $endTimeBreak   = $this->parseTarih($durus->DURUM_BIT_TARIHI);
            $startTimeBreak = $durus->DURUM_BAS_TARIHI ? Carbon::parse($durus->DURUM_BAS_TARIHI) : null;
            $endTimeBreak   = $durus->DURUM_BIT_TARIHI ? Carbon::parse($durus->DURUM_BIT_TARIHI) : Carbon::now();
            // Log::info($request->guid . ' Tarihler: ' . $startTimeBreak . ' - ' . $endTimeBreak);

            $personeller = DB::connection('sqlsrv')
              ->table('oftt_aktif_ekipler')
              ->where('guid', $request->guid)
              ->whereRaw("CAST(start_work_time AS DATETIME) <= ?", [$startTimeBreak->format('Y-m-d H:i:s')])
              ->where(function ($query) use ($endTimeBreak) {
                $query->whereRaw("CAST(end_work_time AS DATETIME) >= ?", [$endTimeBreak->format('Y-m-d H:i:s')])
                  ->orWhereNull('end_work_time');
              })
              ->get();

            // Log::info('Personeller: ' . json_encode($personeller));
            $lineNo = 0;

            // Log::info('Line No: ' . $lineNo);
            // Log::info('StartDate: ' . $startTimeBreak);
            // Log::info('EndDate: ' . $endTimeBreak);
            // Log::info('Durus: ' . $durus->DURUS_SEBEBI_KODU);
            // Log::info('CitizenshipNo: ' . $personel->citizenship_no);
            // Log::info('NoteLarge: ' . 'test');
            // Log::info('NoteLarge1: ' . 'test');
            // Log::info('NoteLarge2: ' . 'test');

            foreach ($personeller as $personel) {
              $lineNo += 10;
              DB::table('Troc_WorderBreak')->insert([
                'WorkOrderGuid' => $request->guid,
                'BreakLineNo' => $lineNo,
                'StartDate' => $startTimeBreak,
                'EndDate' => $endTimeBreak,
                'BreakReasonCode' => $durus->DURUS_SEBEBI_KODU,
                'CitizenshipNo' => $personel->citizenship_no,
                'NoteLarge' => 'test',
                'NoteLarge1' => 'test',
                'NoteLarge2' => 'test',
                'NoteLarge3' => 'test',
              ]);
            }
          }
        }
      }

      // Log::info('..: ' . json_encode($isemri));

      DB::commit();
      return response()->json(['success' => true]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
  }

  protected function parseTarih($tarih, $default = null): Carbon
  {
    if ($tarih && strtotime($tarih)) {
      return Carbon::parse($tarih);
    }

    return $default instanceof Carbon ? $default : Carbon::now();
  }

  public function IhtiyacHesapla(Request $request)
  {
    ini_set('max_execution_time', 1500); // 5 dakika

    // Log::info($request->all());
    try {
      $anadepolar = DB::connection('pgsql')
        ->table('uyumsoft.invd_whouse')
        ->select('whouse_id')
        ->where('whouse_width', '1')
        ->get();

      $satinalmasiparisleri = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_tumsiparisler')
        ->select('STOK_HIZMET_ID', 'MIKTAR', 'KALAN_MIKTAR', 'satici_adi')
        ->where('purchase_sales', '1')
        ->where('order_status', '1')
        ->where('FIRMA', 'CANOVATE')
        ->get();

      $talepler = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_tumtalepler')
        ->select('item_id', 'miktar')
        ->where('ONAY_DURUMU', 'Onaylandi')
        ->get();


      $emirler = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
        ->select(
          'isemri_id',
          'isemri_no',
          'stok_id',
          'stok_kodu',
          'stok_adi',
          'CIKIS_DEPO',
          'siparis_belge_no',
          'cari_ad',
          DB::raw("concat_ws('-', \"IS_MERKEZI_KODU\", \"IS_MERKEZI_ADI\") as mrk_adi"),
          DB::raw('SUM(isemri_miktari) AS miktar'),
          DB::raw('SUM(kalan_miktar) AS kalan'),
          DB::raw('0 AS stok'),
          DB::raw('0 AS ana_depo'),
          DB::raw('0 AS diger_depo')
        )
        ->whereDate('planlanan_bitis_tarihi', '>=', $request->filterValue)
        ->whereDate('planlanan_bitis_tarihi', '<=', $request->filterValue1)
        ->when($request->merkezID, function ($query, $merkezID) {
          return $query->where('IS_MERKEZI_ID', $merkezID);
        })
        ->when($request->istasyonID, function ($query, $istasyonID) {
          $istasyonArray = explode(',', $istasyonID);
          return $query->whereIn('IS_ISTASYONU_ID', $istasyonArray);
        })
        ->when($request->siparis, function ($query, $siparis) {
          return $query->where('siparis_belge_no', $siparis);
        })
        ->when($request->cari, function ($query, $cari) {
          return $query->where('cari_ad', $cari);
        })
        ->where('is_sub_worder', 0)
        ->where('Rotadaki_Son_Operasyon', 1)
        ->groupBy('isemri_id', 'isemri_no', 'stok_id', 'stok_kodu', 'stok_adi', 'CIKIS_DEPO', 'siparis_belge_no', 'cari_ad', 'IS_MERKEZI_KODU', 'IS_MERKEZI_ADI')
        ->orderBy('stok', 'asc')
        ->distinct()
        ->get();

      $bulkUpdates = [];
      $hangiIsemirleri = [];

      foreach ($emirler as $emir) {
        $liste = DB::connection('pgsql')
          ->table('uyumsoft.OFTV_ISEMIRLERI_MALZEMELER')
          ->where('worder_m_id', $emir->isemri_id)
          ->where('whouse_id', $emir->CIKIS_DEPO)
          ->get();

        foreach ($liste as $list) {
          $existingKey = array_search($list->item_id, array_column($bulkUpdates, 'item_id'));
          $existingKey1 = array_search($list->worder_m_id, array_column($hangiIsemirleri, 'stok_id'));
          // $filteredResults = array_filter($hangiIsemirleri, function ($item) use ($list) {
          //     return isset($item['isemri_id'], $item['stok_id']) &&
          //            $item['isemri_id'] == $list->worder_m_id &&
          //            $item['stok_id'] == $list->item_id;
          // });

          // $existingKey1 = !empty($filteredResults) ? key($filteredResults) : null;

          $hesapMiktari = ($list->qty_net / $emir->miktar) * $emir->kalan;

          if ($existingKey1 !== false) {
            $hangiIsemirleri[$existingKey1]['isemri_miktari'] += $emir->miktar;
          } else {
            $hangiIsemirleri[] = [
              'item_id' => $list->item_id,
              'isemri_id' => $list->worder_m_id,
              'isemri_no' => $emir->isemri_no,
              'siparis_belge_no' => $emir->siparis_belge_no,
              'cari_ad' => $emir->cari_ad,
              'isemri_miktari' => $emir->miktar,
              'kalan' => $emir->kalan,
              'stok_id' => $emir->stok_id,
              'stok_kodu' => $emir->stok_kodu,
              'stok_adi' => $emir->stok_adi,
            ];
          }

          if ($existingKey !== false) {
            $bulkUpdates[$existingKey]['isemri_miktari'] += $emir->miktar;
            $bulkUpdates[$existingKey]['kalan'] += $emir->kalan;
            $bulkUpdates[$existingKey]['ihtiyac'] += $hesapMiktari;
            $bulkUpdates[$existingKey]['dongu'] += 1;
          } else {
            $bulkUpdates[] = [
              'item_id' => $list->item_id,
              'tipi' => $list->tipi,
              'stok_kodu' => $list->stok_kodu,
              'stok_adi' => $list->stok_adi,
              'mrk_adi' => $emir->mrk_adi,
              'isemri_miktari' => $emir->miktar,
              'kalan' => $emir->kalan,
              'ihtiyac' => $hesapMiktari,
              'stok' => $list->qty_prm,
              'ana_depo' => 0,
              'diger_depo' => 0,
              'satinalma' => 0,
              'satinalmapersoneli' => '',
              'talepler' => 0,
              'bakiye' => 0,
              'dongu' => 1,
            ];
          }
        }
      }

      foreach ($bulkUpdates as &$list) {
        $anaDepo = DB::connection('pgsql')
          ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
          ->where('item_id', $list['item_id'])
          ->whereIn('whouse_id', $anadepolar->pluck('whouse_id')->toArray())
          ->sum('qty_prm');

        if ($anaDepo > 0) {
          $list['ana_depo'] = $anaDepo ?? 0;
        }
      }

      unset($list);

      foreach ($bulkUpdates as &$list) {
        $digerDepo = DB::connection('pgsql')
          ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
          ->where('item_id', $list['item_id'])
          ->where('whouse_id', '!=', $emir->CIKIS_DEPO)
          ->whereNotIn('whouse_id', $anadepolar->pluck('whouse_id')->toArray())
          ->sum('qty_prm');

        if ($digerDepo > 0) {
          $list['diger_depo'] = $digerDepo ?? 0;
        }

        $satinalma = $satinalmasiparisleri->where('STOK_HIZMET_ID', $list['item_id'])->sum('KALAN_MIKTAR');
        $satinalmapersoneli = $satinalmasiparisleri->where('STOK_HIZMET_ID', $list['item_id'])->pluck('satici_adi')->first();

        if ($satinalma > 0) {
          $list['satinalma'] = $satinalma ?? 0;
          $list['satinalmapersoneli'] = $satinalmapersoneli ?? '';
        }

        $talep = $talepler->where('item_id', $list['item_id'])->sum('miktar');
        if ($talep > 0) {
          $list['talepler'] = $talep ?? 0;
        }

        $list['bakiye'] = ($list['ana_depo'] + $list['stok']) - $list['ihtiyac'];
      }
      unset($list);

      return response()->json([
        'message' => 'İndirimler başarıyla güncellendi!',
        'emirler' => $bulkUpdates,
        'dagilim' => $hangiIsemirleri,
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'error' => 'İşlem sırasında hata oluştu!',
        'details' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
      ], 500);
    }
  }

  public function IhtiyacHesaplaSiparis(Request $request)
  {
    ini_set('max_execution_time', 1500); // 5 dakika

    try {
      // Ana depo listesi
      $anadepolar = DB::connection('pgsql')
        ->table('uyumsoft.invd_whouse')
        ->select('whouse_id')
        ->where('whouse_width', '1')
        ->get();
      $anaDepoIds = $anadepolar->pluck('whouse_id')->toArray();

      // Satın alma siparişleri
      $satinalmasiparisleri = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_tumsiparisler')
        ->select('STOK_HIZMET_ID', 'MIKTAR', 'KALAN_MIKTAR', 'satici_adi')
        ->where('purchase_sales', '1')
        ->where('order_status', '1')
        ->where('FIRMA', 'CANOVATE')
        ->get()
        ->groupBy('STOK_HIZMET_ID');

      // Talepler
      $talepler = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_tumtalepler')
        ->select('item_id', 'miktar')
        ->where('ONAY_DURUMU', 'Onaylandi')
        ->get()
        ->groupBy('item_id');

      // Depoların tümünü önceden al
      $digerDepoVerileri = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
        ->select('item_id', 'whouse_id', DB::raw('SUM(qty_prm) as qty'))
        ->groupBy('item_id', 'whouse_id')
        ->get();

      // Müşteri siparişleri
      $siparisler = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_MUSTERI_SIPARISLERI')
        ->select(
          'tipi',
          'STOK_HIZMET_ID',
          'STOK_HIZMET_KOD',
          'STOK_HIZMET_AD AS item_name',
          DB::raw('SUM("MIKTAR") AS miktar'),
          DB::raw('SUM("REZERVLI_MIKTAR") AS rezerv'),
          DB::raw('SUM("KALAN_MIKTAR") AS kalan')
        )
        ->whereBetween('teslim_tarihi', [
          Carbon::parse($request->filterValue)->startOfDay(),
          Carbon::parse($request->filterValue1)->endOfDay()
        ])
        ->where('ACIK_KAPALI', 'Açık')
        ->when($request->siparis, fn($query, $siparis) => $query->where('BELGE_NO', $siparis))
        ->when($request->cari, fn($query, $cari) => $query->where('CARI_AD', $cari))
        ->groupBy('tipi', 'STOK_HIZMET_ID', 'STOK_HIZMET_KOD', 'STOK_HIZMET_AD')
        // ->orderBy('STOK_HIZMET_AD')
        ->get();

      $musteriSiparisleri = [];

      foreach ($siparisler as $siparis) {
        $itemId = $siparis->STOK_HIZMET_ID;

        // Ana depo miktarı
        $anaDepo = $digerDepoVerileri
          ->where('item_id', $itemId)
          ->whereIn('whouse_id', $anaDepoIds)
          ->sum('qty');

        // Diğer depo miktarı
        $digerDepo = $digerDepoVerileri
          ->where('item_id', $itemId)
          ->whereNotIn('whouse_id', $anaDepoIds)
          ->sum('qty');

        // Satın alma ve talepler
        $satinalma = $satinalmasiparisleri->get($itemId)?->sum('KALAN_MIKTAR') ?? 0;
        $talep = $talepler->get($itemId)?->sum('miktar') ?? 0;

        $musteriSiparisleri[] = [
          'tipi' => $siparis->tipi,
          'item_id' => $itemId,
          'item_code' => $siparis->STOK_HIZMET_KOD,
          'item_name' => $siparis->item_name,
          'miktar' => $siparis->miktar,
          'rezerv' => $siparis->rezerv,
          'kalan' => $siparis->kalan - $siparis->rezerv,
          'stok' => 0,
          'ana_depo' => $anaDepo - $siparis->rezerv,
          'diger_depo' => $digerDepo,
          'satin_alma' => $satinalma,
          'talep' => $talep,
          'bakiye' => ($anaDepo + $talep) - $siparis->kalan,
        ];
      }

      return response()->json([
        'message' => 'İhtiyaç hesaplama tamamlandı!',
        'emirler' => $musteriSiparisleri,
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'error' => 'İşlem sırasında hata oluştu!',
        'details' => $e->getMessage()
      ], 500);
    }
  }

  public function EksikKontrolu(Request $request)
  {
    ini_set('max_execution_time', 300); // 5 dakika

    DB::beginTransaction();
    // Log::info($request->all());
    try {
      $emirler = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
        ->where('IS_ISTASYONU_ID', $request->istasyon)
        ->where('hafta', $request->hafta)
        ->orderBy('planlanan_baslangic', 'asc')
        ->get();

      foreach ($emirler as $emir) {
        // Log::info(json_encode($request->all()));
        // Log::info(json_encode($emir));
        if ($request->depolar === 'false') {
          // Log::info("Kendi Depoları");
          $liste = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_ISEMIRLERI_MALZEMELER')
            ->select(
              'item_id',
              DB::raw('AVG(qty_net) AS ihtiyac'),
              DB::raw('SUM(qty_prm) AS stok'),
              DB::raw('SUM(qty_prm) - SUM(qty_net) AS bakiye')
            )
            ->where('worder_m_id', $emir->isemri_id)
            ->where('whouse_id', $emir->CIKIS_DEPO)
            ->groupBy('item_id')
            ->distinct()
            ->get();
        } else {
          // Log::info("Diğer Depolar");
          $liste = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_ISEMIRLERI_MALZEMELER')
            ->select(
              'item_id',
              DB::raw('AVG(qty_net) AS ihtiyac'),
              DB::raw('SUM(qty_prm) AS stok'),
              DB::raw('SUM(qty_prm) - AVG(qty_net) AS bakiye')
            )
            ->where('worder_m_id', $emir->isemri_id)
            ->whereIn('whouse_width', [1, 2, 4])
            ->groupBy('item_id')
            ->distinct()
            ->get();
        }

        $list = $liste->where('bakiye', '<', 0)->count();

        $emir->eksikler = $list > 0 ? '1' : '0';
      }

      DB::commit();

      return response()->json([
        'message' => 'İndirimler başarıyla güncellendi!',
        'emirler' => $emirler,
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => 'İşlem sırasında hata oluştu!', 'details' => $e->getMessage()], 500);
    }
  }

  public function getUretimPerformans(Request $request)
  {
    $istasyon = $request->istasyon;
    // Log::info($istasyon);

    // $bugun = Carbon::today()->toDateString(); // '2025-07-21'

    $toplam = DB::connection('pgsql')
      ->table('OFTV_ISEMIRLERI_DETAY')
      ->where('IS_ISTASYONU_ID', $istasyon)
      ->whereDate('planlanan_bitis_tarihi', '=', now())
      ->sum('kalan_miktar');

    // Log::info($toplam);

    $tarih = Carbon::today()->toDateString();
    // Log::info('Tarih: ' . $tarih);
    // 1. URUN_ID listesini al
    $urunIDs = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->whereDate('DURUM_BAS_TARIHI', '=', $tarih)
      ->distinct()
      ->pluck('URUN_ID')
      ->toArray();

    if (empty($urunIDs)) {
      return response()->json([
        'bugunKalanToplam' => $toplam,
        'data' => [],
        'message' => 'Seçilen tarihte üretim yok.',
      ]);
    }

    $meco = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->selectRaw('
            ISNULL(SUM(
                CASE
                    WHEN DURUM IN (\'ÇALIŞIYOR\', \'HAZIRLIK\') THEN 
                        CASE 
                            WHEN DURUM_SURESI = 0 THEN DATEDIFF(SECOND, DURUM_BAS_TARIHI, GETDATE())
                            ELSE DURUM_SURESI 
                        END
                END
            ) / 60.0, 0) AS CALISMA,
            
            ISNULL(SUM(
                CASE
                    WHEN DURUM = \'DURUYOR\' THEN 
                        CASE 
                            WHEN DURUM_SURESI = 0 THEN DATEDIFF(SECOND, DURUM_BAS_TARIHI, GETDATE())
                            ELSE DURUM_SURESI 
                        END
                END
            ) / 60.0, 0) AS DURUS
        ')
      ->whereDate('DURUM_BAS_TARIHI', '=', now()->subDays(0)->toDateString())
      ->where('ISTASYON', $istasyon)
      ->first();

    // 2. Paket miktarlarını getir
    $paketMiktarlari = DB::connection('pgsql')
      ->table('uyumsoft.invt_package_tra_d as ipd')
      ->select('ipd.item_id', DB::raw('SUM(ipd.qty) as paket_miktari'))
      ->join('uyumsoft.invt_package_tra_m as ipm', 'ipm.package_tra_m_id', '=', 'ipd.package_tra_m_id')
      ->whereDate('ipm.doc_date', '=', $tarih)
      ->where('ipm.package_operation_type', 1)
      ->whereIn('ipd.item_id', $urunIDs)
      ->groupBy('ipd.item_id')
      ->get();

    // Log::info('Paket Miktarları: ' . $paketMiktarlari);

    return response()->json([
      'paketler' => $paketMiktarlari,
      'bugunKalanToplam' => $toplam,
      'calisma' => round($meco->CALISMA, 2),
      'durus' => round($meco->DURUS, 2),
    ], 200);
  }

  public function getHaftalikGunlukToplamPaketMiktarlari(Request $request)
  {
    $baslangic = $request->input('baslangic');
    $bitis = $request->input('bitis');
    $istasyon = $request->istasyon;

    if (!$baslangic || !$bitis) {
      Carbon::setWeekStartsAt(Carbon::MONDAY);
      $baslangic = Carbon::now()->startOfWeek()->toDateString();
      $bitis = Carbon::now()->endOfWeek()->toDateString();
    }

    $urunIDs = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->whereBetween(DB::raw('CAST(DURUM_BAS_TARIHI AS DATE)'), [$baslangic, $bitis])
      ->distinct()
      ->pluck('URUN_ID')
      ->toArray();

    // Log::info('Haftalık Paket Miktarları: Başlangıç: ' . $baslangic . ', Bitiş: ' . $bitis);

    $gunlukToplamlar = DB::connection('pgsql')
      ->table('uyumsoft.invt_package_tra_d as ipd')
      ->select(
        DB::raw('CAST(ipm.doc_date AS DATE) as gun'),
        DB::raw('SUM(ipd.qty) as toplam_paket_miktari')
      )
      ->join('uyumsoft.invt_package_tra_m as ipm', 'ipm.package_tra_m_id', '=', 'ipd.package_tra_m_id')
      ->whereBetween(DB::raw('CAST(ipm.doc_date AS DATE)'), [$baslangic, $bitis])
      ->where('ipm.package_operation_type', 1)
      ->whereIn('ipd.item_id', $urunIDs)
      ->groupBy(DB::raw('CAST(ipm.doc_date AS DATE)'))
      ->orderBy('gun')
      ->get();

    return response()->json([
      'data' => $gunlukToplamlar,
      'hafta' => [$baslangic, $bitis]
    ]);
  }

  public function getUretimData(Request $request)
  {
    $istasyon = $request->istasyon;
    // $istasyonArray = explode(',', $istasyon);
    // Log::info($istasyon);

    $query = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->where('IS_ISTASYONU_ID', (int)$istasyon)
      //->whereDate('planlanan_bitis_tarihi', '=', Carbon::now()->toDateString())
      ->orderBy('planlanan_baslangic', 'asc')
      ->distinct()
      ->get();

    $toplamEmir = $query->count();

    // Log::info($bugunKalanToplam);

    return response()->json([
      'emirler' => $query,
      'toplamEmir' => $toplamEmir,
    ], 200);
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

    $notlar = DB::connection('sqlsrv')
      ->table('PARAM_NOTLAR')
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

  public function VerileriAl(Request $request)
  {
    // Log::info($request->all());
    $istasyonArray = explode(',', $request->istasyon);

    $durusSebepleri = DB::connection('pgsql')
      ->table('uyumsoft.prdd_break_reason')
      ->select('break_reason_code', 'description')
      ->where('co_id', '2715')
      ->Where('ispassive', 0)
      ->orderBy("description", "asc")
      ->get();

    // Log::info($durusSebepleri);

    return response()->json([
      'durusSebepleri' => $durusSebepleri,
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

  public function updatePlanlananBaslangic(Request $request, LogService $logService)
  {

    // Log::info($request->all());

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

      $logService->LogKaydet("İş Emirleri", "Plan güncelleme", $request->userID, $request->ip());

      DB::commit();

      return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
    }
  }

  public function istasyonKaydet(Request $request, LogService $logService)
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

      $logService->LogKaydet("İş Emirleri", "İstasyon Atama", $request->userID, $request->ip());

      DB::commit();
      return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Hata oluştu: " . $e->getMessage());
      return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
    }
  }

  public function RalGuncelle(Request $request, LogService $logService)
  {
    ini_set('max_execution_time', 1500); // 5 dakika

    $logService->LogKaydet("İş Emirleri", "RAL Kodları güncellendi", $request->userID, $request->ip());

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

  public function AksesuarKaydet(Request $request, LogService $logService)
  {
    try {
      // Gelen parametreleri al
      $updateData = $request->input('updateData');

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
        } else {
          Log::error('isemriNo bulunamadı!', $data);
        }
      }
      $logService->LogKaydet("İş Emirleri", "Aksesuar Değişikliği", $request->userID, $request->ip());
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
        $isemri = DB::connection('sqlsrv')
          ->table('OFTT_CALISMA_BILGILERI_MONTAJ')
          ->where('IS_EMRI_ID', $validated['isemriID'])
          ->where('ISTASYON', $validated['istasyonKodu'])
          ->first();

        if ($isemri) {
          DB::connection('sqlsrv')
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
          DB::connection('sqlsrv')
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
        DB::connection('sqlsrv')
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
    $kayitlar = DB::connection('sqlsrv')
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

      DB::connection('sqlsrv')
        ->table('OFTT_CALISMA_SURELERI_MONTAJ')
        ->where('ID', $kayit->ID)
        ->update([
          'DURUM_BIT_TARIHI' => $bitTarihi,
          'DURUM_SURESI' => DB::raw("DATEDIFF(SECOND, DURUM_BAS_TARIHI, '{$bitTarihi->format('Y-m-d H:i:s')}')")
        ]);
    }

    $data = [];
    $istasyonArray = explode(',', $request->istasyon);
    Log::info($istasyonArray);
    $aktifler = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->select(
        'IS_EMRI_ID',
        'IS_EMRI_NO',
        'URUN_ID',
        'URUN_KODU',
        'URUN_ADI',
        'ISTASYON',
        'PERSONEL_ID',
        'DURUM',
        'DURUS_SEBEBI',
        'DURUM_BAS_TARIHI',
        'PERSONEL_SAYISI',
        'GUID'
      )
      ->whereIn('ISTASYON', $istasyonArray)
      ->whereDate('DURUM_BAS_TARIHI', now()->toDateString())
      ->whereNull('DURUM_BIT_TARIHI')
      ->orderBy('IS_EMRI_NO', 'asc')
      ->get();

    $bugun_sureler = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->select(
        'IS_EMRI_ID',
        DB::raw("SUM(CASE WHEN DURUM = 'ÇALIŞIYOR' THEN DURUM_SURESI ELSE 0 END) AS CAL_SURE"),
        DB::raw("SUM(CASE WHEN DURUM = 'DURUYOR' THEN DURUM_SURESI ELSE 0 END) AS DUR_SURE")
      )
      ->whereDate('DURUM_BAS_TARIHI', now()->toDateString())
      ->groupBy('IS_EMRI_ID')
      ->get()
      ->keyBy('IS_EMRI_ID');

    foreach ($aktifler as $aktif) {
      $sure = $bugun_sureler->get($aktif->IS_EMRI_ID);
      $cal_sure = $sure->CAL_SURE ?? 0;
      $dur_sure = $sure->DUR_SURE ?? 0;

      $tpl_sure = DB::connection('sqlsrv')
        ->table('OFTT_CALISMA_SURELERI_MONTAJ')
        ->where('IS_EMRI_ID', $aktif->IS_EMRI_ID)
        ->selectRaw("ISNULL(SUM(DURUM_SURESI), 0) AS SURE")
        ->value('SURE');

      $veri = DB::connection('pgsql')
        ->table('OFTV_ISEMIRLERI_DETAY')
        ->where('isemri_id', $aktif->IS_EMRI_ID)
        ->selectRaw("COALESCE(operasyon_hazirlik_suresi + operasyon_suresi, 0) * 60 AS SURE, teknik_not1, teknik_not2")
        ->first();

      $kontrol = DB::connection('sqlsrv')
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

  public function getAktifCalismalar()
  {
    $aktifler = DB::connection('sqlsrv')
      ->table('oftt_kontrol_isemri')
      ->join('PARAM_ISTASYON', 'PARAM_ISTASYON.wstation_id', '=', 'oftt_kontrol_isemri.istasyon_id')
      ->where('oftt_kontrol_isemri.is_open', 1)
      ->select(
        'oftt_kontrol_isemri.id',
        'oftt_kontrol_isemri.isemri_no',
        'oftt_kontrol_isemri.item_code',
        'oftt_kontrol_isemri.item_name',
        'PARAM_ISTASYON.TANIM',
        'oftt_kontrol_isemri.istasyon_id',
        'oftt_kontrol_isemri.item_id',
        'oftt_kontrol_isemri.is_active',
        'oftt_kontrol_isemri.isemri_id'
      )
      ->get();

    return response()->json($aktifler);
  }

  public function DurumlariAl(Request $request)
  {
    // Log::info($request->all());

    $durumlar = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->select('ISTASYON', 'IS_EMRI_ID', 'IS_EMRI_NO', 'URUN_KODU', 'URUN_ADI', 'DURUM', 'DURUM_BAS_TARIHI')
      ->whereNull('DURUM_BIT_TARIHI')
      ->where('ISTASYON', $request->istasyon)
      ->get();

    $sureler = DB::connection('sqlsrv')
      ->table('OFTV_GNLK_SURELERI_AL')
      ->get();

    return response()->json([
      'sureler' => $sureler,
      'durumlar' => $durumlar,
    ], 200);
  }

  public function PersonelSayisiKaydet(Request $request)
  {
    $islem = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->where('ISTASYON', $request->istasyonKodu)
      ->where('IS_EMRI_ID', $request->isEmriId)
      ->where('PERSONEL_ID', $request->userId)
      ->where('DURUM_BIT_TARIHI', null)
      ->orderBy('ID', 'desc')
      ->first();

    if ($islem) {
      DB::connection('sqlsrv')
        ->table('OFTT_CALISMA_SURELERI_MONTAJ')
        ->where('Id', $islem->ID)
        ->update([
          'PERSONEL_SAYISI' => $request->personelSayisi,
        ]);
    }

    return response()->json(['message' => 'Güncelleme başarılı'], 200);
  }

  public function DurusKaydet(Request $request)
  {
    // Log::info($request->all());

    $islem = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->where('ISTASYON', $request->istasyonKodu)
      ->where('IS_EMRI_ID', $request->isEmriId)
      ->where('PERSONEL_ID', $request->userId)
      ->where('DURUM_BIT_TARIHI', null)
      ->orderBy('ID', 'desc')
      ->first();

    if ($islem) {
      DB::connection('sqlsrv')
        ->table('OFTT_CALISMA_SURELERI_MONTAJ')
        ->where('Id', $islem->ID)
        ->update([
          'DURUS_SEBEBI' => $request->selectedDurus['description'],
          'DURUS_SEBEBI_KODU' => $request->selectedDurus['break_reason_code'],
        ]);
    }

    return response()->json(['message' => 'Güncelleme başarılı'], 200);
  }

  public function DurumKaydet(Request $request)
  {
    //Log::info($request->all());
    $tarih = now();
    $baglanti = DB::connection('sqlsrv');


    $durum = $request->durum;
    $durusSebebi = is_array($request->selectedDurus) ? $request->selectedDurus['description'] : $request->selectedDurus;
    $durusSebebiKodu = is_array($request->selectedDurus) ? $request->selectedDurus['break_reason_code'] : $request->selectedDurus;


    // if ($request->durum === 'SON DURUMA DÖN') {
    //   $durumAl = DB::connection('sqlsrv')
    //     ->table('OFTT_CALISMA_SURELERI_MONTAJ')
    //     ->select('DURUM', 'DURUS_SEBEBI', 'DURUS_SEBEBI_KODU')
    //     ->where('GUID', $request->guid)
    //     ->whereNotNull('DURUM_BIT_TARIHI')
    //     ->where('DURUM', '!=', 'MOLA')
    //     ->orderByDesc('ID')
    //     ->first();

    //   if ($durumAl) {
    //     $durum = $durumAl->DURUM;
    //     $durusSebebi = $durumAl->DURUS_SEBEBI;
    //     $durusSebebiKodu = $durumAl->DURUS_SEBEBI_KODU;
    //   } else {
    //     $durum = 'ÇALIŞIYOR';
    //     $durusSebebi = null;
    //     $durusSebebiKodu = null;
    //   }
    // }


    $onceki = $baglanti
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->where('ISTASYON', $request->istasyonKodu)
      ->where('GUID', $request->guid)
      ->where('PERSONEL_ID', $request->userId)
      ->whereNull('DURUM_BIT_TARIHI')
      ->orderByDesc('ID')
      ->first();

    $yeniEkipNo = 1;
    // Log::info('Bulunan önceki kayıt:', (array) $onceki);

    if ($onceki) {
      // Eski kaydı kapat
      $baglanti
        ->table('OFTT_CALISMA_SURELERI_MONTAJ')
        ->where('ID', $onceki->ID)
        ->update([
          'DURUM_BIT_TARIHI' => $tarih,
          'DURUM_SURESI' => DB::raw("DATEDIFF(SECOND, DURUM_BAS_TARIHI, '$tarih')")
        ]);
    }
    //  else {
    //   $ekipno = $baglanti
    //     ->table('OFTT_CALISMA_SURELERI_MONTAJ')
    //     ->select('EKIP_NO')
    //     ->where('ISTASYON', $request->istasyonKodu)
    //     ->where('PERSONEL_ID', $request->userId)
    //     ->whereNull('DURUM_BIT_TARIHI')
    //     ->orderByDesc('ID', 'desc')
    //     ->first();
    //   // Ekip numarasını 1 artır
    //   $yeniEkipNo = ($ekipno->EKIP_NO ?? 0) + 1;
    // }


    if ($request->durum !== 'KAPANDI') {
      $baglanti
        ->table('OFTT_CALISMA_SURELERI_MONTAJ')
        ->insert([
          'ISTASYON' => $request->istasyonKodu,
          'IS_EMRI_ID' => $request->isEmriId,
          'IS_EMRI_NO' => $request->isEmriNo,
          'URUN_ID' => $request->urunID,
          'URUN_KODU' => $request->urunKodu,
          'URUN_ADI' => $request->urunAdi,
          'DURUM_BAS_TARIHI' => $tarih,
          'PERSONEL_ID' => $request->userId,
          'PERSONEL_SAYISI' => $request->personelSayisi,
          'VARDIYA' => $request->vardiya,
          'DURUM' => $durum,
          'DURUS_SEBEBI' => $durusSebebi,
          'DURUS_SEBEBI_KODU' => $durusSebebiKodu,
          'EKIP_NO' => $yeniEkipNo,
          'GUID' => $request->guid,
        ]);
    }

    return response()->json(['message' => 'Güncelleme başarılı'], 200);
  }

  public function IstasyonDuruslariniAl(Request $request)
  {
    $istasyonArray = explode(',', $request->istasyon);

    $duruslar = DB::connection('sqlsrv')
      ->table('OFTV_DURUSLAR_ISTASYON')
      ->whereIn('ISTASYON', $istasyonArray)
      ->whereDate('DURUM_BAS_TARIHI', '>=', $request->filterValue)
      ->whereDate('DURUM_BAS_TARIHI', '<=', $request->filterValue1)
      ->orderBy('ID', 'desc')
      ->get();

    return response()->json([
      'duruslar' => $duruslar,
    ], 200);
  }

  public function AktifDuruslariAl(Request $request)
  {
    // Log::info($request->all());

    $duruslar = DB::connection('sqlsrv')
      ->table('OFTV_DURUSLAR_AKTIF')
      ->where('ISTASYON', $request->istasyon)
      ->whereDate('DURUM_BAS_TARIHI', now()->toDateString())
      ->orderBy('ID', 'desc')
      ->get();

    return response()->json([
      'duruslar' => $duruslar,
    ], 200);
  }

  public function getAcilmisIsEmirleri(Request $request)
  {
    $data = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->select(
        'siparis_belge_no',
        'cari_ad',
        'isemri_no',
        'stok_kodu',
        'stok_adi',
        'isemri_miktari',
        'kalan_miktar',
        'planlanan_bitis_tarihi',
      )
      ->where('item_id', $request->item_id)
      ->distinct()
      ->get();

    return response()->json([
      'data' => $data,
    ], 200);
  }

  public function getIsEmri(Request $request)
  {
    // Log::info($request->all());
    $deger = (int)$request->deger;

    $data = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->select(
        'isemri_id',
        'isemri_miktari',
        'cari_ad',
        'IS_ISTASYONU',
        'IS_ISTASYONU_ID',
        'isemri_no',
        'stok_kodu',
        'stok_adi',
        'aksesuar',
        'siparis_belge_no',
        DB::raw("to_char(planlanan_baslangic, 'DD.MM.YYYY') || ' ... ' || to_char(planlanan_bitis_tarihi, 'DD.MM.YYYY') as plan_tarih_araligi")
      )
      ->where('isemri_id', $deger)
      ->get();

    if ($data->isEmpty()) {
      $data = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_ISEMIRLERI_KAPANMISLAR')
        ->select(
          'isemri_id',
          'isemri_miktari',
          'cari_ad',
          'IS_ISTASYONU',
          'IS_ISTASYONU_ID',
          'isemri_no',
          'stok_kodu',
          'stok_adi',
          'aksesuar',
          'siparis_belge_no',
          DB::raw("to_char(planlanan_baslangic, 'DD.MM.YYYY') || ' ... ' || to_char(planlanan_bitis_tarihi, 'DD.MM.YYYY') as plan_tarih_araligi")
        )
        ->where('isemri_id', $deger)
        ->get();
    }
    // Log::info($data);

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

  public function getMerkezler()
  {
    $merkezler = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_IS_ISTASYONLARI')
      ->select(
        'is_merkezi_id',
        'ismerkezi_kodu',
        DB::raw("concat_ws('-', ismerkezi_kodu, ismerkezi_adi) as mrk_adi") // 1200 ve istasyon_adi birleştirildi
      )
      ->where('firma_id', 2715)
      ->orderBy('is_merkezi_id')
      ->distinct()
      ->get();

    $siparisler = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_ISEMIRLERI_DETAY')
      ->select('siparis_belge_no')
      ->where('firma_id', 2715)
      ->orderBy('siparis_belge_no')
      ->distinct()
      ->get();

    $cariler = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_ISEMIRLERI_DETAY')
      ->select('cari_ad')
      ->where('firma_id', 2715)
      ->orderBy('cari_ad')
      ->distinct()
      ->get();

    return response()->json([
      'merkezler' => $merkezler,
      'siparisler' => $siparisler,
      'cariler' => $cariler,
      'message' => 'Veriler başarıyla alındı',
      'success' => true,
    ]);
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

  public function getIstasyon(Request $request)
  {

    // Log::info($request->all());
    $istasyonArray = explode(',', $request->ismerkezi);

    // Log::info($istasyonArray);

    $istasyonlar = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_IS_ISTASYONLARI')
      ->select(
        'istasyon_id',
        'istasyon_kodu',
        DB::raw("concat_ws('-', istasyon_kodu, istasyon_adi) as ist_adi") // 1200 ve istasyon_adi birleştirildi
      )
      ->whereIn('is_merkezi_id', $istasyonArray)
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

  public function getSatinalmaSorgu(Request $request)
  {
    // Log::info($request->all());

    $satinalma = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_SATINALMA_SIPARISLERI')
      ->where('STOK_HIZMET_ID', $request->item_id)
      ->where('ACIK_KAPALI', 'Açık')
      ->orderBy('BELGE_TARIHI', 'desc')
      ->get();

    return response()->json([
      'satinalma' => $satinalma,
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

  public function getTaleplerSorgu(Request $request)
  {
    // Log::info($data);

    $talepler = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_TALEPLER')
      ->where('item_id', $request->item_id)
      ->orderBy('talep_tarihi', 'desc')
      ->get();

    return response()->json([
      'talepler' => $talepler,
      'message' => 'Veriler başarıyla alındı',
      'success' => true,
    ]);
  }

  public function getDepoBakiyeleri(Request $request)
  {
    // Log::info($data);

    $bakiyeler = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
      ->where('item_id', $request->item_id)
      ->where('whouse_id', '!=', $request->depo)
      ->orderBy('qty_prm', 'desc')
      ->get();

    return response()->json([
      'bakiyeler' => $bakiyeler,
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

  public function getKapasiteParam(Request $request)
  {
    $istasyonlar = DB::connection('pgsql')->table('OFTV_ISEMIRLERI_DETAY')
      ->select('IS_ISTASYONU_ID', 'IS_ISTASYONU')
      // ->where('IS_MERKEZI_KODU', '4001')
      ->orderBy('IS_ISTASYONU')
      ->distinct()
      ->get();

    return response()->json([
      'istasyonlar' => $istasyonlar,
    ], 200);
  }

  public function getKapasiteHaftalar(Request $request)
  {

    $haftalar = DB::connection('pgsql')->table('OFTV_ISEMIRLERI_DETAY')
      ->select('hafta')
      ->where('IS_ISTASYONU_ID', $request->istasyon)
      ->orderBy('hafta')
      ->distinct()
      ->get();

    // Log::info('Haftalar:', ['haftalar' => $haftalar]);
    return response()->json([
      'haftalar' => $haftalar,
    ], 200);
  }

  public function getTakvim()
  {
    $veri = DB::connection('sqlsrv')
      ->table('PARAM_CALISMA_SAATLERI_KAP')
      ->where('ACIKLAMA', 'çalışma')
      ->select(
        DB::raw("LOWER(GUN) as gun"),
        'BASLANGIC_DAKIKASI as bas',
        'BITIS_DAKIKASI as bit'
      )
      ->orderBy('GUN')
      ->orderBy('BASLANGIC_DAKIKASI')
      ->get();

    return response()->json($veri);
  }

  public function getKapasiteData(Request $request)
  {
    $data = DB::connection('pgsql')->table('OFTV_ISEMIRLERI_DETAY')
      ->where('IS_ISTASYONU_ID', $request->istasyon)
      ->where('hafta', '>=', $request->hafta)
      ->where('isemri_tipi', '!=', 'MONTAJ İŞ EMRİ')
      ->orderBy('planlanan_baslangic')
      ->get()
      ->map(function ($item, $index) {
        $item->sira = ($index + 1) * 10;
        return $item;
      });

    return response()->json($data);
  }

  public function kapasiteGuncelle(Request $request)
  {
    Log::info($request->all());

    date_default_timezone_set('Europe/Istanbul');

    $liste = $request->input('liste');
    if (empty($liste)) {
      return response()->json(['error' => 'Veri gelmedi'], 400);
    }

    // CASE bloklarını oluştur
    $caseStart = collect($liste)->map(fn($item) => "WHEN worder_op_d_id = {$item['worder_d_id']} THEN '{$item['baslangic']}'::timestamp")->implode(' ');
    $caseEnd = collect($liste)->map(fn($item) => "WHEN worder_op_d_id = {$item['worder_d_id']} THEN '{$item['bitis']}'::timestamp")->implode(' ');
    $ids = collect($liste)->pluck('worder_d_id')->implode(',');
    $worderIds = collect($liste)->pluck('worder_id')->unique()->implode(',');

    $sql = "UPDATE uyumsoft.prdt_worder_op_d 
            SET plan_start_date = CASE {$caseStart} END,
                plan_end_date = CASE {$caseEnd} END
            WHERE worder_op_d_id IN ({$ids})";

    DB::connection('pgsql')->statement($sql);

    // Master tabloyu CTE ile topluca güncelle
    $cte = "WITH aggregate_dates AS (
              SELECT worder_m_id, 
                     MIN(plan_start_date) AS min_start,
                     MAX(plan_end_date) AS max_end
              FROM uyumsoft.prdt_worder_op_d
              WHERE worder_m_id IN ({$worderIds})
              GROUP BY worder_m_id
            )
            UPDATE uyumsoft.prdt_worder_m m
            SET plan_start_date = a.min_start,
                plan_end_date = a.max_end
            FROM aggregate_dates a
            WHERE m.worder_m_id = a.worder_m_id";

    DB::connection('pgsql')->statement($cte);

    // CASE WHEN blokları oluştur
    $caseTeslim = collect($liste)->map(function ($item) {
      $tarih = date('Y-m-d H:i:s', strtotime($item['teslim_tarihi']));
      return "WHEN '{$item['worder_no']}' THEN '{$tarih}'::timestamp";
    })->implode(' ');

    // İş emri numaralarını virgülle ayır, PostgreSQL dizisi gibi ({...}) formatına sok
    $isemrileriArray = collect($liste)->pluck('worder_no')->map(fn($no) => "'{$no}'")->implode(',');

    $sql = "
    WITH tarih_map AS (
        SELECT DISTINCT
            wsr.order_d_id,
            CASE wm.worder_no
                {$caseTeslim}
            END AS yeni_teslim
        FROM uyumsoft.prdt_worder_sales_rel wsr
        JOIN uyumsoft.prdt_worder_m wm ON wm.worder_m_id = wsr.worder_m_id
        WHERE wm.worder_no IN ({$isemrileriArray})
    )
    UPDATE uyumsoft.psmt_order_d od
    SET delivery_date = tarih_map.yeni_teslim
    FROM tarih_map
    WHERE tarih_map.order_d_id = od.order_d_id
    ";


    DB::connection('pgsql')->statement($sql);

    return response()->json(['status' => 'ok']);
  }

  public function PersonelListesi()
  {
    // $personeller = DB::connection('pgsql')
    //   ->table('prdd_employee as pe')
    //   ->leftJoin('hrmd_register as hr', 'hr.register_id', '=', 'pe.register_id')
    //   ->where('hr.ispassive', 0)
    //   ->where('pe.ispassive', 0)
    //   ->where('pe.co_id', 2715)
    //   ->select([
    //     'hr.register_id',
    //     DB::raw("hr.register_name || ' ' || hr.register_surname as adsoyad"),
    //     'hr.register_full_name',
    //     'hr.register_code',
    //     'hr.register_name',
    //     'hr.register_surname',
    //     'hr.citizenship_no',
    //     'pe.co_id',
    //   ])
    //   ->orderBy('hr.register_name')
    //   ->distinct()
    //   ->get();

    // $personeller = DB::connection('pgsql') // PostgreSQL bağlantısı ise
    //   ->table('prdd_employee as pe')
    //   ->leftJoin('prdd_employee_task_type as pet', 'pet.employee_task_type_id', '=', 'pe.employee_task_type_id')
    //   ->leftJoin('prdd_wstation as pw', DB::raw("to_char(pe.employee_task_type_id, 'FM999999999')"), '=', 'pw.add_string03')
    //   ->leftJoin('hrmd_register as hr', 'hr.register_id', '=', 'pe.register_id')
    //   ->where('pe.ispassive', 0)
    //   ->where('pe.co_id', 2715)
    //   // ->where('pw.wstation_id', 327)
    //   ->select([
    //     'pw.wstation_id as istasyon_id',
    //     'pe.citizenship_no as kimlik_no',
    //     DB::raw("hr.register_name || ' ' || hr.register_surname as ad_soyad"),
    //     'pe.register_id',
    //     'hr.register_code as sicil_no'
    //   ])
    //   ->get();

    $personeller = DB::connection('pgsql')
      ->table('prdd_employee as pe')
      ->select([
        DB::raw('DISTINCT ON (pe.citizenship_no) pe.citizenship_no as kimlik_no'),
        'pw.wstation_id as istasyon_id',
        'pw.wstation_code',
        DB::raw("pe.emp_name || ' ' || pe.emp_surname as ad_soyad"),
        'pe.register_id',
        'hr.register_code as sicil_no',
      ])
      ->leftJoin('prdd_wstation as pw', DB::raw('TO_CHAR(pe.employee_task_type_id)'), '=', DB::raw('pw.add_string03'))
      ->leftJoin('hrmd_register as hr', 'hr.register_id', '=', 'pe.register_id')
      ->where('pe.ispassive', 0)
      ->where('pe.co_id', 2715)
      //->orderBy('pe.citizenship_no') // DISTINCT ON için şarttır
      ->get();

    // Log::info($personeller);

    return response()->json($personeller);
  }

  public function EkipKaydet(Request $request)
  {
    $personeller = $request->all();
    $now = Carbon::now();
    try {
      foreach ($personeller as $p) {
        DB::connection('sqlsrv')->table('oftt_aktif_ekipler')->insert([
          'istasyon_id' => $p['istasyon_id'],
          'worder_m_id' => $p['worder_m_id'],
          'register_id' => $p['register_id'],
          'sicil_no' => $p['sicil_no'],
          'personel_full_name' => $p['personel_full_name'],
          'citizenship_no' => $p['citizenship_no'],
          'co_id' => 2517,
          'start_work_time' => $now,
          'end_work_time' => null,
          'guid' => $p['guid'],
        ]);
      }

      return response()->json(['success' => true], 201);
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function EkipleriAl(Request $request)
  {
    // Log::info($request->all());
    $veriler = DB::connection('sqlsrv')
      ->table('oftt_aktif_ekipler')
      // ->where('worder_m_id', $request->isemriID)
      ->where('guid', $request->guid)
      ->whereNull('end_work_time')
      ->orderBy('personel_full_name')
      ->get();

    // Log::info($veriler);
    return response()->json($veriler);
  }

  public function EkipleriKapat(Request $request)
  {
    $registerIds = $request->input('register_ids', []);
    $guid = $request->input('guid');
    $endWorkTime = $request->input('end_work_time');

    if (empty($registerIds) || !$guid || !$endWorkTime) {
      return response()->json(['message' => 'Eksik parametre'], 400);
    }

    DB::connection('sqlsrv')
      ->table('oftt_aktif_ekipler')
      ->where('guid', $guid)
      ->whereNull('end_work_time')
      ->whereIn('register_id', $registerIds)
      ->update(['end_work_time' => $endWorkTime]);

    return response()->json(['message' => 'Kapatma işlemi tamamlandı']);
  }

  public function bitir(Request $request)
  {
    $id = $request->id;

    $ekip = DB::table('oftt_aktif_ekipler')->where('id', $id)->first();

    if (!$ekip) {
      return response()->json(['message' => 'Kayıt bulunamadı'], 404);
    }

    $start = Carbon::parse($ekip->start_work_time);
    $now = Carbon::now();

    $duration = intval($start->diffInSeconds($now));

    // Log::info($duration);

    DB::table('oftt_aktif_ekipler')
      ->where('id', $id)
      ->update([
        'end_work_time' => $now,
        'duration' => $duration,
      ]);

    return response()->json(['message' => 'Kayıt güncellendi', 'duration' => $duration]);
  }

  public function bitirToplu(Request $request)
  {
    $idList = $request->ids; // dizi bekleniyor

    if (!is_array($idList) || empty($idList)) {
      return response()->json(['message' => 'ID listesi boş.'], 400);
    }

    $now = Carbon::now();

    // Kayıtları al
    $kayitlar = DB::table('oftt_aktif_ekipler')
      ->whereIn('id', $idList)
      ->get();

    foreach ($kayitlar as $ekip) {
      $start = Carbon::parse($ekip->start_work_time);
      $duration = intval($start->diffInSeconds($now));

      DB::table('oftt_aktif_ekipler')
        ->where('id', $ekip->id)
        ->update([
          'end_work_time' => $now,
          'duration' => $duration,
        ]);
    }

    return response()->json([
      'message' => 'Toplu güncelleme tamamlandı.',
      'adet' => count($kayitlar),
    ]);
  }

  public function getKartlar(Request $request)
  {
    $istasyonId = (int)$request->istasyon;

    // Log::info('İstasyon ID:', ['istasyonId' => $istasyonId]);

    $calismalar = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ as calisma')
      ->join('users', 'calisma.PERSONEL_ID', '=', 'users.id')
      ->join('oftt_kontrol_isemri', 'calisma.IS_EMRI_ID', '=', 'oftt_kontrol_isemri.isemri_id')
      ->select(
        'calisma.IS_EMRI_ID',
        'calisma.DURUM',
        'calisma.DURUS_SEBEBI',
        'calisma.DURUM_BAS_TARIHI',
        'calisma.DURUM_SURESI',
        'calisma.GUID',
        'calisma.PERSONEL_ID',
        'users.name as personel_adi',
        'oftt_kontrol_isemri.is_use_quality',
        'oftt_kontrol_isemri.is_check_quality_opr'
      )
      ->whereNull('calisma.DURUM_BIT_TARIHI')
      ->where('calisma.ISTASYON', $istasyonId)
      ->limit(100)
      ->get()
      ->groupBy('IS_EMRI_ID');

    // Log::info('Çalışmalar:', $calismalar->toArray());

    $isemriIDs = $calismalar->keys()->toArray();
    // Log::info('İş Emirleri ID Listesi:', $isemriIDs);
    $guidList = $calismalar->flatten()->pluck('GUID')->unique()->values()->toArray();


    $parcalar = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->select('URUN_ID')
      ->whereDate('DURUM_BAS_TARIHI', '=', Carbon::now()->toDateString())
      ->where('ISTASYON', $istasyonId)
      ->get()
      ->groupBy('URUN_ID');

    $parcaIDs = $parcalar->keys()->toArray();

    // Log::info('Parçalar ID Listesi:', $parcaIDs);

    $isEmirleri = DB::connection('pgsql')
      ->table('OFTV_ISEMIRLERI_DETAY')
      ->select(
        'isemri_id',
        'isemri_no',
        'item_id',
        'stok_kodu',
        'stok_adi',
        'planlanan_baslangic',
        'planlanan_bitis_tarihi',
        'teknik_not1',
        'teknik_not2',
        'aksesuar',
      )
      ->whereIn('isemri_id', $isemriIDs)
      ->distinct()
      ->get();

    // Log::info('İş Emirleri:', $isEmirleri->toArray());

    $sureler = DB::connection('sqlsrv')
      ->table('OFTT_CALISMA_SURELERI_MONTAJ')
      ->select('IS_EMRI_ID', 'DURUM', 'DURUS_SEBEBI', 'DURUM_SURESI', 'DURUM_BAS_TARIHI')
      // ->whereIn('IS_EMRI_ID', $isemriIDs)
      ->whereIn('GUID', $guidList)
      ->orderBy('DURUM_BAS_TARIHI')
      ->get()
      ->groupBy('IS_EMRI_ID');

    $formatted = $isEmirleri->map(function ($item) use ($sureler, $calismalar) {
      $veriListesi = $sureler[$item->isemri_id] ?? collect();
      $calismaList = $calismalar[$item->isemri_id] ?? collect();
      $sonDurum = $calismaList->last();
      $now = now();

      $status = $sonDurum?->DURUM;
      // $status = match (true) {
      //         $sonDurum?->DURUM === 'ÇALIŞIYOR' => 'Çalışıyor',
      //         $sonDurum?->DURUM === 'HAZIRLIK' => 'Hazırlık',
      //         $sonDurum?->DURUM === 'DURUYOR' => 'Duruyor',
      //         $sonDurum?->DURUM === 'MOLA' => 'Mola',
      //         default => 'ÇALIŞIYOR',
      //       };

      $barData = [];
      $sonDurumSuresi = 0;

      foreach ($veriListesi as $row) {
        $type = match (true) {
          $row->DURUM === 'ÇALIŞIYOR' => 'calisma',
          $row->DURUM === 'HAZIRLIK' => 'ayar',
          $row->DURUM === 'DURUYOR' => 'durus',
          $row->DURUM === 'MOLA' => 'mola',
          default => 'kalan',
        };

        $sure = (int) $row->DURUM_SURESI;

        $lastRow = $veriListesi->last();
        if ($sure === 0 && isset($row->DURUM_BAS_TARIHI) && $row === $lastRow) {
          $basTarih = Carbon::parse($row->DURUM_BAS_TARIHI);
          $sure = $basTarih->diffInSeconds($now);
          $sonDurumSuresi = $sure;
        }

        $barData[] = [
          'type' => $type,
          'duration' => $sure,
          'sebep' => $row->DURUS_SEBEBI ?? null,
        ];
      }

      $toplamSure = array_sum(array_column($barData, 'duration'));
      $barTotal = $toplamSure / 60; // dakika cinsine çevir

      $durus = array_sum(array_column(array_filter($barData, fn($d) => $d['type'] === 'durus'), 'duration'));
      $ayar = array_sum(array_column(array_filter($barData, fn($d) => $d['type'] === 'ayar'), 'duration'));
      $mola = array_sum(array_column(array_filter($barData, fn($d) => $d['type'] === 'mola'), 'duration'));
      $calisma = array_sum(array_column(array_filter($barData, fn($d) => $d['type'] === 'calisma'), 'duration'));
      $payda = ($durus + $ayar + $calisma);
      $meco = $payda > 0 ? ($calisma / $payda * 100) : 0;
      // Log::info("İş Emri: {$item->isemri_id}, Durum: {$status}, Toplam Süre: {$barTotal} dakika, Son Durum Süresi: {$sonDurumSuresi} saniye");
      // Log::info('İş Emri ID', ['id' => $item->isemri_id, 'calismaListCount' => $calismaList->count()]);

      $ekipSayisi = DB::table('oftt_aktif_ekipler')
        ->where('guid', $sonDurum->GUID ?? null)
        ->whereNull('end_work_time')
        ->count();

      return [
        'status' => $status,
        'personelId' => $sonDurum->PERSONEL_ID,
        'personelName' => $sonDurum->personel_adi ?? '?',
        'is_use_quality' => $sonDurum->is_use_quality ?? '0',
        'is_check_quality_opr' => $sonDurum->is_check_quality_opr ?? '0',
        'isemriId' => $item->isemri_id,
        'isemriNo' => $item->isemri_no,
        'runTime' => gmdate('H:i', $calisma),
        'totalDown' => gmdate('H:i', $durus + $ayar + $mola),
        'totalTime' => gmdate('H:i', $toplamSure),
        'target' => 0,
        'actual' => 0,
        'efficiency' => (int)$meco,
        'quality' => 100,
        'partId' => $item->item_id,
        'partCode' => $item->stok_kodu,
        'partName' => $item->stok_adi,
        'aksesuarli' => $item->aksesuar === 'Aksesuarlı' ? '1' : '0',
        'sebep' => $sonDurum->DURUS_SEBEBI ?? null,
        'baslikArkarenk' => match ($status) {
          'ÇALIŞIYOR' => 'calisma',
          'HAZIRLIK' => 'ayar',
          'DURUYOR' => 'durus',
          'MOLA' => 'mola',
          default => 'ÇALIŞIYOR',
        },
        'barTotal' => $barTotal,
        'barData' => $barData,
        'sonDurumSuresi' => gmdate('H:i', $sonDurumSuresi),
        'guid' => $sonDurum->GUID ?? null,
        'plnNote' => $item->teknik_not1 ?? '...',
        'prdNote' => $item->teknik_not2 ?? '...',
        'baslangicZamani' => Carbon::parse($sonDurum->DURUM_BAS_TARIHI)->format('d-m-Y H:i'),
        'ekipSayisi' => $ekipSayisi ?? 0,
      ];
    });


    return response()->json($formatted);
  }

  public function baslat(Request $request)
  {
    DB::connection('pgsql')->table('OFTT_CALISMA_SURELERI_MONTAJ')->insert([
      'is_emri_id' => $request->input('is_emri_id'),
      'sebep' => $request->input('sebep'),
      'durum' => 'Mola',
      'baslangic' => $request->input('baslangic'),
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    return response()->json(['message' => 'Mola kaydedildi']);
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







  // public function kapasitePlanla()
  // {
  //   Carbon::setLocale('tr');

  //   $isemirleri = DB::connection('pgsql')->table('OFTV_ISEMIRLERI_DETAY')
  //     ->select('isemri_no', 'IS_ISTASYONU_ID', 'operasyon_hazirlik_suresi', 'operasyon_suresi', 'hafta')
  //     ->whereIn('IS_ISTASYONU_ID', [327])
  //     ->where('hafta', '=', '25-25')
  //     ->orderBy('isemri_no')
  //     ->limit(1000)
  //     ->get();

  //   $calismaSaatleri = DB::connection('sqlsrv')->table('PARAM_CALISMA_SAATLERI_KAP')
  //     ->where('ACIKLAMA', 'çalışma')
  //     ->get();

  //   $takvim = $this->hazirTakvim($calismaSaatleri);
  //   $baslangicZamani = Carbon::now()->startOfDay();
  //   $sonZamanlar = ['327' => $baslangicZamani->copy()];
  //   $planlanmis = [];

  //   foreach ($isemirleri as $item) {
  //     $sure = floatval($item->operasyon_hazirlik_suresi) + floatval($item->operasyon_suresi);
  //     $istasyon = $item->IS_ISTASYONU_ID;
  //     // Log::info("Planlama: {$item->isemri_no} - İstasyon: {$istasyon} - Süre: {$sure}dk");
  //     try {
  //       list($basla, $bitir) = $this->calismaSaatlerineGoreParcala($sonZamanlar[$istasyon], $sure, $takvim);
  //     } catch (\Exception $e) {
  //       Log::error("Planlama hatası: {$item->isemri_no} - " . $e->getMessage());
  //       continue;
  //     }

  //     $planlanmis[] = [
  //       'hafta' => $item->hafta,
  //       'sure' => $sure,
  //       'isemri_no' => $item->isemri_no,
  //       'istasyon' => $istasyon,
  //       'gun' => $basla->translatedFormat('l'), // örn: Pazartesi
  //       'baslangic' => $basla->format('Y-m-d H:i'),
  //       'bitis' => $bitir->format('Y-m-d H:i'),
  //     ];

  //     $sonZamanlar[$istasyon] = $bitir->copy();
  //   }

  //   return response()->json($planlanmis);
  // }

  // private function hazirTakvim($saatler)
  // {
  //   $takvim = [];
  //   foreach ($saatler as $satir) {
  //     $gun = mb_strtolower($satir->GUN);
  //     if (!isset($takvim[$gun])) {
  //       $takvim[$gun] = [];
  //     }
  //     $takvim[$gun][] = [
  //       'bas' => intval($satir->BASLANGIC_DAKIKASI),
  //       'bit' => intval($satir->BITIS_DAKIKASI),
  //     ];
  //   }
  //   return $takvim;
  // }

  // private function calismaSaatlerineGoreParcala($zaman, $sure, $takvim)
  // {
  //   Carbon::setLocale('tr');

  //   $z = $zaman->copy();
  //   $baslangic = null;
  //   $kalanSure = floatval($sure);

  //   for ($i = 0; $i < 1000; $i++) {
  //     $gunAdi = mb_strtolower($z->translatedFormat('l'));

  //     // Log::info("Planlama: {$z->format('Y-m-d H:i')} - $gunAdi - Kalan süre: {$kalanSure}dk");

  //     if (!isset($takvim[$gunAdi]) || count($takvim[$gunAdi]) === 0) {
  //       $z->addDay()->setTime(0, 0);
  //       continue;
  //     }

  //     foreach ($takvim[$gunAdi] as $blok) {
  //       $blokBas = $z->copy()->setTime(0, 0)->addMinutes($blok['bas']);
  //       $blokBit = $z->copy()->setTime(0, 0)->addMinutes($blok['bit']);

  //       if ($z->gte($blokBit)) continue;

  //       $basla = $z->lt($blokBas) ? $blokBas : $z->copy();
  //       $musaade = $basla->diffInRealMinutes($blokBit, false); // garantili pozitif yön

  //       if ($musaade <= 0) continue;

  //       if (!$baslangic) {
  //         $baslangic = $basla->copy();
  //       }

  //       if ($musaade >= $kalanSure) {
  //         $bitis = $basla->copy()->addMinutes($kalanSure);
  //         Log::info("Planlama tamamlandı: {$baslangic->format('Y-m-d H:i')} - {$bitis->format('Y-m-d H:i')}");
  //         return [$baslangic, $bitis];
  //       }

  //       $kalanSure -= $musaade;
  //       $z = $blokBit->copy();
  //     }

  //     $z->addDay()->setTime(0, 0);
  //   }

  //   throw new \Exception("Uygun zaman blo\u011fu bulunamad\u0131: {$zaman->format('Y-m-d H:i')} - {$sure}dk");
  // }

  // private function gunIngilizcedenTurkceye($en)
  // {
  //   return match (strtolower($en)) {
  //     'monday' => 'Pazartesi',
  //     'tuesday' => 'Salı',
  //     'wednesday' => 'Çarşamba',
  //     'thursday' => 'Perşembe',
  //     'friday' => 'Cuma',
  //     'saturday' => 'Cumartesi',
  //     'sunday' => 'Pazar',
  //   };
  // }

  // public function deleteIsEmri($id)
  // {
  //   try {
  //     // İş emrinin mevcut olup olmadığını kontrol et
  //     $isEmri = DB::connection('pgsql')
  //       ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
  //       ->where('satir_id', $id)
  //       ->first();

  //     if (!$isEmri) {
  //       return response()->json([
  //         'error' => 'İş emri bulunamadı!'
  //       ], 404);
  //     }

  //     // İş emrinin aktif olup olmadığını kontrol et
  //     $aktifIsEmri = DB::connection('pgsql')
  //       ->table('uyumsoft.OFTV_AKTIF_ISEMIRLERI')
  //       ->where('isemri_id', $isEmri->isemri_id)
  //       ->first();

  //     if ($aktifIsEmri) {
  //       return response()->json([
  //         'error' => 'Aktif iş emri silinemez! Önce iş emrini kapatın.'
  //       ], 400);
  //     }

  //     // İş emrini sil
  //     DB::connection('pgsql')
  //       ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
  //       ->where('satir_id', $id)
  //       ->delete();

  //     // Log kaydı
  //     Log::info('İş emri silindi', [
  //       'satir_id' => $id,
  //       'isemri_no' => $isEmri->isemri_no,
  //       'user_id' => 'unknown'
  //     ]);

  //     return response()->json([
  //       'message' => 'İş emri başarıyla silindi!'
  //     ], 200);

  //   } catch (\Exception $e) {
  //     Log::error('İş emri silme hatası', [
  //       'satir_id' => $id,
  //       'error' => $e->getMessage()
  //     ]);

  //     return response()->json([
  //       'error' => 'İş emri silinirken hata oluştu: ' . $e->getMessage()
  //     ], 500);
  //   }
  // }
}
