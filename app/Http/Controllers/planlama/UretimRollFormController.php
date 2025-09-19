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

  /**
   * Mevcut zamana göre (.env SHIFT_SLICES) aktif vardiyayı bulur
   * Örn: SHIFT_SLICES = "23:30-08:00, 08:00-18:00, 18:00-23:30"
   * Geri dönüş: [ 'id' => 'N', 'start' => Carbon, 'end' => Carbon ]
   */
  protected function resolveCurrentShift(): array
  {
    $tz = config('app.timezone', 'Europe/Istanbul');
    $now = Carbon::now($tz);
    $sliceStr = env('SHIFT_SLICES', '08:00-18:00, 18:00-23:30, 23:30-08:00');
    $raw = array_values(array_filter(array_map('trim', explode(',', $sliceStr))));
    // Varsayılan etiketler: G (Gündüz), A (Akşam), N (Gece) – uzunluk eşleşmezse indeks kullan.
    $labels = ['G', 'A', 'N'];
    foreach ($raw as $i => $span) {
      if (!str_contains($span, '-')) continue;
      [$s, $e] = array_map('trim', explode('-', $span));
      if (!$s || !$e) continue;
      // Start ve end için baz tarih
      $start = Carbon::createFromTimeString($s, $tz)->setDate($now->year, $now->month, $now->day);
      $endSameDay = Carbon::createFromTimeString($e, $tz)->setDate($now->year, $now->month, $now->day);
      $overnight = $s > $e; // gece devri
      if ($overnight) {
        // Örn 23:30-08:00 -> eğer now 00:30 ise start bir önceki gün.
        if ($now->format('H:i') < $e) {
          $start->subDay();
        }
        $end = (clone $start)->addDay()->setTimeFromTimeString($e);
      } else {
        $end = $endSameDay;
      }
      $inSlice = $now->betweenIncluded($start, $end);
      if ($inSlice) {
        $id = $labels[$i] ?? (string)$i;
        return [
          'id' => $id,
          'start' => $start,
          'end' => $end,
        ];
      }
    }
    // Hiçbiri bulunamazsa ilkini varsayalım
    if (count($raw)) {
      [$s, $e] = array_map('trim', explode('-', $raw[0]));
      $start = Carbon::createFromTimeString($s, $tz)->setDate($now->year, $now->month, $now->day);
      $end = Carbon::createFromTimeString($e, $tz)->setDate($now->year, $now->month, $now->day);
      if ($s > $e) {
        $end->addDay();
      }
      return ['id' => $labels[0] ?? '0', 'start' => $start, 'end' => $end];
    }
    // Tamamen fallback
    return ['id' => 'NA', 'start' => $now->copy()->startOfHour(), 'end' => $now->copy()->endOfHour()];
  }

  /**
   * KPI (availability, performance, quality, oee) değerlerini direkt machine_events & works_info üzerinden üretir.
   * İstek: station_id (query param) zorunlu.
   * Çıktı yüzdelik değil 0-1 arası oran döner; frontend çarpar.
   */
  public function kpi(Request $request)
  {
    $stationId = $request->query('station_id');
    if (!$stationId) {
      return response()->json(['error' => 'station_id gerekli'], 422);
    }

    $shift = $this->resolveCurrentShift();
    $start = $shift['start'];
    $end = $shift['end'];

    $good = 0;
    $scrap = 0;

    try {
      // Durum (UP/DOWN) süreleri – created_at / updated_at kullandığımız varsayım.
      $rows = DB::connection('pgsql_oft')
        ->table('machine_events')
        ->selectRaw("state, SUM(good_count_inc) as good, SUM(scrap_count_inc) as scrap, SUM(EXTRACT(EPOCH FROM (LEAST(COALESCE(end_ts, now()), ?) - GREATEST(event_ts, ?)))) AS seconds", [$end, $start])
        ->where('wstation_id', $stationId)
        ->where('event_ts', '<', $end) // overlap koşulları
        ->whereRaw('COALESCE(end_ts, now()) > ?', [$start])
        ->groupBy('state')
        ->get();

      $secondsByState = [];
      foreach ($rows as $r) {
        $secondsByState[$r->state] = (float)$r->seconds;
        $good += (float)$r->good ?? 0;
        $scrap += (float)$r->scrap ?? 0;
      }

      // Çalışma süreleri: UP / RUN / CALISMA etiketlerinden biri varsa çalışma kabul.
      $upSeconds = 0;
      foreach (['UP', 'RUN', 'CALISMA', 'WORK', 'RUNNING'] as $tag) {
        $upSeconds += $secondsByState[$tag] ?? 0;
      }
      // DOWN + diğerleri -> duruş.
      $downSeconds = 0;
      foreach ($secondsByState as $state => $secs) {
        if (in_array($state, ['DOWN', 'DUR', 'DURUŞ'])) {
          $downSeconds += $secs;
        }
      }
      $totalTracked = $upSeconds + $downSeconds;
      $availability = $totalTracked > 0 ? ($upSeconds / $totalTracked) : 0.0;

      // Log::info('KPI zaman hesaplama', [
      //   'station_id' => $stationId,
      //   'shift' => $shift,
      //   'upSeconds' => $upSeconds,
      //   'downSeconds' => $downSeconds,
      //   'totalTracked' => $totalTracked,
      //   'availability' => $availability,
      // ]);

      // Üretim bilgileri
      $w = DB::connection('pgsql_oft')
        ->table('oftt_works_info')
        ->select('speed_target', 'scrap_qty', 'item_length')
        ->where('wstation_id', $stationId)
        ->first();

      // oftt_works_info kaydı bulunamayabilir -> null koruması
      $speedTarget = $w ? (float)($w->speed_target ?? 0) : 0.0;
      $actualUnits = $good + $scrap; // mevcut kullanım şekline uyum
      // item_length (mm varsayımı) -> metre
      $itemLength = ($w && $w->item_length) ? (float)$w->item_length / 1000.0 : 0.0;
      $runtimeHours = $upSeconds / 3600.0;
      $theoretical = ($itemLength > 0 && $speedTarget > 0 && $runtimeHours > 0)
        ? ($speedTarget / $itemLength * 60 * $runtimeHours)
        : 0.0;
      $performance = $theoretical > 0 ? ($actualUnits / $theoretical) : 0.0;
      if ($performance < 0) $performance = 0; // güvenlik

      // Log::info('KPI hesaplama', [
      //   'station_id' => $stationId,
      //   'shift' => $shift,
      //   'upSeconds' => $upSeconds,
      //   'downSeconds' => $downSeconds,
      //   'speedTarget' => $speedTarget,
      //   'itemLength' => $itemLength,
      //   'actualUnits' => $actualUnits,
      //   'goodUnits' => $good,
      //   'scrapUnits' => $scrap,
      //   'runtimeHours' => $runtimeHours,
      //   'theoretical' => $theoretical,
      //   'availability' => $availability,
      //   'performance' => $performance,
      // ]);
      $denQuality = max($good + $scrap, 0.000001);
      $quality = $denQuality > 0 ? ($good / $denQuality) : 0.0;

      // OEE
      $oee = $availability * $performance * $quality;

      return response()->json([
        'shift' => [
          'id' => $shift['id'],
          'start' => $start->toDateTimeString(),
          'end' => $end->toDateTimeString(),
        ],
        'kpi' => [
          'availability' => $availability,
          'performance' => $performance,
          'quality' => $quality,
          'oee' => $oee,
        ],
        'raw' => [
          'upSeconds' => $upSeconds,
          'downSeconds' => $downSeconds,
          'speedTarget' => $speedTarget,
          'actualUnits' => $actualUnits,
          'goodUnits' => $good,
          'scrapUnits' => $scrap,
          'runtimeHours' => $runtimeHours,
        ],
      ]);
    } catch (\Throwable $e) {
      Log::error('KPI hesaplama hatası', [
        'err' => $e->getMessage(),
        'station_id' => $stationId ?? null,
        'trace' => $e->getFile() . ':' . $e->getLine(),
      ]);
      return response()->json(['error' => 'KPI hesaplanamadı'], 500);
    }
  }

  /**
   * Hurda giriş popup kaydı.
   * İstek: station_id, qty (pozitif sayı)
   * Etkiler: oftt_works_info.counter -= qty, scrap_qty += qty
   *          machine_events (aktif satır: end_ts IS NULL) good_count_inc -= qty, scrap_count_inc += qty
   */
  public function hurdaGir(Request $request)
  {
    $validated = $request->validate([
      'station_id' => 'required|numeric',
      'qty' => 'required|numeric|min:0.0001',
    ]);
    $sid = (int)$validated['station_id'];
    $qty = (float)$validated['qty'];
    try {
      DB::connection('pgsql_oft')->transaction(function () use ($sid, $qty) {
        // oftt_works_info: tek UPDATE ile counter ve scrap_qty güncelle
        DB::connection('pgsql_oft')
          ->table('oftt_works_info')
          ->where('wstation_id', $sid)
          ->update([
            // counter = GREATEST(counter - qty, 0)
            'counter' => DB::raw('GREATEST(counter - ' . ((float)$qty) . ', 0)'),
            'scrap_qty' => DB::raw('scrap_qty + ' . ((float)$qty)),
          ]);

        // machine_events: aktif (end_ts IS NULL) en güncel kaydı tek UPDATE ile güncelle
        // good_count_inc = GREATEST(good_count_inc - qty, 0), scrap_count_inc = scrap_count_inc + qty
        DB::connection('pgsql_oft')
          ->update(
            'UPDATE machine_events me
             SET good_count_inc = GREATEST(me.good_count_inc - ?, 0),
                 scrap_count_inc = me.scrap_count_inc + ?
             WHERE me.id = (
               SELECT id FROM machine_events
               WHERE wstation_id = ? AND end_ts IS NULL
               ORDER BY id DESC
               LIMIT 1
             )',
            [$qty, $qty, $sid]
          );
      });
      return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
      Log::error('Hurda giriş hatası', ['err' => $e->getMessage()]);
      return response()->json(['error' => 'Hurda kaydedilemedi'], 500);
    }
  }

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

  /**
   * İstemci IP adresine göre oftt_works_info tablosundan istasyon (wstation_id) tespiti.
   * Dönüş: { station_id: int|null, ip: string }
   */
  public function detectStation(Request $request)
  {
    try {
      // Reverse proxy/CDN arkasında doğru IP'yi almak için X-Forwarded-For tercih et
      $xff = $request->header('X-Forwarded-For');
      $ip = $xff ? trim(explode(',', $xff)[0]) : $request->ip();
      // IPv6-mapped IPv4 ("::ffff:192.168.1.10") formatını normalize et
      if (str_starts_with($ip, '::ffff:')) {
        $ip = substr($ip, 7);
      }
      // Log::info('Detect station request', ['ip' => $ip]);
      $row = DB::connection('pgsql_oft')
        ->table('oftt_works_info')
        ->select('wstation_id')
        ->where('ip_address', $ip)
        ->first();
      // Log::info('Detect station', ['ip' => $ip, 'station_id' => $row->wstation_id ?? null]);
      return response()->json([
        'station_id' => $row->wstation_id ?? null,
        'ip' => $ip,
      ]);
    } catch (\Throwable $e) {
      return response()->json([
        'station_id' => null,
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  public function isEmirleri(Request $request)
  {
    // İstasyon kodunu belirleme sırası:
    // 1) Query param 'istasyon'
    // 2) IP eşlemesi (config/stations.php ip_map)
    // 3) DEFAULT_STATION_CODE (.env)
    $istasyon = $request->query('istasyon');
    // if (!$istasyon) {
    //   $clientIp = $request->ip();
    //   $map = config('stations.ip_map', []);
    //   $istasyon = $map[$clientIp] ?? null;
    // }
    // Log::info('İstasyon kodu', ['istasyon' => $istasyon]);
    if (!$istasyon) {
      $istasyon = config('stations.default_code', '0000');
    }
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
      ->where('IS_ISTASYONU_ID', '=', $istasyon)
      ->get();
    // Log::info('İş emri detayları', ['rows' => $rows]);
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
