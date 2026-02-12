<?php

return [
    'title' => 'Profil',
    'sections' => [
        'account' => [
            'title' => 'Profil Bilgileri',
            'description' => 'Hesap bilgilerini ve e-posta adresini guncelle.',
        ],
        'password' => [
            'title' => 'Sifre Guncelle',
            'description' => 'Guvenlik icin uzun ve rastgele bir sifre kullan.',
        ],
        'delete' => [
            'title' => 'Hesabi Sil',
            'description' => 'Hesabiniz silindikten sonra tum veriler kalici olarak silinir.',
            'confirm_title' => 'Hesabini silmek istedigine emin misin?',
            'confirm_text' => 'Kalici olarak silmek icin sifreni girerek onayla.',
        ],
    ],
    'actions' => [
        'save' => 'Degisiklikleri Kaydet',
        'update_password' => 'Sifre Guncelle',
        'delete' => 'Hesabi Sil',
        'resend_verification' => 'Dogrulama e-postasini tekrar gonder',
    ],
    'fields' => [
        'current_password' => 'Mevcut Sifre',
        'new_password' => 'Yeni Sifre',
    ],
    'messages' => [
        'updated' => 'Profil guncellendi.',
        'update_failed' => 'Profil guncellenemedi. Lutfen tekrar deneyin.',
        'password_updated' => 'Sifre guncellendi.',
        'password_update_failed' => 'Sifre guncellenemedi. Lutfen tekrar deneyin.',
        'delete_failed' => 'Hesap silinemedi. Lutfen tekrar deneyin.',
        'verification_sent' => 'E-posta adresine yeni bir dogrulama linki gonderildi.',
        'email_unverified' => 'E-posta adresin dogrulanmamis.',
    ],
];
