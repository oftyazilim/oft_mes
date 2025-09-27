<?php

// Optimized version of IhtiyacHesapla
// Notes: reduces DB queries by joining materials with work orders, aggregates depo sums in batch,
// uses associative maps instead of array_search and avoids per-item queries inside loops.

public function IhtiyacHesapla(Request $request)
{
    ini_set('max_execution_time', 1500);

    Log::info($request->all());

    try {
        // 1) preload small reference sets
        $anadepolar = DB::connection('pgsql')
            ->table('uyumsoft.invd_whouse')
            ->where('whouse_width', '1')
            ->pluck('whouse_id')
            ->toArray();

        $satinalmasiparisleri = DB::connection('pgsql')
            ->table('uyumsoft.zz_bk_tumsiparisler')
            ->select('STOK_HIZMET_ID', 'MIKTAR', 'KALAN_MIKTAR', 'satici_adi')
            ->where('purchase_sales', '1')
            ->where('order_status', '1')
            ->where('FIRMA', 'CANOVATE')
            ->get()
            ->groupBy('STOK_HIZMET_ID')
            ->map(function($g){
                return [
                    'kalan' => $g->sum('KALAN_MIKTAR'),
                    'satici' => $g->pluck('satici_adi')->first()
                ];
            })
            ->toArray();

        $talepler = DB::connection('pgsql')
            ->table('uyumsoft.zz_bk_tumtalepler')
            ->select('item_id', DB::raw('sum(miktar) as toplam'))
            ->where('ONAY_DURUMU', 'Onaylandi')
            ->groupBy('item_id')
            ->pluck('toplam', 'item_id')
            ->toArray();

        // 2) fetch joined rows (work order details + materials) in one query
        $query = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY as e')
            ->join('uyumsoft.OFTV_ISEMIRLERI_MALZEMELER as m', 'm.worder_m_id', '=', 'e.isemri_id')
            ->select(
                'e.isemri_id',
                'e.isemri_no',
                'e.stok_id',
                'e.stok_kodu',
                'e.stok_adi',
                'e.CIKIS_DEPO',
                'e.siparis_belge_no',
                'e.cari_ad',
                DB::raw("concat_ws('-', e.IS_MERKEZI_KODU, e.IS_MERKEZI_ADI) as mrk_adi"),
                DB::raw('e.isemri_miktari as miktar'),
                DB::raw('e.kalan_miktar as kalan'),
                'm.item_id',
                'm.qty_net',
                'm.qty_prm',
                'm.tipi',
                'm.stok_kodu as malz_stok_kodu',
                'm.stok_adi as malz_stok_adi',
                // hesap_miktari = (m.qty_net / e.isemri_miktari) * e.kalan_miktar
                DB::raw('(m.qty_net::numeric / NULLIF(e.isemri_miktari,0)::numeric) * e.kalan_miktar::numeric as hesap_miktari')
            )
            ->whereDate('e.planlanan_bitis_tarihi', '>=', $request->filterValue)
            ->whereDate('e.planlanan_bitis_tarihi', '<=', $request->filterValue1)
            ->when($request->merkezID, fn($q) => $q->where('e.IS_MERKEZI_ID', $request->merkezID))
            ->when($request->istasyonID, function($q) use ($request){
                $istasyonArray = explode(',', $request->istasyonID);
                return $q->whereIn('e.IS_ISTASYONU_ID', $istasyonArray);
            })
            ->when($request->siparis, fn($q) => $q->where('e.siparis_belge_no', $request->siparis))
            ->when($request->cari, fn($q) => $q->where('e.cari_ad', $request->cari))
            ->whereNot('e.OPERASYON', 'HAYALET')
            ->whereNot('e.OPERASYON_KODU', 'M12')
            ->where('e.Rotadaki_Son_Operasyon', 1);

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return response()->json(['message' => 'Veri bulunamadı', 'emirler' => [], 'dagilim' => []], 200);
        }

        // 3) build maps keyed by item_id to aggregate in-memory (single pass)
        $bulkMap = []; // item_id => aggregated data
        $dagilimMap = []; // composite key isemri_id|item_id => record

        $itemIds = [];
        foreach ($rows as $r) {
            $itemId = $r->item_id;
            $itemIds[$itemId] = $itemId;

            // fill dagilim (which orders use this item)
            $dagKey = $r->isemri_id . '|' . $itemId;
            if (!isset($dagilimMap[$dagKey])) {
                $dagilimMap[$dagKey] = [
                    'item_id' => $itemId,
                    'isemri_id' => $r->isemri_id,
                    'isemri_no' => $r->isemri_no,
                    'siparis_belge_no' => $r->siparis_belge_no,
                    'cari_ad' => $r->cari_ad,
                    'isemri_miktari' => (float) $r->miktar,
                    'kalan' => (float) $r->kalan,
                    'stok_id' => $r->stok_id,
                    'stok_kodu' => $r->stok_kodu,
                    'stok_adi' => $r->stok_adi,
                ];
            } else {
                $dagilimMap[$dagKey]['isemri_miktari'] += (float) $r->miktar;
            }

            // aggregate bulk map
            if (!isset($bulkMap[$itemId])) {
                $bulkMap[$itemId] = [
                    'item_id' => $itemId,
                    'tipi' => $r->tipi,
                    'stok_kodu' => $r->malz_stok_kodu ?? $r->stok_kodu,
                    'stok_adi' => $r->malz_stok_adi ?? $r->stok_adi,
                    'mrk_adi' => $r->mrk_adi,
                    'isemri_miktari' => (float) $r->miktar,
                    'kalan' => (float) $r->kalan,
                    'ihtiyac' => (float) $r->hesap_miktari,
                    'stok' => (float) $r->qty_prm,
                    'ana_depo' => 0,
                    'diger_depo' => 0,
                    'satinalma' => 0,
                    'satinalmapersoneli' => '',
                    'talepler' => 0,
                    'bakiye' => 0,
                    'dongu' => 1,
                    'depo_ihtiyaci' => 0,
                ];
            } else {
                $bulkMap[$itemId]['isemri_miktari'] += (float) $r->miktar;
                $bulkMap[$itemId]['kalan'] += (float) $r->kalan;
                $bulkMap[$itemId]['ihtiyac'] += (float) $r->hesap_miktari;
                $bulkMap[$itemId]['dongu'] += 1;
            }
        }

        $itemIds = array_values($itemIds);

        // 4) batch queries for depo sums for all involved items
        $anaDepoSums = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
            ->select('item_id', DB::raw('sum(qty_prm) as total'))
            ->whereIn('item_id', $itemIds)
            ->whereIn('whouse_id', $anadepolar)
            ->groupBy('item_id')
            ->pluck('total', 'item_id')
            ->toArray();

        $digerDepoSums = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_DIGER_DEPOLAR')
            ->select('item_id', DB::raw('sum(qty_prm) as total'))
            ->whereIn('item_id', $itemIds)
            ->whereNotIn('whouse_id', $anadepolar)
            ->groupBy('item_id')
            ->pluck('total', 'item_id')
            ->toArray();

        // 5) enrich bulkMap from preloaded maps
        foreach ($bulkMap as $iid => &$entry) {
            // ana depo
            if (isset($anaDepoSums[$iid])) {
                $entry['ana_depo'] = (float) $anaDepoSums[$iid];
            }
            // diger depo
            if (isset($digerDepoSums[$iid])) {
                $entry['diger_depo'] = (float) $digerDepoSums[$iid];
            }
            // satinalma (from preloaded collection)
            if (isset($satinalmasiparisleri[$iid])) {
                $entry['satinalma'] = (float) $satinalmasiparisleri[$iid]['kalan'];
                $entry['satinalmapersoneli'] = $satinalmasiparisleri[$iid]['satici'] ?? '';
            }
            // talepler
            if (isset($talepler[$iid])) {
                $entry['talepler'] = (float) $talepler[$iid];
            }
            // bakiye and depo_ihtiyaci
            $entry['bakiye'] = ($entry['ana_depo'] + $entry['stok']) - $entry['ihtiyac'];
            $need = $entry['ihtiyac'] - $entry['stok'];
            $entry['depo_ihtiyaci'] = $need > 0 ? $need : 0;
        }
        unset($entry);

        // 6) prepare response arrays
        $bulkUpdates = array_values($bulkMap);
        $hangiIsemirleri = array_values($dagilimMap);

        return response()->json([
            'message' => 'İhtiyaç hesaplama başarılı',
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
