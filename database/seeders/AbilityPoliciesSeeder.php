<?php

namespace Database\Seeders;

use App\Models\AbilityPolicy;
use Illuminate\Database\Seeder;

class AbilityPoliciesSeeder extends Seeder
{
    public function run(): void
    {
        // Key => [actions, subjects, description]
        $items = [
            // Dashboard
            'menu:dashboard' => [['read'], ['genel'], 'Dashboard ana grup'],
            'menu:dashboard:dash-bukum' => [['read'], ['genel'], 'Büküm Dashboard'],
            'menu:dashboard:dash-bukum-bak' => [['read'], ['genel'], 'Büküm Dashboard test'],
            'menu:dashboard:analytics' => [['read'], ['genel'], 'Analytics'],
            'menu:dashboard:crm' => [['read'], ['genel'], 'CRM'],
            'menu:dashboard:ecommerce' => [['read'], ['genel'], 'Ecommerce'],
            'menu:dashboard:academy' => [['read'], ['genel'], 'Academy'],
            'menu:dashboard:logistics' => [['read'], ['genel'], 'Logistics'],

            // Planlama
            'menu:planlama' => [['read'], ['planlama'], 'Planlama ana grup'],
            'menu:planlama:is-emirleri-tumu' => [['read'], ['planlama'], 'İş Emirleri - Tümü'],
            'menu:planlama:is-emirleri-master' => [['read'], ['planlama'], 'İş Emirleri - Master'],
            'menu:planlama:is-emirleri-kapanmislar' => [['read'], ['planlama'], 'İş Emirleri - Kapanmışlar'],
            'menu:planlama:is-emirleri-montaj' => [['read'], ['planlama','montaj'], 'İş Emirleri - Montaj'],
            'menu:planlama:is-emirleri-mekanik' => [['read'], ['planlama','mekanik'], 'İş Emirleri - Mekanik'],
            'menu:planlama:ihtiyac-istasyon' => [['read'], ['planlama','montaj'], 'Malzeme İhtiyaç (İstasyon)'],
            'menu:planlama:ihtiyac-toplu' => [['read'], ['planlama','montaj'], 'Malzeme İhtiyaç (Toplu)'],
            'menu:planlama:kapasite-hesapla' => [['create','read'], ['planlama'], 'Kapasite Hesapla'],
            'menu:planlama:urun-agaci-sorgula' => [['read'], ['planlama','montaj','mekanik','satinalma','depo','satis'], 'Ürün Ağacı Sorgula'],

            // Üretim
            'menu:uretim' => [['read'], ['planlama','montaj','mekanik'], 'Üretim ana grup'],
            'menu:uretim:duruslar-montaj' => [['read'], ['planlama','montaj'], 'Duruşlar (Montaj)'],
            'menu:uretim:duruslar-mekanik' => [['read'], ['planlama','mekanik'], 'Duruşlar (Mekanik)'],

            // Satınalma
            'menu:satinalma' => [['read'], ['satinalma','planlama'], 'Satınalma ana grup'],
            'menu:satinalma:siparisler' => [['read'], ['satinalma','planlama'], 'Satınalma Siparişleri'],
            'menu:satinalma:talepler' => [['read'], ['satinalma','planlama'], 'Satınalma Talepleri'],
            'menu:satinalma:ihtiyac-listesi-siparis' => [['read'], ['satinalma','planlama'], 'Malzeme İhtiyaç Listesi (Sipariş)'],

            // Satış
            'menu:satis' => [['read'], ['satis','planlama'], 'Satış ana grup'],
            'menu:satis:musteri-siparisleri' => [['read'], ['satis','planlama'], 'Müşteri Siparişleri'],
            'menu:satis:ciro-ozet' => [['read'], ['satis','planlama'], 'Ciro Özet Tablo'],
            'menu:satis:ihtiyac-listesi-siparis' => [['read'], ['satis','planlama'], 'Malzeme İhtiyaç Listesi (Sipariş)'],

            // Depo
            'menu:depo' => [['read'], ['depo','planlama','satinalma','satis','montaj'], 'Depo ana grup'],
            'menu:depo:urun-sorgula' => [['read'], ['depo','terminal'], 'Ürün Sorgula'],
            'menu:depo:stoklar' => [['read'], ['depo','planlama','satinalma','satis','montaj'], 'Stoklar'],

            // Kalite
            'menu:kalite' => [['read'], ['kalite'], 'Kalite ana grup'],
            'menu:kalite:uretim-kontrol' => [['create','read'], ['kalite'], 'Ürün Kontrolü'],
            'menu:kalite:kontroller' => [['read'], ['kalite'], 'Kontrol Listeleri'],

            // İstasyonlar
            'menu:istasyonlar' => [['read'], ['montaj','mekanik'], 'İstasyonlar ana grup'],
            'menu:istasyonlar:montaj' => [['read'], ['montaj'], 'Montaj İstasyonu'],
            'menu:istasyonlar:rollform' => [['read'], ['mekanik'], 'Rollform İstasyonu'],

            // Sistem Yönetimi
            'menu:sistem' => [['manage','read'], ['genel'], 'Sistem Yönetimi ana grup'],
            'menu:sistem:kullanicilar' => [['manage','read'], ['genel','users'], 'Kullanıcılar alt grup'],
            'menu:sistem:kullanicilar:liste' => [['manage','read'], ['genel','users'], 'Kullanıcı Listesi'],
            'menu:sistem:kullanicilar:yetki-yonetimi' => [['manage','read'], ['users'], 'Yetki Yönetimi'],
            'menu:sistem:kullanicilar:loglar' => [['manage','read'], ['genel'], 'Kullanıcı Logları'],
            'menu:sistem:feedback' => [['manage','read'], ['genel'], 'Geri Bildirimler'],
            'menu:sistem:feedback:ayarlar' => [['manage','read'], ['genel'], 'Bildirim Ayarları'],
            'menu:sistem:acl-policy-yonetimi' => [['manage'], ['genel'], 'ACL Policy Yönetimi'],
        ];

        foreach ($items as $key => [$actions, $subjects, $desc]) {
            AbilityPolicy::updateOrCreate(
                ['key' => $key],
                [
                    'actions' => $actions,
                    'subjects' => $subjects,
                    'description' => $desc,
                ]
            );
        }
    }
}
