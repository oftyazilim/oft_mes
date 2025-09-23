<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;

class HataBildirimiMail extends Mailable
{
    public $veriler;
    public $onayLink;
    public $retLink;

    public function __construct($veriler)
    {
        $this->veriler = $veriler;
        // İlgili named route'lar tanımlı değilse exception fırlamasın
        try {
            $this->onayLink = Route::has('hata.onayla')
                ? URL::signedRoute('hata.onayla', ['id' => $veriler['bildirim_id']])
                : null;
        } catch (\Throwable $e) {
            $this->onayLink = null;
        }
        try {
            $this->retLink = Route::has('hata.ret')
                ? URL::signedRoute('hata.ret', ['id' => $veriler['bildirim_id']])
                : null;
        } catch (\Throwable $e) {
            $this->retLink = null;
        }
    }

    public function build()
    {
        $subject = Lang::get('emails.feedback.subject', ['category' => ($this->veriler['hata_sebebi'] ?? 'Geri Bildirim')]);
        $mail = $this->subject($subject)
            ->view('emails.feedback_submitted');

        // Ekran görüntüsü dosya olarak iliştir (varsa)
        if (!empty($this->veriler['screenshot_path'])) {
            try {
                $path = 'public/' . ltrim($this->veriler['screenshot_path'], '/');
                if (Storage::exists($path)) {
                    $mail->attach(Storage::path($path), [
                        'as' => 'feedback-screenshot.png',
                        'mime' => 'image/png',
                    ]);
                }
            } catch (\Throwable $e) {
                // Ek iliştirme hatasını sessizce geç
            }
        }

        return $mail;
    }
}
