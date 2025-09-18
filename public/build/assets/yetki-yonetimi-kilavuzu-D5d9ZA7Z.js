import{V as t}from"./VRow-Bvzmq6zu.js";import{V as o}from"./VCol-CUagATwZ.js";import{V as s,b as d,d as k}from"./VCard-CrTJSgVK.js";import{V as u}from"./VCardText-TLedgTXA.js";import{V as m}from"./VContainer-SPQGG11d.js";import{d as c,g as p,o as y,f as a,b as i,t as n,e as l,v as z,m as b}from"./main-D-SiMLc-.js";import{_ as g}from"./_plugin-vue_export-helper-DlAUqK2U.js";/* empty css              */import"./VAvatar-eX3ssqTG.js";import"./VImg-Cc13YmMm.js";const f=`# Yetki Yönetimi Kılavuzu (Roller & İzinler)

Bu kılavuz; projedeki rol/izin (Spatie Permission) mimarisini, CASL tarafındaki kullanım kurallarını ve sayfa/aksiyon görünürlüğü için pratik örnekleri içerir.

## 1) Temel Kavramlar
- Rol (role): Birden çok izni kapsayan mantıksal gruplar. Ör: planlama_oper, depo_gorevli.
- İzin (permission): Belirli bir konuda (subject) belirli bir eylemi (action) yapma yetkisi.
- Kullanıcı (user): Bir veya daha fazla role sahip olabilir ve ayrıca doğrudan izinleri de olabilir.

## 2) İzin Adı Söz Dizimi ve “_” Karakteri
İzin adları action_subject formatındadır ve alt çizgi “_” zorunludur.
- Sol taraf (action): read, create, update, delete, manage gibi fiiller.
- Sağ taraf (subject): montaj, planlama, depo, kalite, users, roles gibi alan/sayfa/konu.

Örnekler
- read_montaj: Montaj sayfalarını görüntüleyebilir.
- update_planlama: Planlama ekranlarında güncelleme yapabilir.
- manage_all: Tüm konularda tüm eylemler (özel durum, bkz. aşağıdaki eşleme).

Desteklenen kısaltmalar
- view_… -> read
- edit_… -> update

Dikkat
- action_subject biçimi tutarlı olmalı: küçük harf, Türkçe karakter kullanmayın, boşluk yok.
- subject isimleri, ön uçta CASL’da ve router meta subject alanlarında kullanılan isimlerle eşleşmelidir.

## 3) Backend Eşleme Kuralları (User::getAbilityRules)
Backend, kullanıcının Spatie izinlerini alıp CASL kurallarına dönüştürür:
- action_subject -> { action: action, subject: subject } (view->read, edit->update şeklinde normalize edilir)
- manage -> { action: 'manage', subject: 'all' }
- Bare subject (alt çizgi olmayan tek kelime izin) -> { action: 'read', subject: <name> }

Not: UI’de önerilen biçim action_subject olduğundan, bare subject kullanımı tavsiye edilmez; varsa sadece read olarak yorumlanır.

## 4) Rol – İzin – Kullanıcı Bağı
- Rol -> Birden fazla izin içerir.
- Kullanıcı -> Birden fazla role sahip olabilir (rol izinleri birleşir) ve ek olarak kullanıcıya doğrudan izin atanabilir.
- Doğrudan kullanıcı izinleri, rol izinlerine ek olarak değerlendirilir.

Öneri
- Mümkün olduğunca rol bazlı yönetim yapın, istisnalar için doğrudan kullanıcı izni verin.

## 5) Guard ve Cache
- Guard: projede guard_name 'web' kullanılır.
- Spatie cache: İzin/rol değişikliklerinden sonra cache temizlenir (uygulamada kontrolör bunu otomatik yapar).

## 6) Adlandırma Kuralları ve Standartlar
- action: read | create | update | delete | manage (gerekirse genişletilebilir ancak CASL tarafında karşılığını eklemek gerekir)
- subject: genel modül/sayfa/grup isimleri. Ör: genel, montaj, planlama, kalite, depo, satinalma, satis, users, roles.
- Küçük harf, alt çizgi ile ayırma. Ör: read_kalite_rapor (çok gerekirse iki alt çizgi de olabilir ama sade kalın).

## 7) Şablonlar (Templates) ve Toplu Atamalar
Yetki Yönetimi ekranındaki şablonlar, role hızlıca bir izin seti ekler. Şablonu uygula -> Kaydet yaptığınızda backend, eksik izin adlarını otomatik oluşturur ve role senkronlar.

## 8) Ek İzinler Verilebilir mi?
Evet. Kullanıcıya doğrudan izin eklenebilir. Bu izinler rol izinlerine eklenir. Ör: Bir kullanıcı genel olarak sadece read_planlama yetkisine sahip bir rolde olabilir ama kullanıcıya doğrudan update_planlama verilerek istisna yaratılabilir.

## 9) Sayfa Erişimi ve Router Meta
Sayfaların erişimi, route meta içine yazılan action/subject üzerinden CASL ile kontrol edilir.

Örnek (Vue 3):
- Sayfa dosyasında
  definePage({ meta: { action: 'read', subject: 'planlama' } })

Bu sayfayı açabilmek için kullanıcının en az read_planlama izni olmalıdır.

## 10) Buton/Aksiyon Görünürlüğü (CASL ile)
Bileşenlerde görünürlük/aktiflik kontrolü için ability kullanın.

Örnekler (Vuetify):
- Görünürlüğü tamamen gizlemek:
  <VBtn v-if="$ability.can('update','planlama')" color="primary">Kaydet</VBtn>

- Butonu pasif yapmak (görünür ama tıklanamaz):
  <VBtn :disabled="!$ability.can('update','planlama')" color="primary">Kaydet</VBtn>

- Liste/ikon vs. için:
  <VIcon v-if="$ability.can('delete','montaj')" icon="tabler-trash" />

Not: $ability, CASL sağlayıcısından gelir. Projede girişte veya sonrasında cookie’deki kurallar ile initialize edilir ve değişikliklerde güncellenir.

## 11) CASL Kurallarının Yenilenmesi
Rol/izin değiştirildiğinde, değişiklik kendinizdeyse kurallar frontende otomatik yenilenir. Böylece sayfa yeniden yüklenmeden görünürlük/erişim anında güncellenir.

## 12) Backend API (Özet)
- GET /api/roles -> roller (+ kullanıcı sayısı)
- POST /api/roles { name, permissions? } -> rol oluştur (eksik izinleri otomatik oluşturur)
- PUT /api/roles/{id} { name, permissions } -> rol güncelle (eksik izinleri otomatik oluşturur)
- DELETE /api/roles/{id} -> rol sil
- GET /api/permissions -> tüm izinler
- POST /api/permissions { name } -> izin oluştur (benzersizlik kontrolü 422)
- DELETE /api/permissions/{id} -> izin sil
- GET /api/users/all-basic -> kullanıcı id, ad, e-posta
- GET /api/users/{id}/roles -> kullanıcının rol adları
- POST /api/users/{id}/roles { roles: string[] } -> rol ata/senkronla
- GET /api/users/{id}/permissions -> atanan ve atanabilir izinler
- POST /api/users/{id}/permissions/{permissionId} -> kullanıcıya izin ekle
- DELETE /api/users/{id}/permissions/{permissionId} -> kullanıcıdan izin kaldır

## 13) Sık Karşılaşılan Hatalar ve Çözümler
- “There is no permission named … for guard web”: İzin adı henüz DB’de yok. Backend artık yoksa otomatik oluşturuyor; eski kayıtlarda oluşursa izin adını kontrol edin.
- Rol adı zaten var (422): Rol adını değiştirin; backend Türkçe doğrulama döner.
- PostgreSQL id tekil ihlali (users_pkey): Sekans geride kalmış olabilir; proje migrasyonu sekansı MAX(id) seviyesine çeker.
- İzin/rol değişti ama görünürlük değişmedi: Cache/ability yenilemesi beklenir; sayfayı yenilemek veya tekrar giriş yapmak geçici çözüm; projede ilgili işlemler sonrasında otomatik yenileme yapılır.

## 14) En İyi Uygulamalar
- Her modül için öncelikle read_subject iznini tanımlayın; ayrıntı eylemler için create/update/delete ekleyin.
- Şablonları mümkün olduğunca modüler tutun: ör. planlama_oper sadece planlama ile ilgili izinleri içersin.
- Doğrudan kullanıcı iznini istisna olarak kullanın; genel yönetim rol bazlı olsun.
- İzin adlarını statik sabitler veya tek merkezden üreterek tutarlılığı koruyun.

## 15) Görseller (Şemalar)
Aşağıdaki görseller, akışı ve eşlemeyi özetler (örnek/placeholder):

- docs/images/acl-flow.png — Kullanıcı → Roller → İzinler → CASL kuralları → UI görünürlüğü.
- docs/images/permission-mapping.png — action_subject → { action, subject } dönüşümü; view/edit eşlemeleri; manage_all.
- docs/images/ui-ability.png — Buton/sayfa görünürlüğü için $ability.can kullanımı.

Bu dosyaları kendi süreçlerinize göre güncelleyebilir veya ekran görüntüleriyle değiştirebilirsiniz.

## 16) Word’e Aktarma
Bu kılavuzu Word’e dönüştürmek için:
- VS Code’da “Markdown PDF” veya benzeri eklenti ile docx’e aktarın, veya
- Pandoc kullanın (opsiyonel):
  pandoc docs/yetki-yonetimi-kilavuzu.md -o docs/yetki-yonetimi-kilavuzu.docx

---
Sorularınız veya özel senaryolarınız için bu dokümana madde ekleyebiliriz.
`,v={class:"markdown-pre"},_=c({__name:"yetki-yonetimi-kilavuzu",setup(B){const r=f;return(j,e)=>(y(),p(m,{fluid:""},{default:a(()=>[i(t,null,{default:a(()=>[i(o,{cols:"12"},{default:a(()=>[i(s,null,{default:a(()=>[i(d,null,{default:a(()=>e[0]||(e[0]=[n("Yetki Yönetimi Kılavuzu")])),_:1,__:[0]}),i(k,null,{default:a(()=>e[1]||(e[1]=[n("Bu sayfa, depo içindeki markdown dokümanını gösterir.")])),_:1,__:[1]}),i(u,null,{default:a(()=>[l("div",v,z(b(r)),1),e[2]||(e[2]=l("div",{class:"mt-4 text-medium-emphasis"}," PDF/Word sürümleri için Yardım menüsündeki örnek bağlantıları kullanabilirsiniz. ",-1))]),_:1,__:[2]})]),_:1})]),_:1})]),_:1})]),_:1}))}}),D=g(_,[["__scopeId","data-v-018ce920"]]);export{D as default};
