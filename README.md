Selesai. Modul CBT Student Attempt / Sesi Pengerjaan CBT Peserta sudah dibuat.

File Dibuat

app/Modules/CbtAttempt/Models/: CbtAttempt.php, CbtAnswer.php, CbtResult.php
app/Modules/CbtAttempt/Services/: CbtAttemptService.php, CbtScoringService.php
app/Modules/CbtAttempt/Controllers/Student/CbtAttemptController.php
app/Modules/CbtAttempt/Requests/: autosave, flag, submit, heartbeat request
app/Modules/CbtAttempt/routes.php
resources/js/Pages/Student/Cbt/Exam.vue
tests/Feature/CbtAttempt/CbtAttemptFlowTest.php
Migration Dibuat

2026_05_03_023000_create_cbt_attempts_table.php
2026_05_03_023100_create_cbt_answers_table.php
2026_05_03_023200_create_cbt_results_table.php
File Diubah

routes/web.php
routes/console.php
resources/js/Pages/Student/ExamSchedule/Show.vue
resources/js/Pages/Student/ExamCard/Index.vue
app/Models/User.php
Registration.php, ExamSchedule.php, CbtExam.php
Fitur Yang Masuk

Student klik Mulai CBT dari Jadwal/Kartu Ujian.
Validasi: pendaftaran verified, payment paid, jadwal active, waktu sesuai jadwal, paket CBT published, peserta sudah ditempatkan, attempt belum melebihi max.
Attempt memakai uuid, token, expires_at, IP, user agent.
Timer server-authoritative.
Urutan soal dan opsi disimpan di attempt agar random tetap konsisten saat refresh.
Autosave jawaban dengan debounce 500ms.
Flag/ragu soal.
Deteksi pindah tab via visibilitychange/blur.
Submit manual.
Timeout otomatis via command cbt:close-expired-attempts.
Scoring otomatis ke cbt_results.
Hasil tersimpan, belum dipublish ke student.
Command

php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan cbt:close-expired-attempts
npm run build
Scheduler sudah ditambahkan di routes/console.php:

php artisan schedule:work
Verifikasi

npm run build sukses.
php artisan route:list --name=student.cbt sukses.
php artisan cbt:close-expired-attempts sukses.
php artisan test --filter=CbtAttemptFlowTest sukses: 5 tests passed.
Catatan penting: phpunit.xml project ini memakai MySQL db_pmb_cbt, jadi test RefreshDatabase melakukan migrasi ulang pada DB itu. Saya sudah menjalankan php artisan db:seed setelah test.

Cara Test Manual Student

Admin buat QuestionBank.
Admin buat Paket CBT, tambah soal, lalu publish.
Admin buat Jadwal Ujian status active dan pilih Paket CBT.
Admin tempatkan peserta ke ruang ujian.
Login student yang pendaftarannya verified dan payment_status = paid.
Buka Jadwal Ujian atau Kartu Ujian, klik Mulai CBT.
Pilih jawaban, lihat autosave, tandai ragu, submit