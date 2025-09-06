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
    // Log::info('Fetching works info', ['request' => $request->all()]);
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

  public function isEmirleri(Request $request)
  {
    $istasyon = $request->query('istasyon', '1511');
    $rows = DB::connection('pgsql')
      ->table(DB::raw('uyumsoft."OFTV_ISEMIRLERI_DETAY" as oid'))
      ->select([
        'oid.isemri_id',
        'oid.isemri_no',
        'oid.stok_kodu',
        'oid.stok_adi',
        'oid.siparis_miktari',
        'oid.kalan_miktar',
        'oid.uretilen_net_miktar',
        'oid.toplam_hurda_miktari',
        'oid.operasyon_suresi',
      ])
      ->where('IS_ISTASYONU_KODU', '=', $istasyon)
      ->get();

    return response()->json($rows);
  }

  public function activateWorkorder(Request $request)
  {
    $validated = $request->validate([
      'wstation_id' => 'required',
      'worder_id' => 'required',
      'worder_no' => 'required',
      'item_id' => 'required',
      'item_code' => 'required',
      'item_name' => 'required',
      'item_length' => 'required|numeric',
      'order_qty' => 'required|numeric',
      'net_qty' => 'nullable|numeric',
      'scrap_qty' => 'nullable|numeric',
    ]);

    $payload = [
      'worder_id' => $validated['worder_id'],
      'worder_no' => $validated['worder_no'],
      'item_id' => $validated['item_id'],
      'item_code' => $validated['item_code'],
      'item_name' => $validated['item_name'],
      'item_length' => $validated['item_length'],
      'order_qty' => $validated['order_qty'],
      'net_qty' => $validated['net_qty'] ?? 0,
      'scrap_qty' => $validated['scrap_qty'] ?? 0,
      'counter' => 0,
    ];

    DB::connection('pgsql_oft')
      ->table('oftt_works_info')
      ->where('wstation_id', $validated['wstation_id'])
      ->update($payload);

    return response()->json(['message' => 'Aktif iş emri güncellendi']);
  }


  public function DurusKaydet(Request $request)
  {
    // Log::info($request->all());

    $islem = DB::connection('pgsql_oft')
      ->table('oftt_works_info')
      ->where('wstation_id', $request->istasyonID)
      ->update([
        'break_description' => $request->selectedDurus['description'] ?? 'GİRİLMEDİ',
      ]);

    $kayit = DB::connection('pgsql_oft')
      ->table('machine_events')
      ->where('wstation_id', $request->istasyonID)
      ->where('state', 'DOWN')
      ->orderBy('id', 'desc')
      ->first();

    if ($kayit) {
      DB::connection('pgsql_oft')
        ->table('machine_events')
        ->where('id', $kayit->id)
        ->update([
          'break_description' => $request->selectedDurus['description'] ?? 'GİRİLMEDİ',
          'break_reason_code' => $request->selectedDurus['break_reason_code'] ?? '0000',
        ]);
    }
    return response()->json(['message' => 'Güncelleme başarılı'], 200);
  }
}
