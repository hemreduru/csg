<?php

return [
    'title' => 'Profil',
    'page_title' => 'Profil Ayarları',
    'breadcrumbs' => [
        'home' => 'Ana Sayfa',
        'section' => 'Profil',
    ],
    'summary' => [
        'title' => 'Hesap Özeti',
        'subtitle' => 'Hesap bilgilerini, güvenlik ayarlarını ve bağlı Google hesaplarını yönet.',
        'email_verified' => 'E-posta doğrulandı',
        'email_unverified' => 'E-posta doğrulanmadı',
        'connected_accounts' => '{1} :count bağlı Google hesabı|[2,*] :count bağlı Google hesabı',
    ],
    'sections' => [
        'account' => [
            'title' => 'Profil Bilgileri',
            'description' => 'Hesap bilgilerini ve e-posta adresini güncelle.',
        ],
        'password' => [
            'title' => 'Şifre Güncelle',
            'description' => 'Güvenlik için uzun ve rastgele bir şifre kullan.',
        ],
        'delete' => [
            'title' => 'Hesabı Sil',
            'description' => 'Hesabınız silindikten sonra tüm veriler kalıcı olarak silinir.',
            'confirm_title' => 'Hesabını silmek istediğine emin misin?',
            'confirm_text' => 'Kalıcı olarak silmek için şifreni girerek onayla.',
        ],
    ],
    'actions' => [
        'save' => 'Değişiklikleri Kaydet',
        'update_password' => 'Şifre Güncelle',
        'delete' => 'Hesabı Sil',
        'resend_verification' => 'Doğrulama e-postasını tekrar gönder',
    ],
    'fields' => [
        'current_password' => 'Mevcut Şifre',
        'new_password' => 'Yeni Şifre',
    ],
    'messages' => [
        'updated' => 'Profil güncellendi.',
        'update_failed' => 'Profil güncellenemedi. Lütfen tekrar deneyin.',
        'password_updated' => 'Şifre güncellendi.',
        'password_update_failed' => 'Şifre güncellenemedi. Lütfen tekrar deneyin.',
        'delete_failed' => 'Hesap silinemedi. Lütfen tekrar deneyin.',
        'verification_sent' => 'E-posta adresine yeni bir doğrulama bağlantısı gönderildi.',
        'email_unverified' => 'E-posta adresin doğrulanmamış.',
    ],
];

