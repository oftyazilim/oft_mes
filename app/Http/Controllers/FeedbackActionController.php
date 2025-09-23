<?php

namespace App\Http\Controllers;

use App\Mail\FeedbackDecisionMail;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class FeedbackActionController extends Controller
{
    public function showOnay(Request $request, int $id)
    {
        $fb = Feedback::findOrFail($id);
        $postUrl = URL::signedRoute('hata.islem', ['id' => $fb->id]);
        $preset = 'in_progress';
        return view('feedback.review', compact('fb', 'postUrl', 'preset'));
    }

    public function showRet(Request $request, int $id)
    {
        $fb = Feedback::findOrFail($id);
        $postUrl = URL::signedRoute('hata.islem', ['id' => $fb->id]);
        $preset = 'invalid';
        return view('feedback.review', compact('fb', 'postUrl', 'preset'));
    }

    public function submit(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => 'required|in:new,in_progress,closed,invalid',
            'reason' => 'nullable|string|min:5|max:2000',
        ]);

        $fb = Feedback::findOrFail($id);
        $fb->status = $data['status'];
        $fb->save();

        // Kararı gönderene ilet (eğer e-posta mevcutsa)
        if (!empty($fb->email)) {
            try {
                Mail::to($fb->email)->send(new FeedbackDecisionMail($fb, $data['status'], (string) ($data['reason'] ?? '')));
            } catch (\Throwable $e) {
                // sessiz geç, form sonucunu yine de göster
            }
        }

        return view('feedback.review_done', [
            'fb' => $fb,
            'status' => $data['status'],
        ]);
    }
}
