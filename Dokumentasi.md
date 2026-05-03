# Dokumentasi Project Laravel + Inertia.js + Vue 3 + Atlantis Admin

> Panduan belajar lengkap untuk memahami setiap komponen dalam project ini.

---

## Daftar Isi

1. [Gambaran Umum Stack Teknologi](#1-gambaran-umum-stack-teknologi)
2. [Cara Inertia.js Bekerja](#2-cara-inertiajs-bekerja)
3. [Setup & Instalasi dari Nol](#3-setup--instalasi-dari-nol)
4. [File Konfigurasi Utama](#4-file-konfigurasi-utama)
5. [Middleware](#5-middleware)
6. [Routes (Routing)](#6-routes-routing)
7. [Modul: Struktur & Penjelasan](#7-modul-struktur--penjelasan)
8. [Module Biodata](#8-module-biodata)
9. [Module Registration](#9-module-registration)
10. [Module Document](#10-module-document)
11. [Layout Atlantis Admin](#11-layout-atlantis-admin)
12. [Komponen SweetAlert](#12-komponen-sweetalert)
13. [Alur Data Lengkap](#13-alur-data-lengkap)

---

## 1. Gambaran Umum Stack Teknologi

Project ini adalah **Sistem Informasi Penerimaan Mahasiswa Baru (PMB)** yang dibangun dengan:

| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| Laravel | 12.x | Backend framework PHP |
| Inertia.js | 2.x | Jembatan antara Laravel dan Vue |
| Vue 3 | 3.4+ | Frontend framework JavaScript |
| Vite | 7.x | Build tool / bundler |
| Tailwind CSS | 3.x | Utility-first CSS framework |
| Atlantis Admin | Custom | Template desain sidebar admin |

**Konsep Utama:** Project ini **TIDAK menggunakan Blade sebagai halaman**. Blade hanya dipakai sebagai "jembatan" satu kali (`app.blade.php`), setelah itu semua tampilan dikelola oleh Vue. Inertia.js yang bertugas menghubungkan keduanya.

---

## 2. Cara Inertia.js Bekerja

### Konsep Dasar

Inertia.js memungkinkan kamu membangun SPA (Single Page Application) **tanpa perlu membuat REST API secara terpisah**. Laravel tetap menangani routing dan logika bisnis, tapi mengembalikan komponen Vue alih-alih HTML Blade.

```
REQUEST PERTAMA (Full Page Load):
Browser --> Laravel --> Blade (app.blade.php) --> HTML + Vue App
                                                   |
                               Inertia mount Vue di <div id="app">

REQUEST SELANJUTNYA (Navigasi):
Browser --> Inertia intersep <Link> click --> AJAX JSON request
         --> Laravel kembalikan JSON { component, props, url }
         --> Vue swap komponen tanpa reload halaman
```

### Perbandingan dengan Cara Biasa

| Cara Biasa (Blade) | Cara Inertia |
|--------------------|--------------|
| Controller return `view('biodata.index', $data)` | Controller return `Inertia::render('Modules/Biodata/Index', $data)` |
| Data diterima di Blade `{{ $biodata }}` | Data diterima di Vue sebagai `props` |
| Setiap navigasi reload halaman penuh | Navigasi hanya swap komponen (SPA) |
| Form pakai `<form action="">` biasa | Form pakai `useForm()` dari Inertia |

---

## 3. Setup & Instalasi dari Nol

Berikut langkah-langkah jika ingin membangun project serupa dari awal:

### Langkah 1: Buat Project Laravel dengan Inertia + Vue

```bash
# Install Laravel
composer create-project laravel/laravel nama-project

# Masuk ke folder project
cd nama-project

# Install Inertia sisi server (Laravel)
composer require inertiajs/inertia-laravel

# Install Inertia sisi client (Vue), plugin Vite, dan Vue
npm install @inertiajs/vue3 vue @vitejs/plugin-vue
```

### Langkah 2: Buat Root Template Blade

Buat file `resources/views/app.blade.php`:

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

> **Penjelasan `@directives`:**
> - `@routes` → membuat variabel global `route()` di JavaScript (dari package Ziggy)
> - `@vite(...)` → memasukkan script dan CSS yang dikelola Vite
> - `@inertia` → menghasilkan `<div id="app" data-page="...">` yang menjadi mount point Vue
> - `@inertiaHead` → mengisi tag `<title>` dari komponen Vue menggunakan `<Head>`

### Langkah 3: Daftarkan Middleware Inertia

Di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
})
```

Middleware ini **wajib ada** karena bertugas meng-share data global (auth, flash messages) ke semua halaman Vue.

### Langkah 4: Buat Middleware HandleInertiaRequests

```bash
php artisan make:middleware HandleInertiaRequests
```

Isi file-nya extend dari `Inertia\Middleware` (sudah ada di project ini — lihat penjelasan di bagian Middleware).

### Langkah 5: Konfigurasi Vite

Edit `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',  // Entry point utama
            refresh: true,                  // Auto reload saat file PHP berubah
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
```

### Langkah 6: Setup Entry Point Vue (`resources/js/app.js`)

```js
import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

createInertiaApp({
    title: (title) => `${title} - AppName`,   // Format judul tab browser

    resolve: (name) =>                         // Cara menemukan file Vue berdasarkan nama
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {        // Setup Vue app
        return createApp({ render: () => h(App, props) })
            .use(plugin)        // Plugin Inertia
            .use(ZiggyVue)      // Agar bisa pakai route() di Vue
            .mount(el);         // Mount ke <div id="app">
    },

    progress: { color: '#4B5563' },  // Loading bar di atas halaman
});
```

> **Penjelasan penting:**
> - `resolvePageComponent` → saat controller memanggil `Inertia::render('Modules/Biodata/Index', ...)`, Inertia akan otomatis mencari file `resources/js/Pages/Modules/Biodata/Index.vue`
> - `ZiggyVue` → mengaktifkan fungsi `route('biodata.index')` di dalam template Vue (sama seperti helper `route()` di PHP)

### Langkah 7: Pasang Template Atlantis

Template Atlantis di project ini **tidak diinstall lewat npm** — melainkan dibuat **dari scratch menggunakan CSS custom** yang ditulis langsung di dalam `AdminLayout.vue` menggunakan tag `<style scoped>`. Ini cara yang lebih bersih karena:

- Tidak ada ketergantungan eksternal
- CSS-nya persis seperti tampilan Atlantis yang asli
- Bisa dikustomisasi bebas

Untuk memasangnya, cukup copy isi `resources/js/Layouts/AdminLayout.vue` ke project baru.

### Langkah 8: Jalankan Development Server

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (bundler JS/CSS)
npm run dev
```

---

## 4. File Konfigurasi Utama

### `resources/views/app.blade.php`

File Blade **satu-satunya** yang dirender server secara langsung. Hanya berisi kerangka HTML kosong. Semua konten diisi oleh Vue setelah halaman dimuat.

### `resources/js/app.js`

Entry point seluruh aplikasi Vue. Dipanggil satu kali saat browser pertama membuka halaman. Mendaftarkan plugin Inertia dan Ziggy, lalu mount Vue ke DOM.

### `vite.config.js`

Konfigurasi build tool. Memberitahu Vite:
- File mana yang jadi entry point (`resources/js/app.js`)
- Plugin apa yang dipakai (laravel-vite-plugin, vue)

### `package.json`

Daftar dependency JavaScript project:
```json
{
    "@inertiajs/vue3": "^2.0.0",
    "@vitejs/plugin-vue": "^6.0.0",
    "laravel-vite-plugin": "^2.0.0",
    "vue": "^3.4.0",
    "tailwindcss": "^3.2.1"
}
```

---

## 5. Middleware

Middleware adalah "penjaga pintu" yang dieksekusi sebelum request sampai ke controller.

### `HandleInertiaRequests.php`

**Lokasi:** `app/Http/Middleware/HandleInertiaRequests.php`

**Fungsi:** Middleware khusus Inertia yang otomatis menambahkan data ke **setiap** response Vue, tanpa perlu ditulis ulang di setiap controller.

```php
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';  // Merujuk ke resources/views/app.blade.php

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            // Data user login tersedia di SEMUA halaman Vue via usePage().props.auth.user
            'auth' => [
                'user' => $request->user(),
            ],

            // Flash messages tersedia di semua halaman via usePage().props.flash
            'flash' => [
                'status'  => fn () => $request->session()->get('status'),
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info'    => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
```

> **Kenapa pakai `fn () =>`?** Ini adalah "lazy evaluation" — data flash message baru diambil saat benar-benar dibutuhkan, bukan di awal request. Ini mencegah pesan terbaca sebelum waktunya.

**Cara mengakses data ini di Vue:**

```js
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

// Ambil user yang login
const user = computed(() => page.props.auth?.user);

// Ambil flash message
const successMsg = computed(() => page.props.flash?.success);
```

### `RoleMiddleware.php`

**Lokasi:** `app/Http/Middleware/RoleMiddleware.php`

**Fungsi:** Membatasi akses route berdasarkan role user (admin, staff, superadmin, dll).

```php
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Cek 1: Apakah user sudah login?
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Cek 2: Apakah akun aktif?
        if (! $request->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        // Cek 3: Apakah role user sesuai yang diizinkan?
        if (! in_array($request->user()->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki izin akses.');
        }

        return $next($request);  // Lanjut ke controller
    }
}
```

**Cara mendaftarkan alias middleware** di `bootstrap/app.php`:

```php
$middleware->alias([
    'role' => \App\Http\Middleware\RoleMiddleware::class,
]);
```

**Cara pakai di route:**

```php
// Hanya admin, staff, dan superadmin yang bisa akses
Route::middleware(['auth', 'role:admin,staff,superadmin'])->group(function () {
    Route::get('/admin/dashboard', ...);
});
```

---

## 6. Routes (Routing)

**Lokasi:** `routes/web.php`

Routes mendefinisikan URL apa yang tersedia dan controller mana yang menanganinya.

### Struktur Routes Project Ini

```php
// Route "/" — redirect otomatis
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');   // Sudah login -> ke dashboard
    }
    return redirect()->route('login');           // Belum login -> ke halaman login
});

// Route dashboard — butuh login + email terverifikasi
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Group routes yang butuh login
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Registrasi
    Route::resource('registrations', RegistrationController::class)
        ->only(['index', 'create', 'store', 'show']);

    // Aksi tambahan untuk registrasi (bukan CRUD standar)
    Route::post('registrations/{registration}/submit', ...)->name('registrations.submit');
    Route::post('registrations/{registration}/verify', ...)->name('registrations.verify');
    Route::post('registrations/{registration}/accept', ...)->name('registrations.accept');
    Route::post('registrations/{registration}/reject', ...)->name('registrations.reject');

    // Biodata — Full resource
    Route::resource('biodata', StudentBiodataController::class)
        ->parameters(['biodata' => 'student_biodata']);  // Nama parameter custom

    // Dokumen
    Route::resource('documents', DocumentController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::get('documents/{document}/download', ...)->name('documents.download');
});

// Group khusus admin
Route::middleware(['auth', 'role:admin,staff,superadmin'])->group(function () {
    Route::get('/admin/dashboard', ...)->name('admin.dashboard');
});
```

### Resource Route Menghasilkan URL Apa?

`Route::resource('biodata', StudentBiodataController::class)` secara otomatis membuat:

| Method | URL | Action | Nama Route |
|--------|-----|--------|------------|
| GET | `/biodata` | `index()` | `biodata.index` |
| GET | `/biodata/create` | `create()` | `biodata.create` |
| POST | `/biodata` | `store()` | `biodata.store` |
| GET | `/biodata/{id}` | `show()` | `biodata.show` |
| GET | `/biodata/{id}/edit` | `edit()` | `biodata.edit` |
| PUT/PATCH | `/biodata/{id}` | `update()` | `biodata.update` |
| DELETE | `/biodata/{id}` | `destroy()` | `biodata.destroy` |

> **Kenapa pakai `.parameters(['biodata' => 'student_biodata'])`?**
> Karena nama model di PHP adalah `StudentBiodata`, Laravel secara default akan membuat parameter URL `{student_biodata}`. Ini harus sama dengan nama variabel di method controller (`$student_biodata`). Tanpa ini, Laravel tidak bisa otomatis inject model ke controller.

---

## 7. Modul: Struktur & Penjelasan

Project ini menggunakan **pola modular** — setiap fitur dikelompokkan dalam folder sendiri di dalam `app/Modules/`.

```
app/Modules/
├── Biodata/
│   ├── Controllers/
│   │   └── StudentBiodataController.php   <- Terima request, kembalikan Vue page
│   ├── Models/
│   │   └── StudentBiodata.php             <- Representasi tabel database
│   ├── Requests/
│   │   └── StudentBiodataRequest.php      <- Validasi input form
│   └── Services/
│       └── StudentBiodataService.php      <- Logika bisnis
│
├── Registration/
│   ├── Controllers/
│   │   ├── RegistrationController.php
│   │   └── PaymentVerificationController.php
│   ├── Models/
│   │   └── Registration.php
│   ├── Requests/
│   │   ├── StoreRegistrationRequest.php
│   │   └── ReviewPaymentRequest.php
│   └── Services/
│       ├── RegistrationService.php
│       └── PaymentVerificationService.php
│
└── Document/
    ├── Controllers/
    │   └── DocumentController.php
    ├── Models/
    │   └── Document.php
    ├── Requests/
    │   └── StoreDocumentRequest.php
    └── Services/
        └── DocumentService.php
```

### Pola Controller -> Service -> Model

Setiap modul menggunakan **Service Layer Pattern**:

```
Request dari Browser
       |
   Controller        <- Menerima request, validasi, memanggil service
       |
    Service          <- Logika bisnis (boleh/tidak, cek kondisi, dll)
       |
     Model           <- Query database
       |
  Inertia::render    <- Kembalikan data sebagai props ke Vue
```

**Keuntungan pola ini:**
- Controller tetap tipis (slim controller)
- Logika bisnis bisa dipakai ulang dari tempat berbeda
- Mudah diuji (unit test)

---

## 8. Module Biodata

### Model: `StudentBiodata.php`

```php
class StudentBiodata extends Model
{
    protected $table = 'student_biodata';  // Nama tabel di database

    protected $fillable = [               // Kolom yang boleh diisi via ::create() / ->update()
        'registration_id', 'nik', 'birth_place', 'birth_date',
        'gender', 'religion', 'address', 'province', 'city',
        'school_name', 'school_graduation_year',
        'parent_name', 'parent_phone', 'parent_job', 'photo',
    ];

    protected function casts(): array     // Konversi tipe data otomatis
    {
        return [
            'birth_date' => 'date',               // String -> Carbon date object
            'school_graduation_year' => 'integer', // String -> integer
        ];
    }

    // Relasi: Biodata dimiliki oleh satu Registration
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
```

### Request (Validasi): `StudentBiodataRequest.php`

Form Request adalah class khusus untuk validasi input sebelum data masuk ke controller.

```php
class StudentBiodataRequest extends FormRequest
{
    // Otorisasi: siapa yang boleh submit form ini?
    public function authorize(): bool
    {
        return $this->user() !== null;  // Harus sudah login
    }

    public function rules(): array
    {
        // Ambil ID biodata dari URL (untuk kasus edit)
        $biodata = $this->route('student_biodata');
        $biodataId = $biodata instanceof StudentBiodata ? $biodata->id : $biodata;

        return [
            // registration_id hanya wajib diisi jika user adalah admin/staff/superadmin
            'registration_id' => [
                Rule::requiredIf(fn () => in_array($this->user()?->role, ['admin', 'staff', 'superadmin'], true)),
                'nullable', 'integer', 'exists:registrations,id',
                Rule::unique('student_biodata', 'registration_id')->ignore($biodataId),
            ],
            'nik' => ['required', 'string', 'max:20',
                Rule::unique('student_biodata', 'nik')->ignore($biodataId)],  // NIK harus unik
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date'  => ['required', 'date', 'before:today'],  // Harus di masa lalu
            'gender'      => ['required', Rule::in(['male', 'female'])],
            'religion'    => ['required', 'string', 'max:100'],
            'photo'       => ['nullable', 'image', 'max:2048'],  // Foto opsional, maks 2MB
        ];
    }
}
```

### Service: `StudentBiodataService.php`

Service berisi seluruh logika bisnis. Berikut penjelasan setiap method:

**`canManageAll(User $user): bool`**

```php
public function canManageAll(User $user): bool
{
    return in_array($user->role, ['admin', 'staff', 'superadmin'], true);
}
```
Cek apakah user punya akses ke semua data (bukan cuma data miliknya sendiri).

**`paginateFor(User $user): LengthAwarePaginator`**

```php
public function paginateFor(User $user): LengthAwarePaginator
{
    $query = StudentBiodata::query()
        ->with(['registration.user'])  // Eager loading — cegah N+1 query
        ->latest();

    // Jika bukan admin, filter hanya biodata milik user ini
    if (!$this->canManageAll($user)) {
        $query->whereHas('registration', fn ($q) => $q->where('user_id', $user->id));
    }

    return $query->paginate(10);  // 10 item per halaman
}
```

**`create(User $user, array $data): StudentBiodata`**

```php
public function create(User $user, array $data): StudentBiodata
{
    $registration = $this->resolveRegistration($user, $data);

    // Cegah duplikasi — satu registrasi hanya boleh punya satu biodata
    abort_if($registration->biodata()->exists(), 422, 'Biodata untuk pendaftaran ini sudah ada.');

    $payload = $this->payload($data);  // Filter hanya kolom yang diizinkan
    $payload['registration_id'] = $registration->id;

    // Upload foto jika ada
    if (($data['photo'] ?? null) instanceof UploadedFile) {
        $payload['photo'] = $data['photo']->store('biodata/photos', 'public');
    }

    return StudentBiodata::create($payload);
}
```

**`authorize(User $user, StudentBiodata $biodata): void`**

```php
public function authorize(User $user, StudentBiodata $biodata): void
{
    if ($this->canManageAll($user)) {
        return;  // Admin boleh akses semua, langsung return
    }

    // Non-admin hanya boleh akses biodata miliknya sendiri
    abort_unless($biodata->registration()->where('user_id', $user->id)->exists(), 403);
}
```

### Controller: `StudentBiodataController.php`

```php
class StudentBiodataController extends Controller
{
    // Dependency Injection — Laravel otomatis membuat instance StudentBiodataService
    public function __construct(private readonly StudentBiodataService $service)
    {
    }

    // GET /biodata — Tampilkan daftar biodata
    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Biodata/Index', [
            'biodata'      => $this->service->paginateFor($request->user()),
            'canManageAll' => $this->service->canManageAll($request->user()),
            // Data ini menjadi "props" di Vue
        ]);
    }

    // POST /biodata — Simpan biodata baru
    public function store(StudentBiodataRequest $request): RedirectResponse
    {
        // $request->validated() hanya berisi data yang lolos validasi
        $studentBiodata = $this->service->create($request->user(), $request->validated());

        // Redirect ke halaman detail + flash message "status"
        return redirect()
            ->route('biodata.show', $studentBiodata)
            ->with('status', 'Biodata mahasiswa berhasil dibuat.');
    }

    // GET /biodata/{id} — Tampilkan detail
    public function show(Request $request, StudentBiodata $student_biodata): Response
    {
        $this->service->authorize($request->user(), $student_biodata);

        return Inertia::render('Modules/Biodata/Show', [
            // load() = eager load relasi setelah model sudah ada
            'studentBiodata' => $student_biodata->load('registration.user'),
        ]);
    }
}
```

> **Apa itu `Inertia::render()`?**
> Ini pengganti `return view()`. Argumen pertama adalah **path ke file Vue** relatif dari `resources/js/Pages/`. Argumen kedua adalah **array data** yang akan menjadi `props` di komponen Vue tersebut.

---

## 9. Module Registration

Module ini menangani pendaftaran mahasiswa baru dengan alur status yang ketat.

### Alur Status Pendaftaran

```
draft -> submitted -> verified -> accepted
               \              \
            (rejected)     (rejected)
```

| Status | Artinya | Siapa yang bisa ubah |
|--------|---------|---------------------|
| `draft` | Baru dibuat, belum lengkap | System (saat create) |
| `submitted` | Sudah disubmit oleh pendaftar | Pendaftar sendiri |
| `verified` | Sudah diverifikasi admin | Admin/Staff |
| `accepted` | Diterima (syarat: payment_status = paid) | Admin/Staff |
| `rejected` | Ditolak | Admin/Staff |

### Method Submit di Service

```php
public function submit(User $user, Registration $registration): Registration
{
    $this->authorize($user, $registration);  // Hanya pemilik yang bisa submit

    // Validasi: hanya bisa submit saat status masih draft
    abort_if($registration->status !== 'draft', 422, 'Hanya bisa submit saat draft.');

    // Validasi kelengkapan dokumen
    $this->assertComplete($registration);

    $registration->update([
        'status'       => 'submitted',
        'submitted_at' => now(),
    ]);

    return $registration->refresh();
}

// Cek kelengkapan sebelum submit
private function assertComplete(Registration $registration): void
{
    // Harus sudah ada biodata
    abort_unless($registration->biodata()->exists(), 422, 'Biodata belum diisi.');

    // Cek dokumen wajib: ijazah, ktp, photo
    $uploaded = $registration->documents()->pluck('type')->all();
    $missing  = array_diff(self::REQUIRED_DOCUMENTS, $uploaded);

    abort_if(!empty($missing), 422, 'Dokumen belum lengkap: ' . implode(', ', $missing));
}
```

### Model: `Registration.php` — Relasi

```php
class Registration extends Model
{
    // Dimiliki oleh satu User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Punya satu Biodata (one-to-one)
    public function biodata(): HasOne
    {
        return $this->hasOne(StudentBiodata::class);
    }

    // Punya banyak Dokumen (one-to-many)
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
```

### Nomor Registrasi Otomatis

```php
private function generateRegistrationNumber(): string
{
    do {
        // Format: PMB-2026-00123
        $number = 'PMB-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    } while (Registration::where('registration_number', $number)->exists());
    // Loop sampai dapat nomor yang belum dipakai (mencegah duplikasi)

    return $number;
}
```

---

## 10. Module Document

### Penjelasan Penyimpanan File

Dokumen disimpan di storage `local` (bukan `public`) agar **tidak bisa diakses langsung via URL browser**. User harus melalui route `/documents/{id}/download` yang memeriksa izin akses terlebih dahulu.

```php
public function download(Request $request, Document $document): StreamedResponse
{
    $this->service->authorize($request->user(), $document);

    // Cek file ada di storage
    abort_unless(Storage::disk('local')->exists($document->file_path), 404);

    // Stream file ke browser sebagai download
    return Storage::disk('local')->download($document->file_path, $document->original_name);
}
```

### Tipe Dokumen yang Tersedia

```php
public const TYPES = ['ijazah', 'ktp', 'photo', 'payment_proof', 'other'];
```

Dokumen wajib untuk submit pendaftaran: `ijazah`, `ktp`, `photo`.

---

## 11. Layout Atlantis Admin

**Lokasi:** `resources/js/Layouts/AdminLayout.vue`

Layout ini adalah "wrapper" yang membungkus semua halaman admin. Berisi sidebar, topbar, dan `<slot />` untuk konten halaman.

### Cara Pakai di Halaman Vue

```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
    <AdminLayout>
        <!-- Konten halaman diletakkan di sini -->
        <!-- Ini akan menggantikan <slot /> di AdminLayout -->
        <div class="py-8">
            <h1>Halaman Saya</h1>
        </div>
    </AdminLayout>
</template>
```

### Fitur Sidebar Responsive

```js
const sidebarOpen = ref(false);       // Untuk mobile: sidebar terbuka/tertutup
const sidebarCollapsed = ref(false);  // Untuk desktop: sidebar mengecil/normal

const toggleSidebar = () => {
    // Di mobile (< 980px): toggle open/close
    if (window.matchMedia('(max-width: 980px)').matches) {
        sidebarOpen.value = !sidebarOpen.value;
        return;
    }
    // Di desktop: toggle collapsed (icon only) / full
    sidebarCollapsed.value = !sidebarCollapsed.value;
};
```

**State CSS yang dihasilkan:**

| State | Class di `.atlantis` | Efek Visual |
|-------|---------------------|-------------|
| Normal desktop | (kosong) | Sidebar 258px lebar penuh |
| Collapsed desktop | `sidebar-collapsed` | Sidebar 78px, hanya ikon |
| Mobile tertutup | (kosong) | Sidebar tersembunyi (translateX -100%) |
| Mobile terbuka | `sidebar-open` | Sidebar muncul dari kiri |

### Data dari `usePage()` di Layout

```js
const page = usePage();

// Ambil user dari shared props yang di-set di HandleInertiaRequests
const user = computed(() => page.props.auth?.user);
const displayName = computed(() => user.value?.name || 'Guest');
const displayRole = computed(() => {
    const role = user.value?.role || 'User';
    return role.charAt(0).toUpperCase() + role.slice(1);  // Capitalize huruf pertama
});
```

### Menu Navigasi

Menu didefinisikan sebagai array objek, lalu di-render dengan `v-for`:

```js
const menuItems = [
    { label: 'Dashboard', routeName: 'dashboard', current: 'dashboard', icon: 'dashboard' },
    { label: 'Pendaftaran', routeName: 'registrations.index', current: 'registrations.*', icon: 'list' },
    { label: 'Biodata', routeName: 'biodata.index', current: 'biodata.*', icon: 'user' },
    { label: 'Dokumen', routeName: 'documents.index', current: 'documents.*', icon: 'document' },
];
```

```html
<li v-for="item in menuItems" :key="item.routeName">
    <Link
        class="nav-link"
        :class="{ active: route().current(item.current) }"
        :href="route(item.routeName)"
    >
        {{ item.label }}
    </Link>
</li>
```

> **`route().current('biodata.*')`** mengembalikan `true` jika URL saat ini cocok dengan pattern `biodata.*` (misalnya `biodata.index`, `biodata.show`, dll). Ini membuat menu aktif otomatis highlight.

### Logout dengan Method POST

```html
<!-- Link logout harus POST karena Laravel menggunakan CSRF protection -->
<Link
    :href="route('logout')"
    method="post"
    as="button"
>
    Logout
</Link>
```

> Inertia menangani ini dengan otomatis membuat form POST di balik layar, termasuk menyertakan CSRF token.

---

## 12. Komponen SweetAlert

**Lokasi:** `resources/js/Components/SweetAlert.vue`

Komponen notifikasi popup yang terinspirasi SweetAlert2, dibuat dari scratch tanpa dependensi eksternal.

### Cara Kerja

1. Komponen dipasang **sekali** di `AdminLayout.vue` — bukan di setiap halaman
2. Menggunakan `usePage()` untuk **mendengarkan perubahan** `flash` props
3. Saat ada flash message baru, popup otomatis muncul
4. Menggunakan `<Teleport to="body">` agar popup selalu tampil di atas semua elemen

```js
const flashAlert = computed(() => {
    const flash = page.props.flash || {};

    if (flash.success || flash.status) {
        return { type: 'success', title: 'Berhasil', message: flash.success || flash.status };
    }
    if (flash.error) {
        return { type: 'error', title: 'Gagal', message: flash.error };
    }
    // Juga menangkap validation errors otomatis
    const firstError = Object.values(page.props.errors || {})[0];
    if (firstError) {
        return { type: 'error', title: 'Validasi Gagal', message: firstError };
    }
    return null;
});

// Watch perubahan computed -> tampilkan popup
watch(flashAlert, (value) => {
    if (!value) return;
    alert.value = value;
    visible.value = true;
}, { immediate: true });
```

### Bagaimana Flash Message Mengalir

```
Controller PHP                    HandleInertiaRequests          SweetAlert.vue
     |                                    |                            |
     | ->with('success', 'Berhasil!')     |                            |
     +----------------------------------> |                            |
                                          | share({ flash: {...} })    |
                                          +--------------------------> |
                                                                       |
                                                     page.props.flash.success berubah
                                                     flashAlert computed trigger
                                                     watch() -> visible = true
                                                     Popup muncul!
```

### Tipe Alert dan Warnanya

| Tipe | Warna | Trigger dari PHP |
|------|-------|-----------------|
| `success` | Hijau (#31ce36) | `->with('success', ...)` atau `->with('status', ...)` |
| `info` | Biru (#1572e8) | `->with('info', ...)` |
| `warning` | Oranye (#ff9e27) | `->with('warning', ...)` |
| `error` | Merah (#f25961) | `->with('error', ...)` atau validation error |

---

## 13. Alur Data Lengkap

Contoh alur lengkap saat user membuka halaman daftar biodata (`/biodata`):

```
1. USER klik link "Biodata" di sidebar
   |
2. INERTIA intersep klik (karena pakai <Link>, bukan <a>)
   Kirim AJAX request: GET /biodata
   dengan header: X-Inertia: true
   |
3. LARAVEL routes/web.php
   Route::resource('biodata', StudentBiodataController::class)
   -> Cocok dengan GET /biodata -> panggil biodata.index
   |
4. MIDDLEWARE dieksekusi berurutan:
   a. web middleware (session, cookies, CSRF)
   b. auth middleware (cek sudah login)
   c. HandleInertiaRequests (share auth + flash ke semua page)
   |
5. CONTROLLER StudentBiodataController::index()
   - Panggil $this->service->paginateFor($user)
   - Panggil $this->service->canManageAll($user)
   - Return Inertia::render('Modules/Biodata/Index', [
         'biodata' => [paginated data],
         'canManageAll' => false/true,
     ])
   |
6. INERTIA SERVER membuat response JSON:
   {
     "component": "Modules/Biodata/Index",
     "props": {
       "biodata": { "data": [...], "links": [...] },
       "canManageAll": false,
       "auth": { "user": {...} },       <- dari HandleInertiaRequests
       "flash": { "success": null }     <- dari HandleInertiaRequests
     },
     "url": "/biodata"
   }
   |
7. INERTIA CLIENT (browser) terima JSON
   - Cari file Vue: Pages/Modules/Biodata/Index.vue
   - Swap komponen tanpa reload halaman
   |
8. VUE render Index.vue
   - Props 'biodata' otomatis tersedia via defineProps()
   - Layout AdminLayout.vue membungkus konten
   - SweetAlert.vue membaca flash (kosong -> tidak tampil)
   |
9. USER melihat tabel biodata di halaman
```

---

## Ringkasan Arsitektur

```
resources/
├── views/
│   └── app.blade.php          <- Satu-satunya Blade, berisi @inertia
│
└── js/
    ├── app.js                 <- Entry point, setup Inertia + Vue
    ├── Layouts/
    │   ├── AdminLayout.vue    <- Layout Atlantis (sidebar + topbar)
    │   ├── AuthenticatedLayout.vue
    │   └── GuestLayout.vue
    ├── Components/
    │   └── SweetAlert.vue     <- Notifikasi popup global
    └── Pages/                 <- Satu file per halaman
        ├── Auth/              <- Login, Register, dll
        ├── Admin/             <- Dashboard admin
        ├── Profile/           <- Halaman profil
        └── Modules/
            ├── Biodata/       <- Index, Create, Edit, Show, Form
            ├── Registration/  <- Index, Create, Show
            └── Document/      <- Index, Create, Show

app/
├── Http/
│   ├── Controllers/
│   │   └── DashboardController.php
│   └── Middleware/
│       ├── HandleInertiaRequests.php  <- Share data global ke Vue
│       └── RoleMiddleware.php         <- Otorisasi berbasis role
│
├── Models/
│   └── User.php               <- Model user dengan field role + is_active
│
└── Modules/
    ├── Biodata/               <- Controller, Model, Request, Service
    ├── Registration/          <- Controller, Model, Request, Service
    └── Document/              <- Controller, Model, Request, Service
```
