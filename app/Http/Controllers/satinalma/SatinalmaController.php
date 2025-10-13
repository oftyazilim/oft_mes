<?php

namespace App\Http\Controllers\satinalma;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SatinalmaController extends Controller
{
    public function getSatinalmaSiparisleri(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 dakika

        $kapsam = $request->kapsam;

        $query = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_SATINALMA_SIPARISLERI')
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

        $satinalma = $query->limit(30000)->get();

        return response()->json([
            'satinalma' => $satinalma,
            'message' => 'Veriler başarıyla alındı',
            'success' => true,
        ]);
    }

    public function getSatinalmaTalepleri(Request $request)
    {
        // Log::info($request->all());

        $query = DB::connection('pgsql')
            ->table('uyumsoft.zz_bk_tumtalepler')
            ->orderBy('talep_tarihi', 'desc');

        if ($request->tarihAralik === 'true') $query->whereBetween('talep_tarihi', [$request->filterValue, $request->filterValue1]);
        if ($request->firma) $query->where('FIRMA', $request->firma);
        $talepler = $query->get();

        return response()->json([
            'talepler' => $talepler,
            'message' => 'Veriler başarıyla alındı',
            'success' => true,
        ]);
    }

    public function getFirmalar(Request $request)
    {
        $firmalar = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_FIRMALAR')
            ->select('FIRMA')
            ->get();

        return response()->json([
            'firmalar' => $firmalar,
            'message' => 'Veriler başarıyla alındı',
            'success' => true,
        ]);
    }

    public function NotKaydet(Request $request)
    {
        $sayfa = '';
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

                $sayfa = $data['sayfa'];

                $updateFields = [
                    'note1' => $data['not1'],
                    'note2' => $data['not2'],
                    'note3' => $data['not3'],
                ];

                // Log::info($data);
                if ($data['notGuncelle'] === true) {

                    DB::connection('pgsql')->table('uyumsoft.psmt_order_d')
                        ->where('order_m_id', $data['order_m_id'])
                        ->update($updateFields);
                } else {
                    DB::connection('pgsql')->table('uyumsoft.psmt_order_d')
                        ->where('order_d_id', $data['order_d_id'])
                        ->update($updateFields);
                }
            }

            DB::commit();

            return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
        }
    }

    public function TeslimTarihiDegistir(Request $request)
    {

        $sayfa = '';

        try {
            // Gelen parametreleri al
            $updateData = $request->input('updateData');

            if (empty($updateData) || !is_array($updateData)) {
                return response()->json(['error' => 'Geçersiz veri!'], 400);
            }

            // İşlemleri başlat
            DB::beginTransaction();

            foreach ($updateData as $data) {

                $sayfa = $data['sayfa'];

                if (isset($data['order_d_id'])) {
                    $updateFields = [
                        'delivery_date' => $data['teslimTarih'],
                        'form_contract_note' => $data['tarihNotu']
                    ];

                    // Log::info($data);

                    DB::connection('pgsql')->table('uyumsoft.psmt_order_d')
                        ->where('order_d_id', $data['order_d_id'])
                        ->update($updateFields);

                    $updateFieldsM = [
                        'delivery_date' => $data['teslimTarih']
                    ];
                    DB::connection('pgsql')->table('uyumsoft.psmt_order_m')
                        ->where('order_m_id', $data['order_m_id'])
                        ->update($updateFieldsM);
                } else {
                    Log::error('Kayıt bulunamadı!', $data);
                }
            }

            DB::commit();

            return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
        }
    }


}
