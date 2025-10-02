<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UrunAgaciController extends Controller
{
    /**
     * GET /api/urun-agaci-sorgula
     * Query params:
     * - work_order_no (öncelik)
     * - stock_code
     * - stock_name
     */
    public function index(Request $request)
    {
        try {
            $workOrderNo = trim((string) $request->query('work_order_no', ''));
            $stockCode   = trim((string) $request->query('stock_code', ''));
            $stockName   = trim((string) $request->query('stock_name', ''));

            // 1) Önce bom_m_id’yi tespit et
            $bomId = null;

            if ($workOrderNo !== '') {
                $bomId = DB::connection('pgsql')
                    ->table('uyumsoft.prdt_worder_m as pwm')
                    ->where('pwm.worder_no', $workOrderNo)
                    ->value('pwm.bom_m_id');
            } elseif ($stockCode !== '') {
                $query = DB::connection('pgsql')
                    ->table('uyumsoft.prdd_bom_m as pbm')
                    ->join('uyumsoft.invd_item as ii', 'ii.item_id', '=', 'pbm.item_id')
                    ->leftJoin('uyumsoft.prdt_bom_exploded as pbe', 'pbe.exploded_bom_m_id', '=', 'pbm.bom_m_id')
                    ->where('ii.ispassive', 0)
                    ->where('pbm.is_default', 1)
                    ->where('ii.item_code', $stockCode);

                $bomId = $query->value('pbm.bom_m_id');
            } elseif ($stockName !== '') {
                $query = DB::connection('pgsql')
                    ->table('uyumsoft.prdd_bom_m as pbm')
                    ->join('uyumsoft.invd_item as ii', 'ii.item_id', '=', 'pbm.item_id')
                    ->leftJoin('uyumsoft.prdt_bom_exploded as pbe', 'pbe.exploded_bom_m_id', '=', 'pbm.bom_m_id')
                    ->where('ii.ispassive', 0)
                    ->where('pbm.is_default', 1)
                    ->where('ii.item_name', '=', $stockName );
                    // ->where('ii.item_name', 'like', '%' . $stockName . '%');

                $bomId = $query->value('pbm.bom_m_id');
            }

            if (!$bomId) {
                return response()->json([]);
            }

            // 2) Ürün ağacı satırlarını getir
            $rows = DB::connection('pgsql')
                ->table('uyumsoft.prdt_bom_exploded as pom')
                ->leftJoin('uyumsoft.invd_item as ii', 'ii.item_id', '=', 'pom.item_id')
                ->select([
                    'pom.exploded_level',
                    'pom.parent_exploded_id',
                    'pom.exploded_id',
                    'pom.line_no',
                    'pom.bom_line_type',
                    'ii.item_id',
                    'ii.item_code',
                    'ii.item_name',
                    'pom.qty_prm_exploded',
                    'pom.operation_id',
                    'pom.operation_no',
                ])
                ->where('pom.main_bom_m_id', $bomId)
                ->orderBy('pom.exploded_level')
                ->orderBy('pom.exploded_id')
                ->orderBy('pom.line_no')
                ->get();

            return response()->json($rows);
        } catch (\Throwable $th) {
            Log::error('UrunAgaciController@index hata', [
                'work_order_no' => $request->query('work_order_no'),
                'stock_code' => $request->query('stock_code'),
                'stock_name' => $request->query('stock_name'),
                'error' => $th->getMessage(),
                'code' => $th->getCode(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);
            return response()->json([
                'message' => 'Internal server error',
                'code' => 'URUN_AGACI_DB_ERROR'
            ], 500);
        }
    }
}
