@php($fb = $fb)
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Geri Bildirim İşlemi Tamamlandı</title>
  <style>
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#111827}
    .container{max-width:720px;margin:24px auto;padding:20px}
    .card{border:1px solid #e5e7eb;border-radius:10px;background:#fff;padding:20px}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>İşlem Tamamlandı</h2>
      <p>#{{ $fb->id }} nolu geri bildirim <strong>{{ $status }}</strong> olarak güncellendi.</p>
      <p>Teşekkürler.</p>
    </div>
  </div>
</body>
</html>
