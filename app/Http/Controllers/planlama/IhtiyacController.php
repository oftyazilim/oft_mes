<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;
use Illuminate\Auth\Events\Validated;
use Carbon\Carbon;

class IhtiyacController extends Controller
{
  
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
        ->whereNot('OPERASYON', 'HAYALET')
        ->whereNot('OPERASYON_KODU', 'M12')
        // ->where('is_sub_worder', 0)
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
              'depo_ihtiyaci' => 0,
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
        $list['depo_ihtiyaci'] = ($list['ihtiyac'] - $list['stok']) > 0 ? ($list['ihtiyac'] - $list['stok']) : 0;
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

}
