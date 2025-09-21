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
