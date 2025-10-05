<?php

namespace App\Http\Controllers\satis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SatisController extends Controller
{
    public function getMusteriSiparisleri(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 dakika
        // Log::info($request->all());
        $kapsam = $request->kapsam;

        $query = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_MUSTERI_SIPARISLERI')
            ->orderBy('BELGE_TARIHI', 'desc');

        if ($kapsam === 'filtre') {
            $query->whereBetween('BELGE_TARIHI', [$request->filterValue, $request->filterValue1]);
            if ($request->firma) $query->where('FIRMA', $request->firma);
        }

        if ($kapsam === 'aciklar') {
            $query->where('ACIK_KAPALI', 'Açık');
        }

        if ($kapsam === 'kapalilar') {
            $query->where('ACIK_KAPALI', 'Kapalı');
        }

        $siparisler = $query->limit(30000)->get();

        return response()->json([
            'siparisler' => $siparisler,
            'message' => 'Veriler başarıyla alındı',
            'success' => true,
        ]);
    }

    public function getCiro(Request $request)
    {
        // Log::info($request->all());
        ini_set('memory_limit', '1024M');

        $query = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_CIROLAR')
            ->whereBetween('fatura_tarih', [$request->filterValue, $request->filterValue1]);
            // ->where('einv_doc_type', 2);

        if ($request->acik_kapali) $query->where('ACIK_KAPALI', $request->acik_kapali);

        $ciro = $query->get();

        return response()->json([
            'ciro' => $ciro,
            'message' => 'Veriler başarıyla alındı',
            'success' => true,
        ]);
    }

    public function TeslimTarihiDegistirMusteri(Request $request)
    {

        // Log::info($request->all());

        try {
            // Gelen parametreleri al
            $updateData = $request->input('updateData');

            // Log::info('Teslim Tarihi Değiştir', [
            //     'updateData' => $updateData,
            //     'userID' => $request->userID,
            //     'ip' => $request->ip()
            // ]);
            return;
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

            DB::commit();

            return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
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
}
