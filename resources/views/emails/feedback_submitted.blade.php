@php($v = $veriler)
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Geri Bildirim</title>
  <style>
    body{font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#111827;}
    .card{max-width:680px;margin:24px auto;padding:20px;border:1px solid #e5e7eb;border-radius:10px;background:#fff}
    .logo{display:flex;align-items:center;gap:.5rem;margin-bottom:12px}
    .logo img{height:56px}
    .muted{color:#6b7280}
    .btn{display:inline-block;padding:10px 14px;border-radius:8px;text-decoration:none}
    .btn-primary{background:#2563eb;color:white}
    .btn-danger{background:#ef4444;color:white}
    .kv{margin:4px 0}
    img{max-width:100%;height:auto;border:1px solid #eee;border-radius:8px}
    .meta{margin-top:12px;font-size:12px;color:#6b7280}
  </style>
</head>
<body>
  <div class="card">
  <div class="logo">
    <img src="{{ asset('images/svg/OFT-Logo.png') }}" alt="Logo" />
    <strong>OFT</strong>
  </div>
  <h2>{{ __('emails.feedback.title') }}</h2>
    <p class="muted">#{{ $v['bildirim_id'] }} • {{ $v['hata_sebebi'] ?? 'Geri Bildirim' }}</p>

    <div class="kv"><strong>Puan:</strong> {{ $v['rating'] ?? '-' }}</div>
    <div class="kv"><strong>E‑posta:</strong> {{ $v['email'] ?? '-' }}</div>
    <div class="kv"><strong>Sayfa:</strong> <a href="{{ $v['page_url'] ?? '#' }}" target="_blank">{{ $v['page_url'] ?? '-' }}</a></div>

    <h3>Mesaj</h3>
    <p>{{ $v['aciklama'] }}</p>

    @if(!empty($v['screenshot_url']))
      <h3>Ekran Görüntüsü</h3>
      <a href="{{ $v['screenshot_url'] }}" target="_blank">
        <img src="{{ $v['screenshot_url'] }}" alt="Ekran Görüntüsü" />
      </a>
    @endif

    @php($onay = isset($onayLink) ? $onayLink : (\Illuminate\Support\Facades\Route::has('hata.onayla') ? \Illuminate\Support\Facades\URL::signedRoute('hata.onayla', ['id' => $v['bildirim_id']]) : null))
  @php($ret = isset($retLink) ? $retLink : (\Illuminate\Support\Facades\Route::has('hata.ret') ? \Illuminate\Support\Facades\URL::signedRoute('hata.ret', ['id' => $v['bildirim_id']]) : null))
    @if($onay || $ret)
    <p class="meta">Hızlı işlem:
      @if($onay)
        <a class="btn btn-primary" href="{{ $onay }}">Değerlendir</a>
      @endif
      @if($ret)
        <a class="btn btn-danger" href="{{ $ret }}">Reddet</a>
      @endif
    </p>
    @endif
  </div>
</body>
</html>
