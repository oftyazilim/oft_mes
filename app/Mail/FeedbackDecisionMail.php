<?php

namespace App\Mail;

use App\Models\Feedback;
use Illuminate\Mail\Mailable;

class FeedbackDecisionMail extends Mailable
{
    public function __construct(
        public Feedback $feedback,
        public string $status,
        public string $reason = ''
    ) {}

    public function build()
    {
        $subject = match ($this->status) {
            'in_progress' => __('Geri Bildiriminiz Değerlendirilmeye Alındı'),
            'closed' => __('Geri Bildiriminiz Sonuçlandı'),
            'invalid' => __('Geri Bildiriminiz Uygunsuz Olarak İşaretlendi'),
            default => __('Geri Bildiriminiz Hakkında'),
        };
        return $this->subject($subject)
            ->view('emails.feedback_decision');
    }
}
