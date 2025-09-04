<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;
use Illuminate\Auth\Events\Validated;
use Carbon\Carbon;

class UretimRollFormController extends Controller
{

  public function getWorksInfo(Request $request)
  {
    $stationId = $request->query('station_id');
    $query = DB::connection('pgsql_oft')
      ->table('oftv_works_info')
      ->select(
        'wstation_id',
        'statu_id',
        'speed',
        'counter',
        'speed_max',
        'speed_target',
        'counter',
        'worder_id',
        'worder_no',
        'item_id',
        'item_code',
        'item_name',
        'item_length',
        'order_qty',
        'net_qty',
        'scrap_qty',
        'wstation_code',
        'wstation_name',
        'statu_time',
      );

    if (!empty($stationId)) {
      $query->where('wstation_id', $stationId);
    }

    $rows = $query->orderBy('wstation_id')->get();

    return response()->json($rows);
  }
}
