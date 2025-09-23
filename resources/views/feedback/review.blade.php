@php($fb = $fb)
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Geri Bildirim İşlemi</title>
  <style>
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#111827}
    .container{max-width:720px;margin:24px auto;padding:20px}
    .logo{display:flex;align-items:center;gap:.5rem;margin-bottom:1rem}
    .logo img{height:28px}
    .card{border:1px solid #e5e7eb;border-radius:10px;background:#fff;padding:20px;box-shadow:0 4px 12px rgba(0,0,0,.04)}
    .row{margin:.5rem 0}
    label{display:block;margin:.25rem 0}
    textarea{width:100%;min-height:120px}
    select,input,textarea{padding:.5rem;border:1px solid #d1d5db;border-radius:8px}
    .btn{display:inline-block;padding:.6rem 1rem;border-radius:8px;text-decoration:none;border:0;cursor:pointer}
    .btn-primary{background:#2563eb;color:#fff}
  </style>
</head>
<body>
  <div class="container">
    <div class="logo">
  <img src="{{ asset('images/svg/OFT-Logo.png') }}" alt="Logo">
      <strong>Geri Bildirim İşlemi</strong>
    </div>
    <div class="card">
      <h2>Geri Bildirim #{{ $fb->id }}</h2>
      <p><strong>Kategori:</strong> {{ $fb->category ?? '-' }} | <strong>Durum:</strong> {{ $fb->status }}</p>
      <p><strong>Mesaj:</strong></p>
      <pre style="white-space:pre-wrap">{{ $fb->message }}</pre>

      <form method="POST" action="{{ $postUrl }}">
        @csrf
        @method('POST')
        <div class="row">
          <label for="status">Durum</label>
          <select id="status" name="status">
            <option value="in_progress" {{ (isset($preset) && $preset==='in_progress') ? 'selected' : '' }}>Değerlendiriliyor</option>
            <option value="closed">Tamamlandı</option>
            <option value="invalid" {{ (isset($preset) && $preset==='invalid') ? 'selected' : '' }}>Uygunsuz</option>
          </select>
        </div>
        <div class="row">
          <label for="reason">Gerekçe/Not (opsiyonel, min 5 karakter)</label>
          <textarea id="reason" name="reason" placeholder="Kısa bir not bırakabilirsiniz" oninput="document.getElementById('cnt').innerText=this.value.length"></textarea>
          <div class="muted" style="font-size:12px;margin-top:4px">Karakter: <span id="cnt">0</span></div>
        </div>
        <button class="btn btn-primary" type="submit">Gönder</button>
      </form>
    </div>
  </div>
</body>
</html>
