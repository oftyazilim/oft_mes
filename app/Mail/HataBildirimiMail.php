<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;

class HataBildirimiMail extends Mailable
{
    public $veriler;
    public $onayLink;
    public $retLink;

    public function __construct($veriler)
    {
        $this->veriler = $veriler;
        $this->onayLink = URL::signedRoute('hata.onayla', ['id' => $veriler['bildirim_id']]);
        $this->retLink = URL::signedRoute('hata.ret', ['id' => $veriler['bildirim_id']]);
    }

    public function build()
    {
        return $this
            ->subject('[HATA] ' . $this->veriler['hata_sebebi'])
            ->view('emails.hata_bildirimi');
    }
}
