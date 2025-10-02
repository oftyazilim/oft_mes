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

  public function IhtiyacHesaplaToplu(Request $request)
  {
    ini_set('max_execution_time', 1500); // 5 dakika

    // Log::info($request->all());
    try {
      // $anadepolar = DB::connection('pgsql')
      //   ->table('uyumsoft.invd_whouse')
      //   ->select('whouse_id')
      //   ->where('whouse_width', '1')
      //   ->get();

      $satinalmasiparisleri = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_tumsiparisler')
        ->select('STOK_HIZMET_ID', 'MIKTAR', 'KALAN_MIKTAR', 'satici_adi')
        ->where('purchase_sales', '1')
        ->where('order_status', '1')
        ->where('co_id', $request->coID)
        ->get();

      $talepler = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_tumtalepler')
        ->select('item_id', 'miktar')
        ->where('co_id', $request->coID)
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
          'siparis_belge_no',
          'cari_ad',
          DB::raw('SUM(isemri_miktari) AS miktar'),
          DB::raw('SUM(kalan_miktar) AS kalan'),
          DB::raw('0 AS stok'),
          DB::raw('0 AS ana_depo'),
          DB::raw('0 AS diger_depo')
        )
        ->where('co_id', $request->input('coID'))
        ->whereDate('planlanan_bitis_tarihi', '>=', $request->filterValue)
        ->whereDate('planlanan_bitis_tarihi', '<=', $request->filterValue1)
        ->whereNot('OPERASYON', 'HAYALET')
        ->whereNot('OPERASYON_KODU', 'M12')
        ->where('Rotadaki_Son_Operasyon', 1)
        ->when($request->siparis, function ($query, $siparis) {
          return $query->where('siparis_belge_no', $siparis);
        })
        ->when($request->cari, function ($query, $cari) {
          return $query->where('cari_ad', $cari);
        })
        ->groupBy('isemri_id', 'isemri_no', 'stok_id', 'stok_kodu', 'stok_adi', 'siparis_belge_no', 'cari_ad')
        ->orderBy('stok', 'asc')
        ->distinct()
        ->get();

      // Log::info('Emirler:', ['emirler' => $emirler]);
      $bulkUpdates = [];
      $bulkIndex = [];
      $hangiIsemirleri = [];
      $dagilimIndex = [];

      foreach ($emirler as $emir) {
        $liste = DB::connection('pgsql')
          ->table('uyumsoft.OFTV_ISEMIRLERI_MALZEMELER')
          ->select('item_id', 'worder_m_id', 'qty', 'qty_net', 'tipi', 'stok_kodu', 'stok_adi')
          ->where('worder_m_id', $emir->isemri_id)
          ->distinct()
          ->get();

        foreach ($liste as $list) {
          $itemId = (int)$list->item_id;
          $existingKey = $bulkIndex[$itemId] ?? false;
          $dagKey = $itemId . '|' . (int)$list->worder_m_id;
          $existingKey1 = $dagilimIndex[$dagKey] ?? false;

          $qtyNet = (float)($list->qty_net ?? 0);
          $miktar = (float)($emir->miktar ?? 0);
          $kalan  = (float)($emir->kalan ?? 0);

          $hesapMiktari = $miktar > 0 ? ($qtyNet / $miktar) * $kalan : 0.0;

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
            $dagilimIndex[$dagKey] = array_key_last($hangiIsemirleri);
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
              'isemri_miktari' => $emir->miktar,
              'kalan' => $emir->kalan,
              'ihtiyac' => $hesapMiktari,
              'stok' => (float)($list->qty_prm ?? 0),
              'ana_depo' => 0,
              'diger_depo' => 0,
              'satinalma' => 0,
              'satinalmapersoneli' => '',
              'talepler' => 0,
              'bakiye' => 0,
              'dongu' => 1,
              'depo_ihtiyaci' => 0,
            ];
            $bulkIndex[$itemId] = array_key_last($bulkUpdates);
          }
        }
      }

      // Depo bakiyelerini toplu hesapla
      // $anaDepoIds = $anadepolar->pluck('whouse_id')->toArray();
      $itemIds = array_values(array_unique(array_map(function ($r) {
        return (int)$r['item_id'];
      }, $bulkUpdates)));

      // $anaDepoSums = DB::connection('pgsql')
      //   ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
      //   ->select('item_id', DB::raw('SUM(qty_prm) as s'))
      //   ->whereIn('whouse_id', $anaDepoIds)
      //   ->whereIn('item_id', $itemIds)
      //   ->groupBy('item_id')
      //   ->get()
      //   ->keyBy('item_id');

      $nonAnaRows = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
        ->select('item_id', 'whouse_id', DB::raw('SUM(qty_prm) as s'))
        // ->whereNotIn('whouse_id', $anaDepoIds)
        ->whereIn('item_id', $itemIds)
        ->groupBy('item_id', 'whouse_id')
        ->get();

      $nonAnaTotals = [];
      $perWhouse = [];
      foreach ($nonAnaRows as $row) {
        $iid = (int)$row->item_id;
        $wid = (int)$row->whouse_id;
        $sum = (float)$row->s;
        $nonAnaTotals[$iid] = ($nonAnaTotals[$iid] ?? 0) + $sum;
        $perWhouse[$iid] = $perWhouse[$iid] ?? [];
        $perWhouse[$iid][$wid] = $sum;
      }

      // Log::info('AnaDepoSums:', ['sums' => $anaDepoSums]);
      // Log::info('NonAnaTotals:', ['totals' => $nonAnaTotals]);
      // Log::info('PerWhouse:', ['perWhouse' => $perWhouse]);
      // Log::info('PerxxxWhouse:', ['perWhouse' => $bulkUpdates]);

      foreach ($bulkUpdates as &$list) {
        $iid = (int)$list['item_id'];
        // $list['ana_depo'] = isset($anaDepoSums[$iid]) ? (float)$anaDepoSums[$iid]->s : 0.0;
        $otherTotal = (float)($nonAnaTotals[$iid] ?? 0);
        $exclude = 0.0;

        $list['diger_depo'] = max(0.0, $otherTotal - $exclude);

        $satinalma = $satinalmasiparisleri->where('STOK_HIZMET_ID', $iid)->sum('KALAN_MIKTAR');
        $satinalmapersoneli = $satinalmasiparisleri->where('STOK_HIZMET_ID', $iid)->pluck('satici_adi')->first();
        if ($satinalma > 0) {
          $list['satinalma'] = $satinalma ?? 0;
          $list['satinalmapersoneli'] = $satinalmapersoneli ?? '';
        }

        $talep = $talepler->where('item_id', $iid)->sum('miktar');
        if ($talep > 0) {
          $list['talepler'] = $talep ?? 0;
        }

        $anaDepoVal = (float)($list['ana_depo'] ?? 0);
        $digerDepoVal = (float)($list['diger_depo'] ?? 0);
        $stokVal = (float)($list['stok'] ?? 0);
        $ihtiyacVal = (float)($list['ihtiyac'] ?? 0);
        $list['bakiye'] = $digerDepoVal - $ihtiyacVal;
        // $list['depo_ihtiyaci'] = ($ihtiyacVal - $stokVal) > 0 ? ($ihtiyacVal - $stokVal) : 0;
      }
      unset($list);

      return response()->json([
        'message' => 'İhtiyaçlar başarıyla hesaplandı',
        'emirler' => $bulkUpdates,
        'dagilim' => $hangiIsemirleri,
      ], 200);
    } catch (\Exception $e) {
      Log::error('IhtiyacHesapla - Hata', [
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
      ]);
      return response()->json([
        'error' => 'İşlem sırasında hata oluştu!',
        'details' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
      ], 500);
    }
  }

  public function IhtiyacHesaplaTest(Request $request)
  {
    ini_set('max_execution_time', 1500); // 5 dakika

    // Log::info($request->all());
    try {
      $anadepolar = DB::connection('pgsql')
        ->table('uyumsoft.invd_whouse')
        ->select('whouse_id')
        ->where('whouse_width', '1')
        ->get();

      // $satinalmasiparisleri = DB::connection('pgsql')
      //   ->table('uyumsoft.zz_bk_tumsiparisler')
      //   ->select('STOK_HIZMET_ID', 'MIKTAR', 'KALAN_MIKTAR', 'satici_adi')
      //   ->where('purchase_sales', '1')
      //   ->where('order_status', '1')
      //   ->where('co_id', $request->coID)
      //   ->get();

      // $talepler = DB::connection('pgsql')
      //   ->table('uyumsoft.zz_bk_tumtalepler')
      //   ->select('item_id', 'miktar')
      //   ->where('co_id', $request->coID)
      //   ->where('ONAY_DURUMU', 'Onaylandi')
      //   ->get();


      // $emirler = DB::connection('pgsql')
      //   ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      //   ->select(
      //     'isemri_id',
      //     'isemri_no',
      //     'stok_id',
      //     'stok_kodu',
      //     'stok_adi',
      //     'CIKIS_DEPO',
      //     'siparis_belge_no',
      //     'cari_ad',
      //     DB::raw("concat_ws('-', \"IS_MERKEZI_KODU\", \"IS_MERKEZI_ADI\") as mrk_adi"),
      //     DB::raw('SUM(isemri_miktari) AS miktar'),
      //     DB::raw('SUM(kalan_miktar) AS kalan'),
      //     DB::raw('0 AS stok'),
      //     DB::raw('0 AS ana_depo'),
      //     DB::raw('0 AS diger_depo')
      //   )
      //   // ->where('isemri_no', 'IEN-25023614')
      //   ->where('co_id', $request->input('coID'))
      //   ->whereDate('planlanan_bitis_tarihi', '>=', $request->filterValue)
      //   ->whereDate('planlanan_bitis_tarihi', '<=', $request->filterValue1)
      //   ->where('IS_MERKEZI_ID', $request->merkezID)
      //   ->where('IS_ISTASYONU_ID', $request->istasyonID)
      //   ->when($request->siparis, function ($query, $siparis) {
      //     return $query->where('siparis_belge_no', $siparis);
      //   })
      //   ->when($request->cari, function ($query, $cari) {
      //     return $query->where('cari_ad', $cari);
      //   })
      //   ->when($request->isemriNo, function ($query, $isemriNo) {
      //     return $query->where('isemri_no', $isemriNo);
      //   })
      //   ->whereNot('OPERASYON', 'HAYALET')
      //   ->whereNot('OPERASYON_KODU', 'M12')
      //   ->where('Rotadaki_Son_Operasyon', 1)
      //   ->groupBy('isemri_id', 'isemri_no', 'stok_id', 'stok_kodu', 'stok_adi', 'CIKIS_DEPO', 'siparis_belge_no', 'cari_ad', 'IS_MERKEZI_KODU', 'IS_MERKEZI_ADI')
      //   ->orderBy('stok', 'asc')
      //   ->distinct()
      //   ->get();


      // Log::info('Emirler:', ['emirler' => $emirler]);

      // Güvenlik / süre ayarları (büyük sorgular için)
      set_time_limit(300); // ihtiyaç varsa arttır
      ini_set('memory_limit', '512M');

      // Parametreler (isteğe bağlı, defaultlar verildi)
      $worder_m_id = $request->input('isemriNo', 356850);
      $whouse_id    = $request->input('whouse_id', 3702);
      $wstation_id  = $request->input('wstation_id', 390);
      $whouse_width = $request->input('whouse_width', [1, 2, 3, 4]);
      $whouse_width_arr = array_map('intval', (array)$whouse_width);
      $in = implode(',', $whouse_width_arr);

      $sql = <<<SQL
select
  case
    ite.categories1_id
      when 2626 then 'Hammadde'::text
      when 2631 then 'YarıMamul'::text
      when 3566 then 'Ara Mamul'::text
      when 2627 then 'Mamul'::text
      when 2628 then 'Ticari'::text
      when 2634 then 'Sarf'::text
      else null::text
  end as tipi,
  wc.wcenter_code::text || wc.description::text AS is_merkezi,
  wbd.worder_m_id,
  pwm.worder_no,
  ibi.whouse_id,
  wbd.item_id,
  max(wbd.qty_base_bom) as qty_base_bom,
  max(wbd.qty_net) as qty_net,
  wbd.unit_id,
  sum(wbd.qty_scrap) as qty_scrap,
  wbd.operation_id,
  wbd.operation_no,
  ite.item_code as stok_kodu,
  ite.item_name as stok_adi,
  sum(ibi.qty_prm) as qty_prm,
  max(ibi.qty_max_inv) as qty_max_inv,
  max(ibi.qty_min_inv) as qty_min_inv,
  max(pwm.qty) as qty,
  max(pwm.qty - pwm.qty_man_net) as kalan,
  (max(pwm.qty) - max(pwm.qty_man_net)) * max(wbd.qty_base_bom) as ihtiyac
from uyumsoft.prdt_worder_bom_d wbd
join uyumsoft.invd_item ite on wbd.item_id = ite.item_id
left join uyumsoft.invd_bwh_item ibi on ite.item_id = ibi.item_id
left join uyumsoft.invd_whouse iw on iw.whouse_id = ibi.whouse_id
left join uyumsoft.prdt_worder_m pwm on pwm.worder_m_id = wbd.worder_m_id
left join uyumsoft.prdt_worder_op_d wop on wop.worder_m_id = pwm.worder_m_id
left join uyumsoft.prdd_wcenter wc on wc.wcenter_id = wop.wcenter_id
where
  iw.whouse_width IN ($in)
  and (pwm.worder_m_id = :worder_m_id or pwm.root_worder_m_id = :worder_m_id)
  and ibi.whouse_id = :whouse_id
  and wop.wstation_id = :wstation_id
group by
  case
    ite.categories1_id
      when 2626 then 'Hammadde'::text
      when 2631 then 'YarıMamul'::text
      when 3566 then 'Ara Mamul'::text
      when 2627 then 'Mamul'::text
      when 2628 then 'Ticari'::text
      when 2634 then 'Sarf'::text
      else null::text
  end,
  wc.wcenter_code::text || wc.description::text,
  wbd.worder_m_id,
  pwm.worder_no,
  wbd.item_id,
  wbd.unit_id,
  ibi.whouse_id,
  wbd.operation_id,
  wbd.operation_no,
  ite.item_code,
  ite.item_name
SQL;

      // Log::info('IhtiyacHesaplaTest SQL:', ['sql' => $sql, 'params' => [
      //   'worder_m_id' => intval($worder_m_id),
      //   'whouse_id' => intval($whouse_id),
      //   'wstation_id' => intval($wstation_id),
      // ]]);

      $rows = DB::connection('pgsql')->select($sql, [
        'worder_m_id' => intval($worder_m_id),
        'whouse_id' => intval($whouse_id),
        'wstation_id' => intval($wstation_id),
      ]);

      Log::info('IhtiyacHesaplaTest:', ['rows' => $rows]);
      return response()->json([
        'success' => true,
        'count' => count($rows),
        'emirler' => $rows,
      ]);
    } catch (\Throwable $e) {
      Log::error('bomSummary error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return response()->json([
        'success' => false,
        'error' => $e->getMessage(),
      ], 500);
    }










    //   $bulkUpdates = [];
    //   $bulkIndex = [];
    //   $hangiIsemirleri = [];
    //   $dagilimIndex = [];

    //   foreach ($emirler as $emir) {
    //     $cikisDepo = isset($emir->CIKIS_DEPO) ? $emir->CIKIS_DEPO : null;

    //     $listeSelect = ['item_id', 'worder_m_id', 'qty_prm', 'qty_net', 'tipi', 'stok_kodu', 'stok_adi'];

    //     $liste = DB::connection('pgsql')
    //       ->table('uyumsoft.OFTV_ISEMIRLERI_MALZEMELER')
    //       ->select($listeSelect)
    //       ->where('worder_m_id', $emir->isemri_id)
    //       ->where('whouse_id', $cikisDepo)
    //       ->distinct()
    //       ->get();

    //     foreach ($liste as $list) {
    //       // O(1) indexler
    //       $itemId = (int)$list->item_id;
    //       $existingKey = $bulkIndex[$itemId] ?? false;
    //       // hangiIsemirleri: (item_id|isemri_id)
    //       $dagKey = $itemId . '|' . (int)$list->worder_m_id;
    //       $existingKey1 = $dagilimIndex[$dagKey] ?? false;

    //       // Güvenli hesap (0'a bölme yok)
    //       $qtyNet = (float)($list->qty_net ?? 0);
    //       $miktar = (float)($emir->miktar ?? 0);
    //       $kalan  = (float)($emir->kalan ?? 0);

    //       $hesapMiktari = $miktar > 0 ? ($qtyNet / $miktar) * $kalan : 0.0;

    //       if ($existingKey1 !== false) {
    //         $hangiIsemirleri[$existingKey1]['isemri_miktari'] += $emir->miktar;
    //       } else {
    //         $hangiIsemirleri[] = [
    //           'item_id' => $list->item_id,
    //           'isemri_id' => $list->worder_m_id,
    //           'isemri_no' => $emir->isemri_no,
    //           'siparis_belge_no' => $emir->siparis_belge_no,
    //           'cari_ad' => $emir->cari_ad,
    //           'isemri_miktari' => $emir->miktar,
    //           'kalan' => $emir->kalan,
    //           'stok_id' => $emir->stok_id,
    //           'stok_kodu' => $emir->stok_kodu,
    //           'stok_adi' => $emir->stok_adi,
    //         ];
    //         $dagilimIndex[$dagKey] = array_key_last($hangiIsemirleri);
    //       }

    //       if ($existingKey !== false) {
    //         $bulkUpdates[$existingKey]['isemri_miktari'] += $emir->miktar;
    //         $bulkUpdates[$existingKey]['kalan'] += $emir->kalan;
    //         $bulkUpdates[$existingKey]['ihtiyac'] += $hesapMiktari;
    //         $bulkUpdates[$existingKey]['dongu'] += 1;
    //       } else {
    //         $bulkUpdates[] = [
    //           'item_id' => $list->item_id,
    //           'tipi' => $list->tipi,
    //           'stok_kodu' => $list->stok_kodu,
    //           'stok_adi' => $list->stok_adi,
    //           'mrk_adi' => $emir->mrk_adi,
    //           'cikis_depo' => $cikisDepo,
    //           'isemri_miktari' => $emir->miktar,
    //           'kalan' => $emir->kalan,
    //           'ihtiyac' => $hesapMiktari,
    //           'stok' => (float)($list->qty_prm ?? 0),
    //           'ana_depo' => 0,
    //           'diger_depo' => 0,
    //           'satinalma' => 0,
    //           'satinalmapersoneli' => '',
    //           'talepler' => 0,
    //           'bakiye' => 0,
    //           'dongu' => 1,
    //           'depo_ihtiyaci' => 0,
    //         ];
    //         $bulkIndex[$itemId] = array_key_last($bulkUpdates);
    //       }
    //     }
    //   }

    //   // Depo bakiyelerini toplu hesapla
    //   $anaDepoIds = $anadepolar->pluck('whouse_id')->toArray();
    //   $itemIds = array_values(array_unique(array_map(function ($r) {
    //     return (int)$r['item_id'];
    //   }, $bulkUpdates)));

    //   $anaDepoSums = DB::connection('pgsql')
    //     ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
    //     ->select('item_id', DB::raw('SUM(qty_prm) as s'))
    //     ->whereIn('whouse_id', $anaDepoIds)
    //     ->whereIn('item_id', $itemIds)
    //     ->groupBy('item_id')
    //     ->get()
    //     ->keyBy('item_id');

    //   $nonAnaRows = DB::connection('pgsql')
    //     ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
    //     ->select('item_id', 'whouse_id', DB::raw('SUM(qty_prm) as s'))
    //     ->whereNotIn('whouse_id', $anaDepoIds)
    //     ->whereIn('item_id', $itemIds)
    //     ->groupBy('item_id', 'whouse_id')
    //     ->get();

    //   $nonAnaTotals = [];
    //   $perWhouse = [];
    //   foreach ($nonAnaRows as $row) {
    //     $iid = (int)$row->item_id;
    //     $wid = (int)$row->whouse_id;
    //     $sum = (float)$row->s;
    //     $nonAnaTotals[$iid] = ($nonAnaTotals[$iid] ?? 0) + $sum;
    //     $perWhouse[$iid] = $perWhouse[$iid] ?? [];
    //     $perWhouse[$iid][$wid] = $sum;
    //   }

    //   // Log::info('AnaDepoSums:', ['sums' => $anaDepoSums]);
    //   // Log::info('NonAnaTotals:', ['totals' => $nonAnaTotals]);
    //   // Log::info('PerWhouse:', ['perWhouse' => $perWhouse]);
    //   // Log::info('PerxxxWhouse:', ['perWhouse' => $bulkUpdates]);

    //   foreach ($bulkUpdates as &$list) {
    //     $iid = (int)$list['item_id'];
    //     $list['ana_depo'] = isset($anaDepoSums[$iid]) ? (float)$anaDepoSums[$iid]->s : 0.0;
    //     $otherTotal = (float)($nonAnaTotals[$iid] ?? 0);
    //     $exclude = 0.0;
    //     if (!empty($list['cikis_depo'])) {
    //       $cd = (int)$list['cikis_depo'];
    //       $exclude = (float)($perWhouse[$iid][$cd] ?? 0);
    //     }
    //     $list['diger_depo'] = max(0.0, $otherTotal - $exclude);

    //     $satinalma = $satinalmasiparisleri->where('STOK_HIZMET_ID', $iid)->sum('KALAN_MIKTAR');
    //     $satinalmapersoneli = $satinalmasiparisleri->where('STOK_HIZMET_ID', $iid)->pluck('satici_adi')->first();
    //     if ($satinalma > 0) {
    //       $list['satinalma'] = $satinalma ?? 0;
    //       $list['satinalmapersoneli'] = $satinalmapersoneli ?? '';
    //     }

    //     $talep = $talepler->where('item_id', $iid)->sum('miktar');
    //     if ($talep > 0) {
    //       $list['talepler'] = $talep ?? 0;
    //     }

    //     $anaDepoVal = (float)($list['ana_depo'] ?? 0);
    //     $stokVal = (float)($list['stok'] ?? 0);
    //     $ihtiyacVal = (float)($list['ihtiyac'] ?? 0);
    //     $list['bakiye'] = ($anaDepoVal + $stokVal) - $ihtiyacVal;
    //     $list['depo_ihtiyaci'] = ($ihtiyacVal - $stokVal) > 0 ? ($ihtiyacVal - $stokVal) : 0;
    //   }
    //   unset($list);

    //   return response()->json([
    //     'message' => 'İhtiyaçlar başarıyla hesaplandı',
    //     'emirler' => $bulkUpdates,
    //     'dagilim' => $hangiIsemirleri,
    //   ], 200);
    // } catch (\Exception $e) {
    //   Log::error('IhtiyacHesapla - Hata', [
    //     'message' => $e->getMessage(),
    //     'line' => $e->getLine(),
    //     'file' => $e->getFile(),
    //   ]);
    //   return response()->json([
    //     'error' => 'İşlem sırasında hata oluştu!',
    //     'details' => $e->getMessage(),
    //     'line' => $e->getLine(),
    //     'file' => $e->getFile()
    //   ], 500);
    // }
  }

  // public function IhtiyacHesaplama(Request $request)
  // {
  //   ini_set('max_execution_time', 1500); // 5 dakika

  //   // Log::info($request->all());
  //   try {
  //     $anadepolar = DB::connection('pgsql')
  //       ->table('uyumsoft.invd_whouse')
  //       ->select('whouse_id')
  //       ->where('whouse_width', '1')
  //       ->get();

  //     $satinalmasiparisleri = DB::connection('pgsql')
  //       ->table('uyumsoft.zz_bk_tumsiparisler')
  //       ->select('STOK_HIZMET_ID', 'MIKTAR', 'KALAN_MIKTAR', 'satici_adi')
  //       ->where('purchase_sales', '1')
  //       ->where('order_status', '1')
  //       ->where('co_id', $request->coID)
  //       ->get();

  //     $talepler = DB::connection('pgsql')
  //       ->table('uyumsoft.zz_bk_tumtalepler')
  //       ->select('item_id', 'miktar')
  //       ->where('co_id', $request->coID)
  //       ->where('ONAY_DURUMU', 'Onaylandi')
  //       ->get();


  //     $emirler = DB::connection('pgsql')
  //       ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
  //       ->select(
  //         'isemri_id',
  //         'isemri_no',
  //         'stok_id',
  //         'stok_kodu',
  //         'stok_adi',
  //         'CIKIS_DEPO',
  //         'siparis_belge_no',
  //         'cari_ad',
  //         DB::raw("concat_ws('-', \"IS_MERKEZI_KODU\", \"IS_MERKEZI_ADI\") as mrk_adi"),
  //         DB::raw('SUM(isemri_miktari) AS miktar'),
  //         DB::raw('SUM(kalan_miktar) AS kalan'),
  //         DB::raw('0 AS stok'),
  //         DB::raw('0 AS ana_depo'),
  //         DB::raw('0 AS diger_depo')
  //       )
  //       ->where('co_id', $request->input('coID'))
  //       ->whereDate('planlanan_bitis_tarihi', '>=', $request->filterValue)
  //       ->whereDate('planlanan_bitis_tarihi', '<=', $request->filterValue1)
  //       ->when($request->merkezID, function ($query, $merkezID) {
  //         return $query->where('IS_MERKEZI_ID', $merkezID);
  //       })
  //       ->when($request->istasyonID, function ($query, $istasyonID) {
  //         $istasyonArray = explode(',', $istasyonID);
  //         return $query->whereIn('IS_ISTASYONU_ID', $istasyonArray);
  //       })
  //       ->when($request->siparis, function ($query, $siparis) {
  //         return $query->where('siparis_belge_no', $siparis);
  //       })
  //       ->when($request->cari, function ($query, $cari) {
  //         return $query->where('cari_ad', $cari);
  //       })
  //       ->whereNot('OPERASYON', 'HAYALET')
  //       ->whereNot('OPERASYON_KODU', 'M12')
  //       ->where('Rotadaki_Son_Operasyon', 1)
  //       ->groupBy('isemri_id', 'isemri_no', 'stok_id', 'stok_kodu', 'stok_adi', 'CIKIS_DEPO', 'siparis_belge_no', 'cari_ad', 'IS_MERKEZI_KODU', 'IS_MERKEZI_ADI')
  //       ->orderBy('stok', 'asc')
  //       ->distinct()
  //       ->get();

  //     // Log::info('Emirler:', ['emirler' => $emirler]);
  //     $bulkUpdates = [];
  //     $bulkIndex = [];
  //     $hangiIsemirleri = [];
  //     $dagilimIndex = [];

  //     foreach ($emirler as $emir) {
  //       // CIKIS_DEPO olmayabilir; güvenli kullan
  //       $cikisDepo = isset($emir->CIKIS_DEPO) ? $emir->CIKIS_DEPO : null;

  //       // select listesi: çıkış deposu varsa qty_prm'yi de dahil et, yoksa ekleme
  //       $listeSelect = ['item_id', 'worder_m_id', 'qty', 'qty_net', 'tipi', 'stok_kodu', 'stok_adi'];
  //       if (!is_null($cikisDepo)) {
  //         $listeSelect[] = 'qty_prm';
  //       }

  //       $liste = DB::connection('pgsql')
  //         ->table('uyumsoft.OFTV_ISEMIRLERI_MALZEMELER')
  //         ->select($listeSelect)
  //         ->where('worder_m_id', $emir->isemri_id)
  //         ->when($cikisDepo, function ($q) use ($cikisDepo) {
  //           return $q->where('whouse_id', $cikisDepo);
  //         })
  //         ->distinct()
  //         ->get();

  //       foreach ($liste as $list) {
  //         // O(1) indexler
  //         $itemId = (int)$list->item_id;
  //         $existingKey = $bulkIndex[$itemId] ?? false;
  //         // hangiIsemirleri: (item_id|isemri_id)
  //         $dagKey = $itemId . '|' . (int)$list->worder_m_id;
  //         $existingKey1 = $dagilimIndex[$dagKey] ?? false;

  //         // Güvenli hesap (0'a bölme yok)
  //         $qtyNet = (float)($list->qty_net ?? 0);
  //         $miktar = (float)($emir->miktar ?? 0);
  //         $kalan  = (float)($emir->kalan ?? 0);

  //         $hesapMiktari = $miktar > 0 ? ($qtyNet / $miktar) * $kalan : 0.0;

  //         if ($existingKey1 !== false) {
  //           // $hangiIsemirleri[$existingKey1]['isemri_miktari'] += $emir->miktar;
  //         } else {
  //           $hangiIsemirleri[] = [
  //             'item_id' => $list->item_id,
  //             'isemri_id' => $list->worder_m_id,
  //             'isemri_no' => $emir->isemri_no,
  //             'siparis_belge_no' => $emir->siparis_belge_no,
  //             'cari_ad' => $emir->cari_ad,
  //             'isemri_miktari' => $emir->miktar,
  //             'kalan' => $emir->kalan,
  //             'stok_id' => $emir->stok_id,
  //             'stok_kodu' => $emir->stok_kodu,
  //             'stok_adi' => $emir->stok_adi,
  //           ];
  //           $dagilimIndex[$dagKey] = array_key_last($hangiIsemirleri);
  //         }

  //         if ($existingKey !== false) {
  //           $bulkUpdates[$existingKey]['isemri_miktari'] += $emir->miktar;
  //           $bulkUpdates[$existingKey]['kalan'] += $emir->kalan;
  //           $bulkUpdates[$existingKey]['ihtiyac'] += $hesapMiktari;
  //           $bulkUpdates[$existingKey]['dongu'] += 1;
  //         } else {
  //           $bulkUpdates[] = [
  //             'item_id' => $list->item_id,
  //             'tipi' => $list->tipi,
  //             'stok_kodu' => $list->stok_kodu,
  //             'stok_adi' => $list->stok_adi,
  //             'mrk_adi' => $emir->mrk_adi,
  //             'cikis_depo' => $cikisDepo,
  //             'isemri_miktari' => $emir->miktar,
  //             'kalan' => $emir->kalan,
  //             'ihtiyac' => $hesapMiktari,
  //             'stok' => (float)($list->qty_prm ?? 0),
  //             'ana_depo' => 0,
  //             'diger_depo' => 0,
  //             'satinalma' => 0,
  //             'satinalmapersoneli' => '',
  //             'talepler' => 0,
  //             'bakiye' => 0,
  //             'dongu' => 1,
  //             'depo_ihtiyaci' => 0,
  //           ];
  //           $bulkIndex[$itemId] = array_key_last($bulkUpdates);
  //         }
  //       }
  //     }

  //     // Depo bakiyelerini toplu hesapla
  //     $anaDepoIds = $anadepolar->pluck('whouse_id')->toArray();
  //     $itemIds = array_values(array_unique(array_map(function ($r) {
  //       return (int)$r['item_id'];
  //     }, $bulkUpdates)));

  //     $anaDepoSums = DB::connection('pgsql')
  //       ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
  //       ->select('item_id', DB::raw('SUM(qty_prm) as s'))
  //       ->whereIn('whouse_id', $anaDepoIds)
  //       ->whereIn('item_id', $itemIds)
  //       ->groupBy('item_id')
  //       ->get()
  //       ->keyBy('item_id');

  //     $nonAnaRows = DB::connection('pgsql')
  //       ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
  //       ->select('item_id', 'whouse_id', DB::raw('SUM(qty_prm) as s'))
  //       ->whereNotIn('whouse_id', $anaDepoIds)
  //       ->whereIn('item_id', $itemIds)
  //       ->groupBy('item_id', 'whouse_id')
  //       ->get();

  //     $nonAnaTotals = [];
  //     $perWhouse = [];
  //     foreach ($nonAnaRows as $row) {
  //       $iid = (int)$row->item_id;
  //       $wid = (int)$row->whouse_id;
  //       $sum = (float)$row->s;
  //       $nonAnaTotals[$iid] = ($nonAnaTotals[$iid] ?? 0) + $sum;
  //       $perWhouse[$iid] = $perWhouse[$iid] ?? [];
  //       $perWhouse[$iid][$wid] = $sum;
  //     }

  //     Log::info('AnaDepoSums:', ['sums' => $anaDepoSums]);
  //     Log::info('NonAnaTotals:', ['totals' => $nonAnaTotals]);
  //     Log::info('PerWhouse:', ['perWhouse' => $perWhouse]);
  //     Log::info('PerxxxWhouse:', ['perWhouse' => $bulkUpdates]);

  //     foreach ($bulkUpdates as &$list) {
  //       $iid = (int)$list['item_id'];
  //       $list['ana_depo'] = isset($anaDepoSums[$iid]) ? (float)$anaDepoSums[$iid]->s : 0.0;
  //       $otherTotal = (float)($nonAnaTotals[$iid] ?? 0);
  //       $exclude = 0.0;
  //       if (!empty($list['cikis_depo'])) {
  //         $cd = (int)$list['cikis_depo'];
  //         $exclude = (float)($perWhouse[$iid][$cd] ?? 0);
  //       }
  //       $list['diger_depo'] = max(0.0, $otherTotal - $exclude);

  //       $satinalma = $satinalmasiparisleri->where('STOK_HIZMET_ID', $iid)->sum('KALAN_MIKTAR');
  //       $satinalmapersoneli = $satinalmasiparisleri->where('STOK_HIZMET_ID', $iid)->pluck('satici_adi')->first();
  //       if ($satinalma > 0) {
  //         $list['satinalma'] = $satinalma ?? 0;
  //         $list['satinalmapersoneli'] = $satinalmapersoneli ?? '';
  //       }

  //       $talep = $talepler->where('item_id', $iid)->sum('miktar');
  //       if ($talep > 0) {
  //         $list['talepler'] = $talep ?? 0;
  //       }

  //       $anaDepoVal = (float)($list['ana_depo'] ?? 0);
  //       $stokVal = (float)($list['stok'] ?? 0);
  //       $ihtiyacVal = (float)($list['ihtiyac'] ?? 0);
  //       $list['bakiye'] = ($anaDepoVal + $stokVal) - $ihtiyacVal;
  //       $list['depo_ihtiyaci'] = ($ihtiyacVal - $stokVal) > 0 ? ($ihtiyacVal - $stokVal) : 0;
  //     }
  //     unset($list);

  //     return response()->json([
  //       'message' => 'İhtiyaçlar başarıyla hesaplandı',
  //       'emirler' => $bulkUpdates,
  //       'dagilim' => $hangiIsemirleri,
  //     ], 200);
  //   } catch (\Exception $e) {
  //     Log::error('IhtiyacHesapla - Hata', [
  //       'message' => $e->getMessage(),
  //       'line' => $e->getLine(),
  //       'file' => $e->getFile(),
  //     ]);
  //     return response()->json([
  //       'error' => 'İşlem sırasında hata oluştu!',
  //       'details' => $e->getMessage(),
  //       'line' => $e->getLine(),
  //       'file' => $e->getFile()
  //     ], 500);
  //   }
  // }

  public function IhtiyacHesaplaIstasyon(Request $request)
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
        ->where('co_id', $request->coID)
        ->get();

      $talepler = DB::connection('pgsql')
        ->table('uyumsoft.zz_bk_tumtalepler')
        ->select('item_id', 'miktar')
        ->where('co_id', $request->coID)
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
        // ->where('isemri_no', 'IEN-25023614')
        ->where('co_id', $request->input('coID'))
        ->whereDate('planlanan_bitis_tarihi', '>=', $request->filterValue)
        ->whereDate('planlanan_bitis_tarihi', '<=', $request->filterValue1)
        ->where('IS_MERKEZI_ID', $request->merkezID)
        ->where('IS_ISTASYONU_ID', $request->istasyonID)
        ->when($request->siparis, function ($query, $siparis) {
          return $query->where('siparis_belge_no', $siparis);
        })
        ->when($request->cari, function ($query, $cari) {
          return $query->where('cari_ad', $cari);
        })
        ->when($request->isemriNo, function ($query, $isemriNo) {
          return $query->where('isemri_no', $isemriNo);
        })
        ->whereNot('OPERASYON', 'HAYALET')
        ->whereNot('OPERASYON_KODU', 'M12')
        ->where('Rotadaki_Son_Operasyon', 1)
        ->groupBy('isemri_id', 'isemri_no', 'stok_id', 'stok_kodu', 'stok_adi', 'CIKIS_DEPO', 'siparis_belge_no', 'cari_ad', 'IS_MERKEZI_KODU', 'IS_MERKEZI_ADI')
        ->orderBy('stok', 'asc')
        ->distinct()
        ->get();

      // Log::info('Emirler:', ['emirler' => $emirler]);
      $bulkUpdates = [];
      $bulkIndex = [];
      $hangiIsemirleri = [];
      $dagilimIndex = [];

      foreach ($emirler as $emir) {
        $cikisDepo = isset($emir->CIKIS_DEPO) ? $emir->CIKIS_DEPO : null;

        $listeSelect = ['item_id', 'worder_m_id', 'qty_prm', 'qty_net', 'tipi', 'stok_kodu', 'stok_adi'];

        $liste = DB::connection('pgsql')
          ->table('uyumsoft.OFTV_ISEMIRLERI_MALZEMELER')
          ->select($listeSelect)
          ->where('worder_m_id', $emir->isemri_id)
          ->where('whouse_id', $cikisDepo)
          ->distinct()
          ->get();

        foreach ($liste as $list) {
          // O(1) indexler
          $itemId = (int)$list->item_id;
          $existingKey = $bulkIndex[$itemId] ?? false;
          // hangiIsemirleri: (item_id|isemri_id)
          $dagKey = $itemId . '|' . (int)$list->worder_m_id;
          $existingKey1 = $dagilimIndex[$dagKey] ?? false;

          // Güvenli hesap (0'a bölme yok)
          $qtyNet = (float)($list->qty_net ?? 0);
          $miktar = (float)($emir->miktar ?? 0);
          $kalan  = (float)($emir->kalan ?? 0);

          $hesapMiktari = $miktar > 0 ? ($qtyNet / $miktar) * $kalan : 0.0;

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
            $dagilimIndex[$dagKey] = array_key_last($hangiIsemirleri);
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
              'cikis_depo' => $cikisDepo,
              'isemri_miktari' => $emir->miktar,
              'kalan' => $emir->kalan,
              'ihtiyac' => $hesapMiktari,
              'stok' => (float)($list->qty_prm ?? 0),
              'ana_depo' => 0,
              'diger_depo' => 0,
              'satinalma' => 0,
              'satinalmapersoneli' => '',
              'talepler' => 0,
              'bakiye' => 0,
              'dongu' => 1,
              'depo_ihtiyaci' => 0,
            ];
            $bulkIndex[$itemId] = array_key_last($bulkUpdates);
          }
        }
      }

      // Depo bakiyelerini toplu hesapla
      $anaDepoIds = $anadepolar->pluck('whouse_id')->toArray();
      $itemIds = array_values(array_unique(array_map(function ($r) {
        return (int)$r['item_id'];
      }, $bulkUpdates)));

      $anaDepoSums = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
        ->select('item_id', DB::raw('SUM(qty_prm) as s'))
        ->whereIn('whouse_id', $anaDepoIds)
        ->whereIn('item_id', $itemIds)
        ->groupBy('item_id')
        ->get()
        ->keyBy('item_id');

      $nonAnaRows = DB::connection('pgsql')
        ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
        ->select('item_id', 'whouse_id', DB::raw('SUM(qty_prm) as s'))
        ->whereNotIn('whouse_id', $anaDepoIds)
        ->whereIn('item_id', $itemIds)
        ->groupBy('item_id', 'whouse_id')
        ->get();

      $nonAnaTotals = [];
      $perWhouse = [];
      foreach ($nonAnaRows as $row) {
        $iid = (int)$row->item_id;
        $wid = (int)$row->whouse_id;
        $sum = (float)$row->s;
        $nonAnaTotals[$iid] = ($nonAnaTotals[$iid] ?? 0) + $sum;
        $perWhouse[$iid] = $perWhouse[$iid] ?? [];
        $perWhouse[$iid][$wid] = $sum;
      }

      // Log::info('AnaDepoSums:', ['sums' => $anaDepoSums]);
      // Log::info('NonAnaTotals:', ['totals' => $nonAnaTotals]);
      // Log::info('PerWhouse:', ['perWhouse' => $perWhouse]);
      // Log::info('PerxxxWhouse:', ['perWhouse' => $bulkUpdates]);

      foreach ($bulkUpdates as &$list) {
        $iid = (int)$list['item_id'];
        $list['ana_depo'] = isset($anaDepoSums[$iid]) ? (float)$anaDepoSums[$iid]->s : 0.0;
        $otherTotal = (float)($nonAnaTotals[$iid] ?? 0);
        $exclude = 0.0;
        if (!empty($list['cikis_depo'])) {
          $cd = (int)$list['cikis_depo'];
          $exclude = (float)($perWhouse[$iid][$cd] ?? 0);
        }
        $list['diger_depo'] = max(0.0, $otherTotal - $exclude);

        $satinalma = $satinalmasiparisleri->where('STOK_HIZMET_ID', $iid)->sum('KALAN_MIKTAR');
        $satinalmapersoneli = $satinalmasiparisleri->where('STOK_HIZMET_ID', $iid)->pluck('satici_adi')->first();
        if ($satinalma > 0) {
          $list['satinalma'] = $satinalma ?? 0;
          $list['satinalmapersoneli'] = $satinalmapersoneli ?? '';
        }

        $talep = $talepler->where('item_id', $iid)->sum('miktar');
        if ($talep > 0) {
          $list['talepler'] = $talep ?? 0;
        }

        $anaDepoVal = (float)($list['ana_depo'] ?? 0);
        $stokVal = (float)($list['stok'] ?? 0);
        $ihtiyacVal = (float)($list['ihtiyac'] ?? 0);
        $list['bakiye'] = ($anaDepoVal + $stokVal) - $ihtiyacVal;
        $list['depo_ihtiyaci'] = ($ihtiyacVal - $stokVal) > 0 ? ($ihtiyacVal - $stokVal) : 0;
      }
      unset($list);

      return response()->json([
        'message' => 'İhtiyaçlar başarıyla hesaplandı',
        'emirler' => $bulkUpdates,
        'dagilim' => $hangiIsemirleri,
      ], 200);
    } catch (\Exception $e) {
      Log::error('IhtiyacHesapla - Hata', [
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
      ]);
      return response()->json([
        'error' => 'İşlem sırasında hata oluştu!',
        'details' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
      ], 500);
    }
  }

  public function getMerkezler(Request $request)
  {
    // Log::info($request->all());
    $merkezler = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_IS_ISTASYONLARI')
      ->select(
        'is_merkezi_id',
        'ismerkezi_kodu',
        DB::raw("concat_ws('-', ismerkezi_kodu, ismerkezi_adi) as mrk_adi") // 1200 ve istasyon_adi birleştirildi
      )
      ->when($request->coID, function ($query, $coID) {
        return $query->where('firma_id', $coID);
      })
      ->where('firma_id', $request->coID)
      ->orderBy('is_merkezi_id')
      ->distinct()
      ->get();

    // Log::info('Merkezler:', ['merkezler' => $merkezler]);

    $siparisler = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_ISEMIRLERI_DETAY')
      ->select('siparis_belge_no')
      ->when($request->coID, function ($query, $coID) {
        return $query->where('firma_id', $coID);
      })
      ->orderBy('siparis_belge_no')
      ->where('firma_id', $request->coID)
      ->distinct()
      ->get();

    $cariler = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_ISEMIRLERI_DETAY')
      ->select('cari_ad')
      ->when($request->coID, function ($query, $coID) {
        return $query->where('firma_id', $coID);
      })
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
    // $istasyonArray = explode(',', $request->ismerkezi);

    // Log::info($istasyonArray);

    if (empty($request->ismerkezi)) {
      return response()->json([
        'istasyonlar' => [],
        'message' => 'İstasyonlar için geçerli bir iş merkezi ID sağlanmadı',
        'success' => false,
      ], 400);
    }

    $istasyonlar = DB::connection('pgsql')
      ->table('uyumsoft.zz_bk_OFTV_IS_ISTASYONLARI')
      ->select(
        'istasyon_id',
        'istasyon_kodu',
        DB::raw("concat_ws('-', istasyon_kodu, istasyon_adi) as ist_adi") // 1200 ve istasyon_adi birleştirildi
      )
      ->where('is_merkezi_id', $request->ismerkezi)
      ->orderBy('istasyon_kodu')
      ->distinct()
      ->get();

    return response()->json([
      'istasyonlar' => $istasyonlar,
      'message' => 'Veriler başarıyla alındı',
      'success' => true,
    ]);
  }

  public function getIsEmriNoAl(Request $request)
  {

    // Log::info($request->all());

    $emirnolari = DB::connection('pgsql')
      ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
      ->select('isemri_id')
      ->whereDate('planlanan_bitis_tarihi', '>=', $request->filterValue)
      ->whereDate('planlanan_bitis_tarihi', '<=', $request->filterValue1)
      ->where('IS_MERKEZI_ID', $request->ismerkezi)
      ->where('IS_ISTASYONU_ID', $request->istasyon)
      ->orderBy('isemri_id')
      ->distinct()
      ->get();

      Log::info('Emir Noları:', ['emirnolari' => $emirnolari]);
    return response()->json([
      'emirnolari' => $emirnolari,
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
