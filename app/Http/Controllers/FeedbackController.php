<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\HataBildirimiMail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\NotificationSetting;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $q = Feedback::query();
        if ($s = $request->string('search')->toString()) {
            $q->where(function ($qq) use ($s) {
                $qq->where('message', 'ilike', "%$s%");
            });
        }
        if ($c = $request->string('category')->toString()) $q->where('category', $c);
        if ($st = $request->string('status')->toString()) $q->where('status', $st);
        if ($min = $request->integer('min_rating')) $q->where('rating', '>=', $min);
        if ($max = $request->integer('max_rating')) $q->where('rating', '<=', $max);

        $q->orderByDesc('id');
        return $q->paginate($request->integer('per_page', 20));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $q = Feedback::query()->orderByDesc('id');
        $filename = 'feedback-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $columns = ['id','created_at','email','category','rating','message','page_url','user_agent','status'];
        return response()->stream(function () use ($q, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            $q->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $r) {
                    $line = [];
                    foreach ($columns as $col) $line[] = data_get($r, $col);
                    fputcsv($out, $line);
                }
            });
            fclose($out);
        }, 200, $headers);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:oneri,hata,istek,diger|max:64',
            'rating' => 'nullable|integer|min:1|max:5',
            'message' => 'required|string|max:5000',
            'email' => 'nullable|email|max:255',
            'page_url' => 'nullable|string|max:512',
            'screenshot' => 'nullable|image|max:2048', // 2MB
        ]);

        $path = null;
        if ($request->hasFile('screenshot')) {
            $path = $request->file('screenshot')->store('feedback_screens', 'public');
        }

        $fb = Feedback::create([
            'user_id' => optional($request->user())->id,
            'email' => $validated['email'] ?? null,
            'category' => $validated['category'] ?? null,
            'rating' => $validated['rating'] ?? null,
            'message' => $validated['message'],
            'page_url' => $validated['page_url'] ?? $request->header('referer'),
            'user_agent' => $request->userAgent(),
            'screenshot_path' => $path,
            'status' => 'new',
        ]);

        // --- Bildirim Alıcıları (çok katmanlı fallback + teşhis logları) ---
        $notifyList = $this->resolveFeedbackRecipients();
        Log::info('Feedback recipients resolved', ['feedback_id' => $fb->id, 'count' => count($notifyList), 'list' => $notifyList]);
        if (empty($notifyList)) {
            Log::warning('Feedback mail SKIPPED: recipient list empty', ['feedback_id' => $fb->id]);
        } else {
            try {
                $screenshotUrl = $path ? asset('storage/' . ltrim($path, '/')) : null;
                $payload = [
                    'bildirim_id' => $fb->id,
                    'hata_sebebi' => $validated['category'] ?? 'Geri Bildirim',
                    'aciklama' => $validated['message'],
                    'email' => $validated['email'] ?? null,
                    'rating' => $validated['rating'] ?? null,
                    'page_url' => $validated['page_url'] ?? $request->header('referer'),
                    'screenshot_url' => $screenshotUrl,
                    'screenshot_path' => $path,
                ];
                Log::info('Feedback mail SEND attempt', ['feedback_id' => $fb->id, 'recipients' => $notifyList]);
                Mail::to($notifyList)->send(new HataBildirimiMail($payload));
                Log::info('Feedback mail SENT', ['feedback_id' => $fb->id]);
            } catch (\Throwable $e) {
                Log::warning('Feedback mail gönderilemedi', [
                    'feedback_id' => $fb->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['ok' => true, 'id' => $fb->id]);
    }

    /**
     * Çok katmanlı fallback ile feedback mail alıcılarını çözer.
     * Katmanlar sırası:
     * 1) notification_settings.channel=feedback (boş array ise yok sayılır)
     * 2) config('services.feedback.notify_to')
     * 3) config('mail.feedback_notify_to')
     * 4) env('FEEDBACK_NOTIFY')
     */
    protected function resolveFeedbackRecipients(): array
    {
        // 1) DB
        $db = optional(NotificationSetting::firstWhere('channel', 'feedback'))->recipients;
        $dbList = $this->normalizeRecipients($db);
        if (!empty($dbList)) return $dbList;

        // 2) services config
        $svc = $this->normalizeRecipients(config('services.feedback.notify_to'));
        if (!empty($svc)) return $svc;

        // 3) mail config
        $mailCfg = $this->normalizeRecipients(config('mail.feedback_notify_to'));
        if (!empty($mailCfg)) return $mailCfg;

        // 4) env fallback
        return $this->normalizeRecipients(env('FEEDBACK_NOTIFY'));
    }

    protected function normalizeRecipients($raw): array
    {
        if (empty($raw)) return [];
        if (is_string($raw)) {
            $raw = preg_split('/[,;\s]+/', $raw);
        }
        if (!is_array($raw)) return [];
        return collect($raw)
            ->filter(fn($e) => is_string($e) && trim($e) !== '')
            ->map(fn($e) => strtolower(trim($e)))
            ->filter(function ($e) {
                return filter_var($e, FILTER_VALIDATE_EMAIL);
            })
            ->unique()
            ->values()
            ->all();
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,closed,invalid',
        ]);
        $fb = Feedback::findOrFail($id);
        $fb->status = $validated['status'];
        $fb->save();
        return response()->json(['ok' => true]);
    }
}
