@php($fb = $feedback)
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Geri Bildiriminiz Hakkında</title>
  <style>
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#111827}
    .card{max-width:680px;margin:24px auto;padding:20px;border:1px solid #e5e7eb;border-radius:10px;background:#fff}
    .logo{display:flex;align-items:center;gap:.5rem;margin-bottom:12px}
    .logo img{height:56px}
    .muted{color:#6b7280}
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">
      <img src="{{ asset('images/svg/OFT-Logo.png') }}" alt="Logo" />
      <strong>OFT</strong>
    </div>
    <h2>Geri Bildiriminiz Hakkında</h2>
    <p class="muted">#{{ $fb->id }} • Kategori: {{ $fb->category ?? '-' }}</p>
    <p>Durum: <strong>{{ $status }}</strong></p>
    @if(!empty($reason))
      <p>Gerekçe/Not: {{ $reason }}</p>
    @endif
    <p>Mesajınız:</p>
    <pre style="white-space:pre-wrap">{{ $fb->message }}</pre>
  </div>
</body>
</html>
