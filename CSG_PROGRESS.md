# CSG (Cloud Storage Gateway) Implementation Progress

Last updated: 2026-02-10

Bu dokuman, repo icindeki mevcut implementasyon durumunu phase'lere bolunmus sekilde takip etmek icindir.

## Hedef (Kisa)

- Metronic 8.2 (demo1) UI, Laravel Blade layout/partials yapisina otursun.
- Auth: email/password + Google ile giris.
- Kullanici 1'den fazla Google hesabini baglayabilsin, listeleyebilsin, varsayilan secebilisin.
- Drive browser sayfasinda hesaplar arasi switch yaparak dosyalari gorebilsin.

## Calistirma / Kontrol Komutlari

- Metronic asset publish: `composer run metronic:publish`
- Route kontrol: `php artisan route:list`
- Migration: `php artisan migrate`
- Test (opsiyonel): `php artisan test`

## Phase 0: Metronic 8.2 + Auth (Email/Password + Google Login) + Locale

Durum: MVP tamam.

### Tamamlananlar

- Metronic asset publish komutu:
  - `app/Console/Commands/MetronicPublishCommand.php`
  - hedef: `public/assets` (demo path uyumlu)
  - composer script: `composer.json` icinde `metronic:publish`
- Blade layout/partials (Metronic demo1 shell):
  - `resources/views/layouts/app.blade.php`
  - `resources/views/layouts/auth.blade.php`
  - `resources/views/layouts/partials/*`
- Locale switch (TR/EN):
  - middleware: `app/Http/Middleware/SetLocale.php`
  - controller + request: `app/Http/Controllers/LocaleController.php`, `app/Http/Requests/UpdateLocaleRequest.php`
  - route: `POST /locale` (`routes/web.php`)
- Auth UI (Metronic):
  - `resources/views/auth/*`
  - `/` login degilse `login`, login ise `drive.index`
- Profile (Metronic + transaction+logging):
  - `resources/views/profile/edit.blade.php`
  - `app/Http/Controllers/ProfileController.php`
  - `app/Http/Controllers/Auth/PasswordController.php`
- Kullanim disi Breeze/Tailwind kalintilari temizlendi:
  - silindi: `resources/views/welcome.blade.php`
  - silindi: `resources/views/layouts/navigation.blade.php`
  - silindi: `resources/views/components/*`
- Google OAuth callback URI notu eklendi:
  - `.env.example`

### Kalanlar / TODO

- (Opsiyonel) Node/Vite/Tailwind pipeline kaldirilacaksa: `package.json`, `vite.config.js`, `resources/css`, `resources/js` tarafi sadelestirilebilir.

## Phase 1: Google Drive Connections (Coklu Google Hesap Baglama + Switch)

Durum: MVP tamam, opsiyonel iyilestirmeler var.

### Tamamlananlar

- DB + model:
  - `database/migrations/2026_02_10_220010_create_drive_connections_table.php`
  - `app/Models/DriveConnection.php`
- Google login sonrasi otomatik connection create/update:
  - `app/Http/Controllers/Auth/GoogleAuthController.php`
- Connection yonetim ekrani:
  - `GET /connections/google` + add/rename/default/disconnect
  - `app/Http/Controllers/Drive/GoogleConnectionsController.php`
  - `resources/views/connections/google/index.blade.php`
- Default connection silinirse otomatik yeni default seciliyor.
- Header dropdown ile hesap switch:
  - `resources/views/layouts/partials/header.blade.php`

### Kalanlar / TODO

- [ ] (Opsiyonel) Google token revoke + status `revoked` senaryosu.
- [ ] Datatable standardi: connections listesi su an basit HTML table (server-side datatable'ye tasinabilir).

## Phase 2: Drive Browser UI (Hesap Bazli Dosya Goruntuleme + Temel CRUD)

Durum: Browse + temel CRUD tamam.

### Tamamlananlar

- Browse:
  - `GET /drive`, `GET /drive/{connection}`, `GET /drive/{connection}/browse`
  - `app/Http/Controllers/Drive/DriveController.php`
  - `app/Services/GoogleDrive/DriveGateway.php`
  - `resources/views/drive/browse.blade.php`
- CRUD (UI + backend):
  - Create folder: `POST /drive/{connection}/folders`
  - Upload file (<=2MB): `POST /drive/{connection}/upload`
  - Rename: `POST /drive/{connection}/items/{itemId}/rename`
  - Trash/Delete: `POST /drive/{connection}/items/{itemId}/trash`
  - Download: `GET /drive/{connection}/items/{itemId}/download`
  - Not: Google Docs/Sheets/Slides gibi "google-apps" dosyalar PDF export ile indiriliyor.
- Validation (FormRequest + error bags):
  - `app/Http/Requests/Drive/*`
- Name sanitize:
  - `app/Support/DriveNameSanitizer.php`
- Authorization:
  - `{connection}` tum drive action'larinda user'a ait mi kontrolu var (404).

### Kalanlar / TODO

- (Opsiyonel) Move/Copy gibi operasyonlar (UI + API).
- (Opsiyonel) path->fileId cache + invalidation (performans).

## Phase 3+: (Baslanmadi)

- Phase 3 Buckets (folder mapping)
- Phase 4 API Keys + Public API v1 (Sanctum)
- Phase 5 Dayaniklilik (resumable upload, queues, audit log, rate limit/backoff)

