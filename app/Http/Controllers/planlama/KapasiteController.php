<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;
use Illuminate\Auth\Events\Validated;
use Carbon\Carbon;

class KapasiteController extends Controller
{
  public function getKapasiteParam(Request $request)
  {
    $istasyonlar = DB::connection('pgsql')->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->select('IS_ISTASYONU_ID', 'IS_ISTASYONU')
      // ->where('IS_MERKEZI_KODU', '4001')
      ->orderBy('IS_ISTASYONU')
      ->distinct()
      ->get();

    $tipler = DB::connection('pgsql')->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->select('isemri_tipi')
      ->distinct()
      ->orderBy('isemri_tipi')
      ->get();


    return response()->json([
      'istasyonlar' => $istasyonlar,
      'tipler' => $tipler,
    ], 200);
  }

  public function getKapasiteHaftalar(Request $request)
  {

    $haftalar = DB::connection('pgsql')->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
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
    // Log::info('Kapasite Takvim İsteği:');
    $veri = DB::connection('pgsql_oft')
      ->table('oftt_param_calisma_saatleri_kap')
      ->where('aciklama', 'Çalışma')
      ->select(
        DB::raw("LOWER(gun) as gun"),
        'baslangic_dakikasi as bas',
        'bitis_dakikasi as bit'
      )
      ->orderBy('gun')
      ->orderBy('baslangic_dakikasi')
      ->get();
    // Log::info('Kapasite Takvim Verisi:', ['veri' => $veri]);
    return response()->json($veri);
  }

  public function getKapasiteData(Request $request)
  {
    Log::info('Kapasite Data İsteği:', ['request' => $request->all()]);
    $query = DB::connection('pgsql')->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->where('IS_ISTASYONU_ID', $request->istasyon)
      ->where('hafta', '>=', $request->hafta);

    // Tip filtresi: boş/null gönderildiyse tüm tipler gelir; string gönderildiyse eşit filtre; '(boş)' gibi özel label kullanıldıysa null/empty filtrelenir
    if ($request->tip != 'TÜMÜ') {
        $query->where('isemri_tipi', $request->tip);
    }
    // if ($request->filled('tip') && $request->input('tip') !== 'TÜMÜ') {
    //   $tip = $request->input('tip');
    //   if ($tip === '(boş)' || $tip === '') {
    //     $query->where(function ($q) {
    //       $q->whereNull('isemri_tipi')->orWhere('isemri_tipi', '');
    //     });
    //   } else {
    //     $query->where('isemri_tipi', '=', $tip);
    //   }
    // }

    $data = $query
      // ->where('isemri_tipi', '!=', 'MONTAJ İŞ EMRİ')
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
    // Log::info($request->all());

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
}
