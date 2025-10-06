<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;
use Illuminate\Auth\Events\Validated;
use Carbon\Carbon;

class UretimMontajController extends Controller
{
  // Yardımcı: tarih/string/null -> Carbon|null
  protected function parseTarih($value, $default = null)
  {
    if (!$value) {
      return $default ? Carbon::parse($default) : null;
    }
    if ($value instanceof Carbon) {
      return $value;
    }
    try {
      return Carbon::parse($value);
    } catch (\Throwable $e) {
      return $default ? Carbon::parse($default) : null;
    }
  }

  public function PersonelListesi()
  {
    // Log::info('PersonelListesi method called');
    $personeller = DB::connection('pgsql')
      ->table('uyumsoft.prdd_employee as pe')
      ->select([
        DB::raw('DISTINCT ON (pe.citizenship_no) pe.citizenship_no as kimlik_no'),
        'pw.wstation_id as istasyon_id',
        'pw.wstation_code',
        DB::raw("pe.emp_name || ' ' || pe.emp_surname as ad_soyad"),
        'pe.register_id',
        'hr.register_code as sicil_no',
      ])
      ->leftJoin('uyumsoft.prdd_wstation as pw', DB::raw('TO_CHAR(pe.employee_task_type_id)'), '=', DB::raw('pw.add_string03'))
      ->leftJoin('uyumsoft.hrmd_register as hr', 'hr.register_id', '=', 'pe.register_id')
      ->where('pe.ispassive', 0)
      ->where('pe.co_id', 2715)
      //->orderBy('pe.citizenship_no') // DISTINCT ON için şarttır
      ->get();

    // Log::info($personeller);

    return response()->json($personeller);
  }

  public function EkipleriAl(Request $request)
  {
    // Log::info($request->all());
    $veriler = DB::connection('pgsql_oft')
      ->table('oftt_aktif_ekipler')
      // ->where('worder_m_id', $request->isemriID)
      ->where('guid', $request->guid)
      ->whereNull('end_work_time')
      ->orderBy('personel_full_name')
      ->get();

    // Log::info($veriler);
    return response()->json($veriler);
  }

  public function EkipKaydet(Request $request)
  {
    $personeller = $request->all();
    $now = Carbon::now();
    try {
      foreach ($personeller as $p) {
        DB::connection('pgsql_oft')->table('oftt_aktif_ekipler')->insert([
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

  public function EkipleriKapat(Request $request)
  {
    $registerIds = $request->input('register_ids', []);
    $guid = $request->input('guid');
    $endWorkTime = $request->input('end_work_time');

    if (empty($registerIds) || !$guid || !$endWorkTime) {
      return response()->json(['message' => 'Eksik parametre'], 400);
    }

    DB::connection('pgsql_oft')
      ->table('oftt_aktif_ekipler')
      ->where('guid', $guid)
      ->whereNull('end_work_time')
      ->whereIn('register_id', $registerIds)
      ->update(['end_work_time' => $endWorkTime]);

    return response()->json(['message' => 'Kapatma işlemi tamamlandı']);
  }

  public function insertWorkOrder(Request $request)
  {
    try {
      DB::beginTransaction();

      // 1) İŞ EMRİ – kaynak veriler (uyumsoft)
      $isemri = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_OFTV_ISEMIRLERI_DETAY')
        ->where('isemri_id', $request->WorderMId)
        ->first();

      if (!$isemri) {
        DB::rollBack();
        return response()->json([
          'success' => false,
          'error' => 'İş emri bulunamadı',
          'isemri_id' => $request->WorderMId,
        ], 404);
      }

      // Not: uyumsoft tarafında kolon isimleri PostgreSQL'de genellikle küçük harfe düşer.
      // CIKIS_DEPO yerine cikis_depo olabilir. Gerekirse aşağıdaki iki satırdan birini aktif edin.
      $cikisDepoId = $isemri->cikis_depo ?? ($isemri->CIKIS_DEPO ?? null);

      $cikisDepo = $cikisDepoId
        ? DB::connection('pgsql')->table('uyumsoft.invd_whouse')->where('whouse_id', $cikisDepoId)->first()
        : null;
      // 2) Çalışma tarih aralığı
      $calismaTarih = DB::table('oftt_calisma_sureleri_montaj')
        ->selectRaw('MIN(durum_bas_tarihi) as ilk_tarih, MAX(durum_bit_tarihi) as son_tarih')
        ->where('guid', $request->guid)
        ->first();

      $startDate = $calismaTarih?->ilk_tarih ? Carbon::parse($calismaTarih->ilk_tarih) : null;
      $endDate   = $calismaTarih?->son_tarih ? Carbon::parse($calismaTarih->son_tarih) : Carbon::now();

      // 3) troc_workorder Ekle (idempotent updateOrInsert)
      $woConnection = DB::connection('pgsql_oft');
      $woTable = $woConnection->table('Troc_WorkOrder');
      $compositeKey = [
        'wordermid'    => $isemri->isemri_id ?? null,
        'operationno'  => $isemri->operasyon_no ?? ($isemri->Operasyon_no ?? 0),
        'itemcode'     => $isemri->stok_kodu ?? '',
        'awstationcode' => $isemri->is_istasyonu_kodu ?? ($isemri->IS_ISTASYONU_KODU ?? ''),
      ];
      // Not: updateOrInsert yalnızca iki parametre alır: (eşsiz alanlar, güncellenecek alanlar)
      // Önceki sürümde yanlışlıkla 3. dizi verildiği için değer alanları güncellenmedi ve null kaldı.
      $woTable->updateOrInsert($compositeKey, [
        'guid'                       => $request->guid,
        'branchcode'                 => $isemri->firma_kodu ?? null,
        'worderno'                   => $isemri->isemri_no ?? null,
        'wstationcode'               => $isemri->is_istasyonu_kodu ?? ($isemri->IS_ISTASYONU_KODU ?? ''),
        'startdate'                  => $startDate,
        'enddate'                    => $endDate,
        'qtynet'                     => $request->uretimMiktari ?? 0,
        'qty'                        => $request->uretimMiktari ?? 0,
        'unitcode'                   => $isemri->birim_kodu ?? null,
        'productinputwhousecode'     => 500,
        'materialoutputwhousecode'   => $cikisDepo->whouse_code ?? null,
        'semiprdmtrwhousecode'       => $cikisDepo->whouse_code ?? null,
        'note1'                      => $isemri->sip_not1 ?? '',
      ]);

      // Oluşan / var olan kaydın ID'sini al
      $workOrderId = $woConnection->table('Troc_WorkOrder')
        ->where($compositeKey)
        ->value('id');

      if ($workOrderId) {
        // Troc_Info tablosunda aynı systems + systemsid zaten varsa eklemeyelim (idempotent)
        $infoExists = $woConnection->table('Troc_Info')
          ->where('systems', 'Uyumsoft')
          ->where('systemsid', $workOrderId)
          ->exists();
        if (!$infoExists) {
          $woConnection->table('Troc_Info')->insert([
            'systems'    => 'Uyumsoft',
            'transfer'   => false,
            'isdeleted'  => false,
            'systemsid'  => $workOrderId,
          ]);
        }
      }

      // 4) troc_employeeruntime – aktif ekipler
      if (!empty($request->guid)) {
        $personeller = DB::table('oftt_aktif_ekipler')
          ->where('guid', $request->guid)
          ->get();

        foreach ($personeller as $personel) {
          $startTime = $this->parseTarih($personel->start_work_time);
          $endTime   = $this->parseTarih($personel->end_work_time, Carbon::now());
          DB::connection('pgsql_oft')->table('Troc_EmployeeRuntime')->insert([
            'workorderguid'        => $request->guid,
            'employeeno'            => $personel->sicil_no,
            'employeename'          => $personel->personel_full_name,
            'startdate'             => $startTime,
            'enddate'               => $endTime,
            'empworktimeasseconds' => ($personel->duration ?? 0),
          ]);
        }
      }

      // 5) troc_worderbreak – duruşlar
      if (!empty($request->guid)) {
        $duruslar = DB::connection('pgsql_oft')->table('oftt_calisma_sureleri_montaj')
          ->where('guid', $request->guid)
          ->where('durum', 'DURUYOR')
          ->orderBy('durum_bas_tarihi', 'asc')
          ->get();
        if ($duruslar->isNotEmpty()) {
          foreach ($duruslar as $durus) {
            $startTimeBreak = $durus->durum_bas_tarihi ? Carbon::parse($durus->durum_bas_tarihi) : null;
            $endTimeBreak   = $durus->durum_bit_tarihi ? Carbon::parse($durus->durum_bit_tarihi) : Carbon::now();

            $personeller = DB::connection('pgsql_oft')
              ->table('oftt_aktif_ekipler')
              ->where('guid', $request->guid)
              ->where('start_work_time', '<=', $startTimeBreak)
              ->where(function ($query) use ($endTimeBreak) {
                $query->where('end_work_time', '>=', $endTimeBreak)
                  ->orWhereNull('end_work_time');
              })
              ->get();
            // Log::info('Duruşlar: ' . $duruslar);
            // Log::info('Personeller: ' . $personeller);
            // Log::info('Duruş Sebebi: ' . $durus->durus_sebebi_kodu);
            // Log::info('Start Time Break: ' . $startTimeBreak);
            // Log::info('End Time Break: ' . $endTimeBreak);

            $lineNo = 0;
            foreach ($personeller as $personel) {
              $lineNo += 10;
              DB::connection('pgsql_oft')->table('Troc_WorderBreak')->insert([
                'workorderguid'  => $request->guid,
                'breaklineno'    => $lineNo,
                'startdate'       => $startTimeBreak,
                'enddate'         => $endTimeBreak,
                'breakreasoncode' => $durus->durus_sebebi_kodu ?? ($durus->durus_sebebi_kodu ?? null),
                'citizenshipno'   => $personel->citizenship_no,
                'notelarge'       => 'test',
                'notelarge1'      => 'test',
                'notelarge2'      => 'test',
                'notelarge3'      => 'test',
              ]);
            }
          }
        }
      }

      DB::commit();
      return response()->json(['success' => true]);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('insertWorkOrder hata', [
        'message' => $e->getMessage(),
        'trace' => substr($e->getTraceAsString(), 0, 2000),
        'isemriId' => $request->WorderMId,
        'guid' => $request->guid,
      ]);
      return response()->json([
        'success' => false,
        'error' => 'İş emri kapatma sırasında sunucu hatası',
        'detail' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
      ], 500);
    }
  }

  public function guncelleIsCheckQualityOpr(Request $request)
  {
    // Log::info($request->all());
    $guncellendi = DB::connection('pgsql_oft')
      ->table('oftt_kontrol_isemri')
      ->where('isemri_id', $request->isEmriID)
      ->where('istasyon_id', $request->istasyonID)
      ->update([
        'is_check_quality_opr' => $request->is_check_quality_opr === 1 ? true : false,
        'guncelleyen_id' => $request->personelID,
        'updated_at' => now(),
      ]);

    if ($guncellendi) {
      return response()->json(['status' => 'ok']);
    }

    return response()->json(['status' => 'error', 'message' => 'Kayıt bulunamadı'], 404);
  }

  public function DurusSebepleriAl(Request $request)
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

  public function IstasyonDuruslariniAl(Request $request)
  {
    $istasyonArray = explode(',', $request->istasyon);

    $duruslar = DB::connection('pgsql_oft')
      ->table('oftv_duruslar_istasyon')
      ->whereIn('istasyon', $istasyonArray)
      ->whereDate('durum_bas_tarihi', '>=', $request->filterValue)
      ->whereDate('durum_bas_tarihi', '<=', $request->filterValue1)
      ->orderBy('id', 'desc')
      ->get();

    return response()->json([
      'duruslar' => $duruslar,
    ], 200);
  }

  public function AktifDuruslariAl(Request $request)
  {
    // Log::info($request->all());

    $duruslar = DB::connection('pgsql_oft')
      ->table('oftv_duruslar_aktif')
      ->where('istasyon', $request->istasyon)
      ->whereDate('durum_bas_tarihi', now()->toDateString())
      ->orderBy('id', 'desc')
      ->get();

    return response()->json([
      'duruslar' => $duruslar,
    ], 200);
  }





  // Montaj duruşları listesi: oftt_calisma_sureleri_montaj
  public function MontajDuruslar(Request $request)
  {
    try {
      $conn = DB::connection('pgsql_oft');
      $q = $conn->table('oftt_calisma_sureleri_montaj as csm')
        ->leftJoin('oftt_work_stations as ws', 'ws.wstation_id', '=', 'csm.istasyon')
        ->select([
          'csm.id',
          DB::raw("concat_ws('-', ws.wstation_code::text, NULLIF(ws.description::text, '')) as istasyon"),
          'durum_bas_tarihi',
          'durum_bit_tarihi',
          // Bitiş null ise anlık süre: NOW - durum_bas_tarihi, dakika cinsinden
          DB::raw("ROUND((CASE WHEN durum_bit_tarihi IS NULL THEN (EXTRACT(EPOCH FROM (NOW() - durum_bas_tarihi)))::numeric ELSE COALESCE(durum_suresi::numeric, 0) END) / 60, 2) AS durum_suresi"),
          'durum',
          'durus_sebebi_kodu',
          'durus_sebebi',
          'is_emri_no',
          'urun_kodu',
          'urun_adi',
          'personel_id',
          'is_emri_id',
          'urun_id',
        ])
        // İstenilen filtre: durum alanı üzerinden
        ->whereIn('durum', ['DURUYOR', 'MOLA']);

      // Opsiyonel tarih aralığı filtresi
      $from = $request->query('from');
      $to = $request->query('to');
      if ($from) {
        $q->whereDate('durum_bas_tarihi', '>=', $from);
      }
      if ($to) {
        $q->whereDate('durum_bas_tarihi', '<=', $to);
      }

      // Not: SELECT içindeki alias (durum_suresi) WHERE'de kullanılamaz; aynı ifadeyi whereRaw ile uygula
      // Opsiyonel süre eşiği (saniye/dakika). Varsayılan: 0 saniye
      $minSecondsParam = $request->query('min_seconds');
      $minMinutesParam = $request->query('min_minutes');
      $thresholdSeconds = 0;
      if ($minMinutesParam !== null && $minMinutesParam !== '') {
        $thresholdSeconds = (int) round(floatval($minMinutesParam) * 60);
      } elseif ($minSecondsParam !== null && $minSecondsParam !== '') {
        $thresholdSeconds = (int) $minSecondsParam;
      }

      $rows = $q
        ->whereRaw(
          "(CASE WHEN durum_bit_tarihi IS NULL THEN EXTRACT(EPOCH FROM (NOW() - durum_bas_tarihi)) ELSE COALESCE(durum_suresi, 0) END) > ?",
          [$thresholdSeconds]
        )
        ->orderBy('csm.id', 'desc')
        ->limit(5000)
        ->get();

      // Log::info('MontajDuruslar kayıt sayısı: ' . $rows->count());

      return response()->json([
        'data' => $rows,
      ]);
    } catch (\Throwable $e) {
      Log::error('MontajDuruslar hata', [
        'message' => $e->getMessage(),
      ]);
      return response()->json(['message' => 'Sunucu hatası'], 500);
    }
  }

  // oftt_calisma_sureleri_montaj tablosunda tek kaydın duruş sebebi kodu ve açıklamasını güncelle
  public function UpdateDurusSebebi(Request $request, int $id)
  {
    $validated = $request->validate([
      'durus_sebebi_kodu' => 'required|string|max:100',
      'durus_sebebi' => 'required|string|max:255',
    ]);

    try {
      $affected = DB::connection('pgsql_oft')
        ->table('oftt_calisma_sureleri_montaj')
        ->where('id', $id)
        ->update([
          'durus_sebebi_kodu' => $validated['durus_sebebi_kodu'],
          'durus_sebebi' => $validated['durus_sebebi'],
          // 'updated_at' => now(),
        ]);

      if ($affected === 0) {
        return response()->json(['message' => 'Kayıt bulunamadı'], 404);
      }

      return response()->json(['message' => 'Güncellendi']);
    } catch (\Throwable $e) {
      Log::error('UpdateDurusSebebi hata', ['id' => $id, 'err' => $e->getMessage()]);
      return response()->json(['message' => 'Sunucu hatası'], 500);
    }
  }







  public function KontrolGerekKaydet(Request $request)
  {
    // Log::info('KontrolGerekKaydet request:', $request->all());

    $veri = $request->all();

    // MSSQL bağlantısı sorun çıkarırsa hata fırlatıp tüm işlemi kırmasın
    $kayitVarmi = false;
    try {
      $kayitVarmi = DB::connection('pgsql_oft')
        ->table('oftt_kontrol_isemri')
        ->where('isemri_id', $veri['isEmriID'])
        ->where('istasyon_id', $veri['istasyonID'])
        ->exists();
    } catch (\Throwable $e) {
      Log::warning('pgsql_oft kontrol kaydı sorgusu başarısız: ' . $e->getMessage());
    }

    if ($kayitVarmi === true) {
      // Log::info('Bu istasyon için kontrol kaydı zaten var.');
      return response()->json(['status' => 'error', 'message' => 'Bu istasyon için kontrol kaydı zaten var.'], 400);
    }


    $kontrol_gerekli = DB::connection('pgsql')
      ->table('uyumsoft.invd_branch_item')
      ->select('is_use_quality')
      ->where('item_id', $veri['itemID'])
      ->first();

    // Log::info('Kontrol gerekli:', [
    //   'itemID' => $veri['itemID'],
    //   'is_use_quality' => $kontrol_gerekli->is_use_quality ?? 'null',
    // ]);
    $kaydet = [
      'isemri_id' => $veri['isEmriID'],
      'isemri_no' => $veri['isEmriNo'],
      'item_code' => $veri['urunKodu'],
      'item_name' => $veri['urunAdi'],
      'belge_no' => $veri['belgeNo'],
      'cari_ad' => $veri['cariAd'],
      'istasyon_id' => $veri['istasyonID'],
      'item_id' => $veri['itemID'],
      'is_use_quality' => $kontrol_gerekli->is_use_quality,
      'is_check_quality_opr' => $kontrol_gerekli->is_use_quality === 1 ? true : false,
      'olusturan_id' => $veri['userID'],
      'created_at' => now(),
      'updated_at' => now(),
    ];
    // Doğal anahtar: (isemri_id, istasyon_id) ile upsert yap
    try {
      DB::connection('pgsql_oft')
        ->table('oftt_kontrol_isemri')
        ->updateOrInsert([
          'isemri_id' => $veri['isEmriID'],
          'istasyon_id' => $veri['istasyonID'],
        ], $kaydet);
    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
      // Büyük olasılıkla id sequence senkron değil; düzeltip bir kez daha dene
      Log::warning('oftt_kontrol_isemri insert unique violation, sequence düzeltilecek: ' . $e->getMessage());
      $this->fixSequence('oftt_kontrol_isemri', 'id', 'pgsql_oft');
      // Retry once
      DB::connection('pgsql_oft')
        ->table('oftt_kontrol_isemri')
        ->updateOrInsert([
          'isemri_id' => $veri['isEmriID'],
          'istasyon_id' => $veri['istasyonID'],
        ], $kaydet);
    } catch (\Illuminate\Database\QueryException $e) {
      // Başka bir nedenle unique violation veya race condition olabilir; var ise güncelle, yoksa hata döndür
      $kod = (int) ($e->errorInfo[0] ?? 0);
      Log::error('oftt_kontrol_isemri insert hata: ' . $e->getMessage());
      // Son çare: zaten varsa güncelleyelim
      DB::connection('pgsql_oft')
        ->table('oftt_kontrol_isemri')
        ->where('isemri_id', $veri['isEmriID'])
        ->where('istasyon_id', $veri['istasyonID'])
        ->update($kaydet);
    }

    return response()->json(['status' => 'ok']);
  }

  /**
   * PostgreSQL sequence senkronizasyonu düzeltir: nextval, MAX(id)+1 olacak.
   */
  protected function fixSequence(string $table, string $idCol, string $connection = 'pgsql')
  {
    try {
      $sql = "SELECT setval(pg_get_serial_sequence('" . $table . "', '" . $idCol . "'), COALESCE((SELECT MAX(" . $idCol . ") FROM " . $table . "), 0))";
      DB::connection($connection)->unprepared($sql);
    } catch (\Throwable $e) {
      Log::error('Sequence düzeltilemedi: ' . $e->getMessage(), [
        'table' => $table,
        'idCol' => $idCol,
        'connection' => $connection,
      ]);
    }
  }

  public function getUretimData(Request $request)
  {
    // Log::info('Uretim verisi alınıyor:', $request->all());
    $istasyon = $request->istasyon;
    // $istasyonArray = explode(',', $istasyon);

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
        'kalan',
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

    $urunIDs = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->whereBetween(DB::raw('CAST(durum_bas_tarihi AS DATE)'), [$baslangic, $bitis])
      ->distinct()
      ->pluck('urun_id')
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

    // Log::info('Gunluk Toplamlar: ' . $gunlukToplamlar);

    return response()->json([
      'data' => $gunlukToplamlar,
      'hafta' => [$baslangic, $bitis]
    ]);
  }

  public function getUretimPerformans(Request $request)
  {
    $istasyon = $request->istasyon;
    // Log::info($istasyon);

    // $bugun = Carbon::today()->toDateString(); // '2025-07-21'

    $toplam = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_TUM_ISEMIRLERI')
      ->where('IS_ISTASYONU_ID', $istasyon)
      ->whereDate('planlanan_bitis_tarihi', '=', now())
      ->sum('kalan_miktar');

    // Log::info($toplam);

    $tarih = Carbon::today()->toDateString();
    // Log::info('Tarih: ' . $tarih);
    // 1. URUN_ID listesini al
    $urunIDs = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->whereDate('durum_bas_tarihi', '=', $tarih)
      ->distinct()
      ->pluck('urun_id')
      ->toArray();

    if (empty($urunIDs)) {
      return response()->json([
        'bugunKalanToplam' => $toplam,
        'data' => [],
        'message' => 'Seçilen tarihte üretim yok.',
      ]);
    }

    // PostgreSQL uyumlu süre hesaplama: EXTRACT(EPOCH FROM (NOW() - DURUM_BAS_TARIHI)) saniye döner
    $meco = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->selectRaw(<<<SQL
        COALESCE(SUM(
          CASE WHEN durum IN ('ÇALIŞIYOR','HAZIRLIK') THEN
            CASE WHEN DURUM_SURESI = 0 THEN EXTRACT(EPOCH FROM (NOW() - DURUM_BAS_TARIHI))
                 ELSE DURUM_SURESI END
          END
        ) / 60.0, 0) AS calisma,
        COALESCE(SUM(
          CASE WHEN durum = 'DURUYOR' THEN
            CASE WHEN DURUM_SURESI = 0 THEN EXTRACT(EPOCH FROM (NOW() - DURUM_BAS_TARIHI))
                 ELSE DURUM_SURESI END
          END
        ) / 60.0, 0) AS durus
      SQL)
      ->whereDate('durum_bas_tarihi', '=', now()->toDateString())
      ->where('istasyon', $istasyon)
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
      'calisma' => round($meco->calisma, 2),
      'durus' => round($meco->durus, 2),
    ], 200);
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
    // Log::info('Toplu güncelleme tamamlandı.', ['adet' => count($kayitlar)]);
    return response()->json([
      'message' => 'Toplu güncelleme tamamlandı.',
      'adet' => count($kayitlar),
    ]);
  }

  public function getKartlar(Request $request)
  {
    $istasyonId = (int)$request->istasyon;

    // Log::info('İstasyon ID:', ['istasyonId' => $istasyonId]);

    $calismalar = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj as calisma')
      ->join('users', 'calisma.personel_id', '=', 'users.id')
      ->join('oftt_kontrol_isemri', 'calisma.is_emri_id', '=', 'oftt_kontrol_isemri.isemri_id')
      ->select(
        'calisma.is_emri_id',
        'calisma.durum',
        'calisma.durus_sebebi',
        'calisma.durum_bas_tarihi',
        'calisma.durum_suresi',
        'calisma.guid',
        'calisma.personel_id',
        'users.name as personel_adi',
        'oftt_kontrol_isemri.is_use_quality',
        'oftt_kontrol_isemri.is_check_quality_opr'
      )
      ->whereNull('calisma.durum_bit_tarihi')
      ->where('calisma.istasyon', $istasyonId)
      ->limit(100)
      ->get()
      ->groupBy('is_emri_id');

    // Log::info('Çalışmalar:', $calismalar->toArray());

    $isemriIDs = $calismalar->keys()->toArray();
    // Log::info('İş Emirleri ID Listesi:', $isemriIDs);
    $guidList = $calismalar->flatten()->pluck('guid')->unique()->values()->toArray();


    $parcalar = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->select('urun_id')
      ->whereDate('durum_bas_tarihi', '=', Carbon::now()->toDateString())
      ->where('istasyon', $istasyonId)
      ->get()
      ->groupBy('urun_id');

    $parcaIDs = $parcalar->keys()->toArray();

    // Log::info('Parçalar ID Listesi:', $parcaIDs);

    $isEmirleri = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
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

    $sureler = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->select('is_emri_id', 'durum', 'durus_sebebi', 'durum_suresi', 'durum_bas_tarihi')
      // ->whereIn('IS_EMRI_ID', $isemriIDs)
      ->whereIn('guid', $guidList)
      ->orderBy('durum_bas_tarihi')
      ->get()
      ->groupBy('is_emri_id');

    $formatted = $isEmirleri->map(function ($item) use ($sureler, $calismalar) {
      $veriListesi = $sureler[$item->isemri_id] ?? collect();
      $calismaList = $calismalar[$item->isemri_id] ?? collect();
      $sonDurum = $calismaList->last();
      $now = now();

      $status = $sonDurum?->durum;
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
          $row->durum === 'ÇALIŞIYOR' => 'calisma',
          $row->durum === 'HAZIRLIK' => 'ayar',
          $row->durum === 'DURUYOR' => 'durus',
          $row->durum === 'MOLA' => 'mola',
          default => 'kalan',
        };

        $sure = (int) $row->durum_suresi;

        $lastRow = $veriListesi->last();
        if ($sure === 0 && isset($row->durum_bas_tarihi) && $row === $lastRow) {
          $basTarih = Carbon::parse($row->durum_bas_tarihi);
          $sure = $basTarih->diffInSeconds($now);
          $sonDurumSuresi = $sure;
        }

        $barData[] = [
          'type' => $type,
          'duration' => $sure,
          'sebep' => $row->durus_sebebi ?? null,
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
        ->where('guid', $sonDurum->guid ?? null)
        ->whereNull('end_work_time')
        ->count();

      $baslamaData = DB::table('oftt_calisma_sureleri_montaj')
        ->select('durum_bas_tarihi')
        ->where('guid', $sonDurum->guid ?? null)
        ->orderBy('id', 'asc')
        ->first();

      $baslamaZamani = Carbon::parse($baslamaData->durum_bas_tarihi)->format('d-m-Y H:i');

      // Log::info("Kart verisi hazırlanıyor: İş Emri ID {$item->isemri_id}, Parça ID {$item->item_id}, Durum {$status}, Toplam Süre {$barTotal} dakika, Son Durum Süresi {$sonDurumSuresi} saniye, Başlama Zamanı {$baslamaZamani}");
      return [
        'status' => $status,
        'personelId' => $sonDurum->personel_id,
        'personelName' => $sonDurum->personel_adi ?? '?',
        'is_use_quality' => $sonDurum->is_use_quality ?? 'false',
        'is_check_quality_opr' => $sonDurum->is_check_quality_opr ?? 'false',
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
        'sebep' => $sonDurum->durus_sebebi ?? null,
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
        'guid' => $sonDurum->guid ?? null,
        'plnNote' => $item->teknik_not1 ?? '...',
        'prdNote' => $item->teknik_not2 ?? '...',
        'baslangicZamani' => $baslamaZamani,
        'ekipSayisi' => $ekipSayisi ?? 0,
      ];
    });

    // Log::info('Kartlar:', $formatted->toArray());

    return response()->json($formatted);
  }

  public function baslat(Request $request)
  {
    DB::connection('pgsql')->table('oftt_calisma_sureleri_montaj')->insert([
      'is_emri_id' => $request->input('is_emri_id'),
      'sebep' => $request->input('sebep'),
      'durum' => 'Mola',
      'baslangic' => $request->input('baslangic'),
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    return response()->json(['message' => 'Mola kaydedildi']);
  }

  public function DurumlariAl(Request $request)
  {
    // Log::info($request->all());

    $durumlar = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->select('istasyon', 'is_emri_id', 'is_emri_no', 'urun_kodu', 'urun_adi', 'durum', 'durum_bas_tarihi')
      ->whereNull('durum_bit_tarihi')
      ->where('istasyon', $request->istasyon)
      ->get();

    $sureler = DB::connection('pgsql_oft')
      ->table('oftv_gnlk_sureleri_al')
      ->get();

    return response()->json([
      'sureler' => $sureler,
      'durumlar' => $durumlar,
    ], 200);
  }

  public function PersonelSayisiKaydet(Request $request)
  {
    $islem = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->where('istasyon', $request->istasyonKodu)
      ->where('is_emri_id', $request->isEmriId)
      ->where('personel_id', $request->userId)
      ->where('durum_bit_tarihi', null)
      ->orderBy('id', 'desc')
      ->first();

    if ($islem) {
      DB::connection('pgsql_oft')
        ->table('oftt_calisma_sureleri_montaj')
        ->where('id', $islem->id)
        ->update([
          'personel_sayisi' => $request->personelSayisi,
        ]);
    }

    return response()->json(['message' => 'Güncelleme başarılı'], 200);
  }

  public function DurusKaydet(Request $request)
  {
    // Log::info($request->all());

    $islem = DB::connection('pgsql_oft')
      ->table('oftt_calisma_sureleri_montaj')
      ->where('istasyon', $request->istasyonKodu)
      ->where('is_emri_id', $request->isEmriId)
      ->where('personel_id', $request->userId)
      ->where('durum_bit_tarihi', null)
      ->orderBy('id', 'desc')
      ->first();

    if ($islem) {
      DB::connection('pgsql_oft')
        ->table('oftt_calisma_sureleri_montaj')
        ->where('id', $islem->id)
        ->update([
          'durus_sebebi' => $request->selectedDurus['description'] ?? 'GİRİLMEDİ',
          'durus_sebebi_kodu' => $request->selectedDurus['break_reason_code'] ?? '0000',
        ]);
    }

    return response()->json(['message' => 'Güncelleme başarılı'], 200);
  }

  public function DurumKaydet(Request $request)
  {
    //Log::info($request->all());
    $tarih = now();
    $baglanti = DB::connection('pgsql_oft');


    $durum = $request->durum;
    $durusSebebi = is_array($request->selectedDurus) ? $request->selectedDurus['description'] : $request->selectedDurus;
    $durusSebebiKodu = is_array($request->selectedDurus) ? $request->selectedDurus['break_reason_code'] : $request->selectedDurus;

    $onceki = $baglanti
      ->table('oftt_calisma_sureleri_montaj')
      ->where('istasyon', $request->istasyonKodu)
      ->where('guid', $request->guid)
      ->where('personel_id', $request->userId)
      ->whereNull('durum_bit_tarihi')
      ->orderByDesc('id')
      ->first();

    $yeniEkipNo = 1;
    // Log::info('Bulunan önceki kayıt:', (array) $onceki);

    if ($onceki) {
      // Eski kaydı kapat
      $baglanti
        ->table('oftt_calisma_sureleri_montaj')
        ->where('id', $onceki->id)
        ->update([
          'durum_bit_tarihi' => $tarih,
          'durum_suresi' => DB::raw("EXTRACT(EPOCH FROM ('$tarih'::timestamp - durum_bas_tarihi))")
        ]);
    }

    if ($request->durum !== 'KAPANDI') {
      $baglanti
        ->table('oftt_calisma_sureleri_montaj')
        ->insert([
          'istasyon' => $request->istasyonKodu,
          'is_emri_id' => $request->isEmriId,
          'is_emri_no' => $request->isEmriNo,
          'urun_id' => $request->urunID,
          'urun_kodu' => $request->urunKodu,
          'urun_adi' => $request->urunAdi,
          'durum_bas_tarihi' => $tarih,
          'personel_id' => $request->userId,
          'personel_sayisi' => $request->personelSayisi,
          'vardiya' => $request->vardiya,
          'durum' => $durum,
          'durus_sebebi' => $durusSebebi,
          'durus_sebebi_kodu' => $durusSebebiKodu,
          'ekip_no' => $yeniEkipNo,
          'guid' => $request->guid,
        ]);
    }

    return response()->json(['message' => 'Güncelleme başarılı'], 200);
  }
}
