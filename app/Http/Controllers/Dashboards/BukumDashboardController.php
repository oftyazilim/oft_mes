<?php

namespace App\Http\Controllers\Dashboards;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BukumDashboardController extends BaseController
{
    // GET /api/dash-bukum/overview
    public function overview(Request $request)
    {
        // Varsayılan: mevcut vardiya diliminde özet. Parametre ile tarih aralığı da desteklenebilir.
        [$start, $end] = $this->resolveWindow($request);

        // Log::info('Büküm dashboard overview', ['start' => $start->toDateTimeString(), 'end' => $end->toDateTimeString()]);

        // 1) Büküm istasyonlarını topla: oftv_works_info üzerinden aktif-en güncel kayıtlar.
        // Not: Burada Büküm'e ait istasyonları filtrelemek için isim/kod kalıbı gerekiyorsa eklenebilir.
        // Büküm merkez id'sini env veya query param ile aşırı yazılabilir hale getir
        $centerId = (int)($request->query('center_id', env('BUKUM_WCENTER_ID', 1120)));
        $limit = (int)($request->query('limit', 6));
        $stations = DB::connection('pgsql_oft')
            ->table('oftv_works_info')
            ->select('wstation_id', 'wstation_code', 'wstation_name', 'statu_id', 'speed_target', 'item_length', 'net_qty', 'scrap_qty', 'name', 'item_code', 'item_name', 'counter')
            ->where('wcenter_id', $centerId)
            ->orderBy('wstation_code')
            ->limit($limit) // ekran 6 kart istiyordu; şimdilik üst limit parametreli
            ->get();

            // Log::info('Büküm istasyonları', ['stations' => $stations]);

        $machines = [];
        $sumAvailability = 0.0;
        $sumPerformance = 0.0;
        $sumQuality = 0.0;
        $sumOee = 0.0;
        $count = 0;

        $stationIds = $stations->pluck('wstation_id')->filter()->values();
        $eventsByStation = [];
        $timelineByStation = [];
        if ($stationIds->isNotEmpty()) {
            $evRows = DB::connection('pgsql_oft')
                ->table('machine_events')
                ->selectRaw('wstation_id, state, SUM(good_count_inc) AS good, SUM(scrap_count_inc) AS scrap, SUM(EXTRACT(EPOCH FROM (LEAST(COALESCE(end_ts, now()), ?) - GREATEST(event_ts, ?)))) AS seconds', [$end, $start])
                ->whereIn('wstation_id', $stationIds->all())
                ->where('event_ts', '<', $end)
                ->whereRaw('COALESCE(end_ts, now()) > ?', [$start])
                ->groupBy('wstation_id', 'state')
                ->get();
            foreach ($evRows as $er) {
                $wid = (int)$er->wstation_id;
                $eventsByStation[$wid][] = $er;
            }

            // Zaman sıralı segmentler (event granüler) – id/event_ts sırasına göre
            $tlRows = DB::connection('pgsql_oft')
                ->table('machine_events')
                ->select('id', 'wstation_id', 'state', 'break_reason_code', 'break_description', 'event_ts', 'end_ts')
                ->whereIn('wstation_id', $stationIds->all())
                ->where('event_ts', '<', $end)
                ->whereRaw('COALESCE(end_ts, now()) > ?', [$start])
                ->orderBy('wstation_id')
                ->orderBy('event_ts')
                ->orderBy('id')
                ->get();

            foreach ($tlRows as $tr) {
                $wid = (int)$tr->wstation_id;
                $evStart = Carbon::parse($tr->event_ts);
                $evEnd = $tr->end_ts ? Carbon::parse($tr->end_ts) : Carbon::now();
                // Pencereye göre kırp
                $segStart = $evStart->greaterThan($start) ? $evStart : $start;
                $segEnd = $evEnd->lessThan($end) ? $evEnd : $end;
                $secs = $segEnd->greaterThan($segStart) ? $segEnd->diffInSeconds($segStart) : 0;
                if ($secs <= 0) continue;
                // State -> type normalize
                $state = strtoupper((string)$tr->state);
                $type = 'kalan';
                if (in_array($state, ['UP', 'RUN', 'CALISMA', 'WORK', 'RUNNING'])) {
                    $type = 'calisma';
                } elseif (in_array($state, ['DOWN', 'DUR', 'DURUŞ', 'DURUS'])) {
                    $type = 'durus';
                }
                $timelineByStation[$wid][] = [
                    'type' => $type,
                    'duration' => $secs,
                    'reason' => $type === 'durus' ? ($tr->break_description ?? null) : null,
                    'code' => $type === 'durus' ? ($tr->break_reason_code ?? null) : null,
                ];
            }
        }

        foreach ($stations as $ws) {
            $sid = (int)$ws->wstation_id;
            $rows = $eventsByStation[$sid] ?? [];

            $secondsByState = [];
            $good = 0.0;
            $scrap = 0.0;
            foreach ($rows as $r) {
                $secondsByState[$r->state] = (float)$r->seconds;
                $good += (float)($r->good ?? 0);
                $scrap += (float)($r->scrap ?? 0);
            }

            $upSeconds = 0.0;
            foreach (['UP', 'RUN', 'CALISMA', 'WORK', 'RUNNING'] as $tag) {
                $upSeconds += $secondsByState[$tag] ?? 0.0;
            }
            $downSeconds = 0.0;
            foreach ($secondsByState as $state => $secs) {
                if (in_array($state, ['DOWN', 'DUR', 'DURUŞ'])) {
                    $downSeconds += $secs;
                }
            }
            $totalTracked = $upSeconds + $downSeconds;
            $availability = $totalTracked > 0 ? ($upSeconds / $totalTracked) : 0.0;

            // Performans: hedef hıza göre teorik üretim vs. fiili üretim (good+scrap)
            $speedTarget = (float)($ws->speed_target ?? 0.0);
            $itemLength = (float)((($ws->item_length ?? 0) / 1000)); // mm -> m varsayımı
            $runtimeHours = $upSeconds / 3600.0;
            $theoretical = ($itemLength > 0) ? (($speedTarget / $itemLength) * 60.0 * $runtimeHours) : 0.0;
            $actualUnits = max(0.0, $good + $scrap);
            $performance = $theoretical > 0 ? ($actualUnits / $theoretical) : 0.0;
            if ($performance < 0) $performance = 0;

            // Kalite: good / (good+scrap)
            $denQuality = max($good + $scrap, 0.000001);
            $quality = $denQuality > 0 ? ($good / $denQuality) : 0.0;

            // OEE
            $oee = $availability * $performance * $quality;

            // Status: statu_id -> running(2), stopped(1), offline(0/null)
            $status = 'offline';
            $st = $ws->statu_id;
            if ($st === 2 || $st === '2') $status = 'running';
            elseif ($st === 1 || $st === '1') $status = 'stopped';

            // Çalışma/Duruş oranları, toplam 100 olacak şekilde
            $workRatio = $totalTracked > 0 ? round($upSeconds / $totalTracked * 100) : 0;
            $stopRatio = max(0, 100 - $workRatio);

            $machines[] = [
                'id' => $sid,
                'code' => $ws->wstation_code ?: '',
                'name' => ($ws->wstation_name ?: ('Büküm ' . $sid)),
                'operator' => $ws->name ?: 'Atanmamış',
                'itemCode' => $ws->item_code ?? null,
                'itemName' => $ws->item_name ?? null,
                'counter' => (int)($ws->counter ?? 0),
                'status' => $status,
                'availability' => $availability,
                'performance' => $performance,
                'quality' => $quality,
                'oee' => $oee,
                'workRatio' => $workRatio,
                'stopRatio' => $stopRatio,
                'segments' => $timelineByStation[$sid] ?? [],
            ];

            $sumAvailability += $availability;
            $sumPerformance += $performance;
            $sumQuality += $quality;
            $sumOee += $oee;
            $count++;
        }

        if ($count === 0) {
            $daySummary = ['availability' => 0, 'performance' => 0, 'quality' => 1, 'oee' => 0];
        } else {
            $daySummary = [
                'availability' => $sumAvailability / $count,
                'performance' => $sumPerformance / $count,
                'quality' => $sumQuality / $count,
                'oee' => $sumOee / $count,
            ];
        }

        // 3) Duruş sebepleri – DOWN state eventlerinden break_reason_code/description süre toplamı
    $stationIds = collect($stations)->pluck('wstation_id')->filter()->values()->all();
        $reasons = [];
        if (!empty($stationIds)) {
            $reasonsRaw = DB::connection('pgsql_oft')
                ->table('machine_events')
                ->selectRaw("COALESCE(break_reason_code, '0000') AS code, COALESCE(break_description, 'Bilinmiyor') AS name, SUM(EXTRACT(EPOCH FROM (LEAST(COALESCE(end_ts, now()), ?) - GREATEST(event_ts, ?)))) AS seconds", [$end, $start])
                ->whereIn('wstation_id', $stationIds)
                ->where('state', 'DOWN')
                ->where('event_ts', '<', $end)
                ->whereRaw('COALESCE(end_ts, now()) > ?', [$start])
                ->groupBy('code', 'name')
                ->orderByDesc('seconds')
                ->limit(4)
                ->get();

            foreach ($reasonsRaw as $rr) {
                $reasons[] = [
                    'name' => $rr->name,
                    'minutes' => (int)round(((float)$rr->seconds) / 60.0),
                ];
            }
        }

    // 4) Haftalık (Pzt-Paz) günlük çalışma/duruş toplamları (her gün görünsün)
    $weekly = [];
    $tz = config('app.timezone', 'Europe/Istanbul');
    $weekStart = Carbon::now($tz)->startOfWeek(Carbon::MONDAY)->startOfDay();
        for ($i = 0; $i < 7; $i++) {
            $ds = (clone $weekStart)->addDays($i);
            $de = (clone $ds)->addDay();
            $workSec = 0.0;
            $downSec = 0.0;
            if (!empty($stationIds)) {
                $drows = DB::connection('pgsql_oft')
                    ->table('machine_events')
                    ->selectRaw('state, SUM(EXTRACT(EPOCH FROM (LEAST(COALESCE(end_ts, now()), ?) - GREATEST(event_ts, ?)))) AS seconds', [$de, $ds])
                    ->whereIn('wstation_id', $stationIds)
                    ->where('event_ts', '<', $de)
                    ->whereRaw('COALESCE(end_ts, now()) > ?', [$ds])
                    ->groupBy('state')
                    ->get();
                foreach ($drows as $dr) {
                    $sec = (float)($dr->seconds ?? 0);
                    if (in_array($dr->state, ['UP', 'RUN', 'CALISMA', 'WORK', 'RUNNING'])) {
                        $workSec += $sec;
                    } elseif (in_array($dr->state, ['DOWN', 'DUR', 'DURUŞ'])) {
                        $downSec += $sec;
                    }
                }
            }
            $labels = ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'];
            $weekly[] = [
                'date' => $ds->toDateString(),
                'label' => $labels[$i] ?? $ds->format('D'),
                'work' => (int)round($workSec / 60.0),
                'stop' => (int)round($downSec / 60.0),
            ];
        }

        // 5) Haftalık sayaç toplamları (good+scrap artışları) – tüm istasyonlar, gün bazlı
        $weeklyCounts = [];
        $weekEnd = (clone $weekStart)->addDays(7);
        $countsMap = [];
        if (!empty($stationIds)) {
            $countRows = DB::connection('pgsql_oft')
                ->table('machine_events')
                ->selectRaw("date_trunc('day', event_ts) AS day, SUM(COALESCE(good_count_inc,0) + COALESCE(scrap_count_inc,0)) AS cnt")
                ->whereIn('wstation_id', $stationIds)
                ->where('event_ts', '>=', $weekStart)
                ->where('event_ts', '<', $weekEnd)
                ->groupBy('day')
                ->orderBy('day')
                ->get();
            foreach ($countRows as $cr) {
                $d = Carbon::parse($cr->day, $tz)->toDateString();
                $countsMap[$d] = (int)($cr->cnt ?? 0);
            }
        }
        for ($i = 0; $i < 7; $i++) {
            $ds = (clone $weekStart)->addDays($i);
            $label = ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'][$i] ?? $ds->format('D');
            $key = $ds->toDateString();
            $weeklyCounts[] = [
                'date' => $key,
                'label' => $label,
                'count' => $countsMap[$key] ?? 0,
            ];
        }

        return response()->json([
            'machines' => $machines,
            'daySummary' => $daySummary,
            'reasons' => $reasons,
            'weekly' => $weekly,
            'weeklyCounts' => $weeklyCounts,
            'window' => [
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ],
            'meta' => [
                'center_id' => $centerId,
                'station_count' => count($machines),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * İstekten tarih aralığı çöz, yoksa vardiya dilimini bul.
     * Desteklenen query: start, end (YYYY-MM-DD HH:mm:ss) veya shift dilimi.
     */
    protected function resolveWindow(Request $request): array
    {
        $tz = config('app.timezone', 'Europe/Istanbul');
        $startStr = $request->query('start');
        $endStr = $request->query('end');
        if ($startStr && $endStr) {
            $start = Carbon::parse($startStr, $tz);
            $end = Carbon::parse($endStr, $tz);
            return [$start, $end];
        }
        // Vardiyadışıysa: gün başlangıcı - şimdi
        [$shiftStart, $shiftEnd] = $this->resolveCurrentShift();
        return [$shiftStart, $shiftEnd];
    }

    /**
     * Mevcut zamana göre (.env SHIFT_SLICES) aktif vardiya penceresini döner.
     * Örn: SHIFT_SLICES = "23:30-08:00, 08:00-18:00, 18:00-23:30"
     */
    protected function resolveCurrentShift(): array
    {
        $tz = config('app.timezone', 'Europe/Istanbul');
        $now = Carbon::now($tz);
        $sliceStr = env('SHIFT_SLICES', '08:00-18:00, 18:00-23:30, 23:30-08:00');
        $raw = array_values(array_filter(array_map('trim', explode(',', $sliceStr))));
        foreach ($raw as $span) {
            if (!str_contains($span, '-')) continue;
            [$s, $e] = array_map('trim', explode('-', $span));
            if (!$s || !$e) continue;
            $start = Carbon::createFromTimeString($s, $tz)->setDate($now->year, $now->month, $now->day);
            $endSameDay = Carbon::createFromTimeString($e, $tz)->setDate($now->year, $now->month, $now->day);
            $overnight = $s > $e;
            if ($overnight) {
                if ($now->format('H:i') < $e) {
                    $start->subDay();
                }
                $end = (clone $start)->addDay()->setTimeFromTimeString($e);
            } else {
                $end = $endSameDay;
            }
            if ($now->betweenIncluded($start, $end)) {
                return [$start, $end];
            }
        }
        // Fallback: son 8 saat
        return [$now->copy()->subHours(8), $now];
    }
}
