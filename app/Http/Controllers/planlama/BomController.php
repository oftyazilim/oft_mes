<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    public function exploded(Request $request)
    {
        $bomId = $request->query('bom_m_id');

        if ($bomId === null || !is_numeric($bomId)) {
            return response()->json(['message' => 'bom_m_id zorunludur ve sayısal olmalıdır.'], 422);
        }

        $bomId = (int) $bomId;

        $rows = DB::connection('pgsql')
            ->table('uyumsoft.prdt_bom_exploded as pom')
            ->leftJoin('uyumsoft.invd_item as ii', 'ii.item_id', '=', 'pom.item_id')
            ->where('pom.main_bom_m_id', $bomId)
            ->orderBy('pom.exploded_level')
            ->orderBy('pom.exploded_id')
            ->orderBy('pom.line_no')
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
            ->get();

        return response()->json($rows, 200);
    }
}
