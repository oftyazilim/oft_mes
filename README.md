# oft_mes
yeni kalip calismasi

## Fotoğraf Kayıt/Okuma (Cross-platform)

Linux/macOS üzerinde Windows UNC yolları (\\\\sunucu\paylasim) doğrudan çalışmaz. Aşağıdaki .env anahtarları ile kök dizinleri platforma uygun şekilde tanımlayın:

- PHOTO_KK_DIR=/mnt/oft/kk-fotolari
- PHOTO_SK_DIR=/mnt/oft/sk-fotolari
- PHOTO_BASE_URL=http://<host>:<port>/photos/

Notlar:
- Bu dizinler, ağ paylaşımlarının (CIFS/SMB) işletim sistemine mount edilmiş lokal yolları olmalıdır ve PHP'nin çalıştığı kullanıcı tarafından yazılabilir olmalıdır (www-data/_www/_http vb.).
- Uygulama, yol birleştirmeyi platform bağımsız yapar (DIRECTORY_SEPARATOR), ters/ileri slash farkı sorun olmaz.
- Public servis URL'leri `/api/oft-resimler/{isemri_no}/{filename}` ve `/api/stok-resimler/{itemCode}/{filename}` üzerinden verilir.

## Build Zamanı ve Versiyon Gösterimi

Footer'da otomatik olarak aşağıdaki bilgiler görüntülenir:

- Son güncelleme (build time): Vite build anında İstanbul saatine göre enjekte edilir (`__BUILD_TIME__`).
- Uygulama versiyonu: `package.json` içindeki `version` alanı + mevcut git kısa hash (`vX.Y.Z+abc123`) formatında (`__APP_VERSION__`).

Override:
- CI/CD pipeline'da farklı bir değer isterseniz `APP_VERSION` ortam değişkeni sağlayabilirsiniz. Hash ekli değilse otomatik eklenir.

Git yoksa veya repo değilse hash eklenmez (sadece `vX.Y.Z`).

Önerilen sürüm akışı:
1. `package.json` versiyon numarasını semantic versioning'e göre artırın.
2. (Opsiyonel) Git tag oluşturun: `git tag vX.Y.Z && git push --tags`.
3. Build sonrası footer'da `vX.Y.Z+<hash>` görünümünü doğrulayın.
