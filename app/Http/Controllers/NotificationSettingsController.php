<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function showFeedback()
    {
        $row = NotificationSetting::firstWhere('channel', 'feedback');
        return response()->json([
            'channel' => 'feedback',
            'recipients' => $row?->recipients ?? [],
        ]);
    }

    public function updateFeedback(Request $request)
    {
        $data = $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'email',
        ]);
        $row = NotificationSetting::firstOrNew(['channel' => 'feedback']);
        $row->recipients = array_values(array_unique($data['recipients']));
        $row->save();
        return response()->json(['ok' => true]);
    }
}
