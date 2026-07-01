# PMB + CBT System Architecture — Laravel 12

---

## 1. High-Level System Architecture

### Modular Architecture

The system uses a **domain-driven modular monolith** — a single Laravel app, but with code organized into self-contained modules under `app/Modules/`. Each module owns its controllers, services, models, requests, and views. Modules communicate only through defined interfaces (service classes or events), never by reaching into each other's internals.

This means you get the simplicity of a monolith now and can extract any module into a microservice later without touching the others.

```
Browser/Admin → Routes → Controller → Service → Repository (optional) → Model → DB
                                  ↓
                            Events / Contracts
                                  ↓
                     Notification / Payment / Log (future)
```

### Communication Flow

- **Intra-module**: Controller calls its own Service. Service uses its own Model/Repository.
- **Cross-module**: Module A fires a Laravel Event. Module B has a Listener registered in its own `EventServiceProvider`. No direct class coupling.
- **Future async**: Replace synchronous Listeners with queued Jobs — zero change to the firing module.

### System Scope

| Component | Deployment | Network | Notes |
|---|---|---|---|
| **PMB (Admission)** | VPS, internet-facing | Public internet | Registration, biodata, document upload, announcements, manual payment verification |
| **CBT (Exam)** | Local campus server | LAN / campus WiFi only | Exam sessions run locally — no internet dependency during exams |

Both components run inside the same Laravel application and share the same database. The CBT is intentionally local-first: stability and low latency take priority over remote accessibility. Internet access is only needed for the PMB side.

### Scalability Strategy

| Layer | MVP | Future |
|---|---|---|
| Web | Single VPS (PMB) + local server (CBT) | Load balancer + multiple app nodes |
| DB | Single MySQL | Read replicas, connection pooling |
| Jobs | `sync` driver | Redis + Horizon |
| Files | Local disk | S3-compatible (Minio/AWS) |
| Cache | file driver | Redis |
| Sessions | file/cookie | Redis or DB |

---

## 2. Recommended Folder Structure

```
app/
├── Modules/
│   ├── Auth/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Requests/
│   │   └── AuthServiceProvider.php
│   ├── Registration/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Models/
│   │   ├── Repositories/
│   │   ├── Requests/
│   │   └── RegistrationServiceProvider.php
│   ├── Biodata/
│   ├── Document/
│   ├── Exam/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   │   ├── ExamSessionService.php
│   │   │   ├── ScoringService.php
│   │   │   └── TimerService.php
│   │   ├── Models/
│   │   └── Jobs/
│   ├── QuestionBank/
│   ├── Result/
│   ├── Announcement/
│   ├── Admin/
│   ├── Report/
│   ├── Setting/
│   └── _Shared/
│       ├── Contracts/         ← interfaces consumed across modules
│       ├── Events/
│       ├── Listeners/
│       └── Traits/
│
├── Providers/
│   └── ModuleServiceProvider.php   ← loads all module providers
│
config/
├── modules.php
│
resources/
├── views/
│   ├── modules/
│   │   ├── auth/
│   │   ├── registration/
│   │   ├── exam/
│   │   └── admin/
│   └── layouts/
│       ├── app.blade.php
│       └── admin.blade.php
│
routes/
├── web.php           ← loads module route files
├── modules/
│   ├── auth.php
│   ├── registration.php
│   ├── exam.php
│   └── admin.php
│
database/
├── migrations/
│   ├── auth/
│   ├── registration/
│   ├── exam/
│   └── _future/      ← stub migrations for payment, log, etc.
```

---

## 3. Database Schema Design

### Core Tables

**users**
```
id, name, email, password, role (enum: superadmin|admin|staff|student),
email_verified_at, phone (nullable), avatar (nullable),
is_active (bool, default true), last_login_at (nullable),
timestamps, soft_deletes
```

**registrations**
```
id, user_id (FK), academic_year_id (FK), registration_number (unique),
status (enum: draft|submitted|verified|accepted|rejected),
registration_wave_id (nullable FK),
program_id (FK),
payment_status (enum: unpaid|pending|paid, nullable),
payment_ref (nullable),
submitted_at (nullable), verified_at (nullable), timestamps
```

**student_biodata**
```
id, registration_id (FK), nik, birth_place, birth_date,
gender (enum), religion, address, province, city,
school_name, school_graduation_year,
parent_name, parent_phone, parent_job,
photo (nullable), timestamps
```

**documents**
```
id, registration_id (FK), type (enum: ijazah|ktp|photo|skhun|payment_proof|etc),
file_path, original_name, mime_type, size_kb,
status (enum: pending|approved|rejected), notes (nullable),
reviewed_by (FK users, nullable), reviewed_at (nullable), timestamps
```

**programs**
```
id, code, name, quota, description (nullable), is_active, timestamps
```

**academic_years**
```
id, label (e.g. 2025/2026), is_active,
registration_open_at, registration_close_at,
timestamps
```
> `registration_open_at/close_at` adalah rentang keseluruhan tahun ajaran.
> Jadwal per gelombang dikelola di tabel `registration_waves`.

**registration_waves**
```
id, academic_year_id (FK),
wave_number (tinyint),              ← urutan: 1, 2, 3, …
label (e.g. "Gelombang 1"),
open_at (datetime),                 ← pendaftaran gelombang dibuka
close_at (datetime),                ← pendaftaran gelombang ditutup
quota (nullable int),               ← batas kuota gelombang (null = ikuti kuota prodi)
is_active (bool, default true),
description (nullable),
timestamps
```
> Admin dapat menambah gelombang baru kapan saja dalam satu tahun ajaran.
> Satu tahun ajaran bisa memiliki banyak gelombang (Gel. 1, Gel. 2, Gel. 3, dst.).

---

**question_banks**
```
id, category_id (FK), created_by (FK users),
type (enum: multiple_choice|true_false),
question_text (longtext), explanation (nullable),
difficulty (enum: easy|medium|hard), is_active, timestamps
```

**question_options**
```
id, question_id (FK), option_text, is_correct (bool), order_index
```

**question_categories**
```
id, name, description (nullable), timestamps
```

**exams**
```
id, title, academic_year_id (FK), program_id (nullable FK),
duration_minutes, pass_score (int),
randomize_questions (bool), randomize_options (bool),
max_attempts (tinyint, default 1),
start_at (datetime), end_at (datetime),
status (enum: draft|published|closed), timestamps
```

**exam_questions** (pivot)
```
id, exam_id (FK), question_id (FK), order_index, points
```

**exam_sessions**
```
id, exam_id (FK), registration_id (FK), token (unique),
status (enum: pending|ongoing|submitted|timed_out),
started_at (nullable), submitted_at (nullable), expires_at,
ip_address, user_agent (nullable), timestamps
```

**exam_answers**
```
id, session_id (FK), question_id (FK), selected_option_id (nullable FK),
is_flagged (bool, default false), answered_at (nullable)
```

**exam_results**
```
id, session_id (FK, unique), registration_id (FK),
raw_score, final_score, is_passed (bool),
rank (nullable), published_at (nullable), timestamps
```

---

**announcements**
```
id, title, body (longtext), type (enum: general|important|exam),
target_role (nullable enum), published_at (nullable),
created_by (FK users), is_active, timestamps
```

**settings**
```
id, key (unique), value (text), group, description (nullable), timestamps
```

---

**payment_verifications** ← active, manual flow (no gateway)
```
id, registration_id (FK, unique),
bank_name, account_name, transfer_amount,
transfer_date (date),
proof_document_id (FK documents, nullable),
status (enum: pending|verified|rejected),
notes (nullable),
verified_by (FK users, nullable), verified_at (nullable), timestamps
```

Student uploads proof of transfer as a `document` with type `payment_proof`. Admin reviews, then records the verification result here. On verification, the linked `registration.payment_status` is updated to `paid`.

---

### Future-Ready Tables (stub migrations, no active code)

**payments** ← stub for future gateway integration
```
id, registration_id (FK), amount, currency (default IDR),
gateway (enum: midtrans|xendit),
gateway_ref (nullable), gateway_status (nullable),
status (enum: pending|paid|failed|expired),
paid_at (nullable), metadata (json, nullable), timestamps
```

**notifications_log**
```
id, user_id (FK), channel (enum: email|whatsapp|database),
event_type, payload (json), status (enum: pending|sent|failed),
sent_at (nullable), timestamps
```

**activity_logs**
```
id, user_id (nullable FK), action, module, subject_type, subject_id,
old_values (json, nullable), new_values (json, nullable),
ip_address, user_agent (nullable), timestamps
```

---

### Key Relationships

```
User 1──1 Registration 1──1 Biodata
AcademicYear 1──* RegistrationWaves
RegistrationWave 1──* Registrations
Registration 1──* Documents (includes type=payment_proof)
Registration 1──1 PaymentVerification
Registration 1──* ExamSessions
ExamSession 1──* ExamAnswers
ExamSession 1──1 ExamResult
Exam *──* Questions (via exam_questions)
Question 1──* QuestionOptions
```

---

## 4. Module Breakdown

| Module | Responsibility |
|---|---|
| **Auth** | Login, logout, password reset via Breeze. Role assignment. Token guard for future API. |
| **Registration** | Create/update registration record. Gelombang CRUD (admin). Assign pendaftar ke gelombang aktif. Status transitions. |
| **Biodata** | CRUD for student personal data. Linked 1-to-1 with registration. |
| **Document** | Upload, store, review (approve/reject) supporting files including payment proof. Virus/type validation. |
| **Payment** | Manual payment verification flow: student uploads proof → admin reviews `payment_verifications` → marks registration paid. No gateway in MVP. |
| **Exam** | Exam CRUD, scheduling, assigning questions. Session start/submit logic. Server-side timer enforcement. Intended for local campus LAN. |
| **QuestionBank** | Question CRUD by category, difficulty, type. Import from CSV (future). |
| **Result** | Score calculation, pass/fail determination, result publishing, per-student result view. |
| **Announcement** | Publish announcements to specific roles or all users. |
| **Admin** | Dashboard aggregates, student list, status management, document/payment review interface. |
| **Report** | Export registration data, exam results to Excel/PDF. Charts for dashboard. |
| **Setting** | Key-value system config (registration period dates, quota, bank account details, etc.) via DB. |

---

## 5. Authentication & Authorization Design

### Roles

```
superadmin  → full system access
admin       → manage registrations, exams, announcements
staff       → document review, limited reports
student     → own registration, biodata, exam, result
```

### Strategy

- Roles stored as enum on `users.role` column (simple for MVP).
- Future: migrate to `spatie/laravel-permission` for fine-grained permissions without changing service logic.
- Each module has a middleware group check: `role:admin,superadmin`.

### Middleware Stack

```
web
└── auth
    └── verified (email verification, optional)
        └── role:admin      ← admin routes
        └── role:student    ← student routes
        └── exam.session    ← custom: validates active exam session token
```

### Permission Strategy (MVP → Future)

MVP: Simple `role` checks in middleware and `@can` / `Gate` definitions in `AuthServiceProvider`.

Future: Drop-in `spatie/laravel-permission` — the service layer calls `$user->can('action')` which works identically whether backed by simple gates or Spatie's DB-driven permissions.

---

## 6. CBT Flow Design

### Deployment Context

The CBT runs on a **local campus server** accessed over LAN or campus WiFi. Students do not need internet access during an exam. This design prioritises:
- **Stability** — no dependency on external connectivity
- **Low latency** — all traffic stays on the local network
- **Auto-save** — every answer change is persisted immediately server-side via AJAX
- **Server-side timer** — exam time is authoritative on the server regardless of client state

### Session Lifecycle

```
Student logs in (local network)
    → Checks: registration verified + payment verified, exam open, within window, no prior completed session
    → ExamSession created (status: pending, expires_at = now + duration)
    → Questions loaded (shuffled if exam.randomize_questions = true)
    → Options shuffled per-question if exam.randomize_options = true
    → Frontend receives session token + question list + expires_at timestamp
        → Timer counts down client-side from (expires_at - now)
        → Each answer change triggers AJAX to /exam/answer → saved immediately (auto-save)
        → Server rejects answer saves if now() >= expires_at
    → On submit OR timer expiry:
        → POST /exam/submit with session token
        → Server validates expires_at (server-authoritative, not client)
        → Status set to: submitted (manual) or timed_out (server-detected)
        → ScoringService::calculate(session) runs synchronously (async via job later)
        → ExamResult record created
```

### Timer Mechanism

- `expires_at` is set **server-side** at session creation.
- Client timer is display-only. On every answer save, server checks `now() < expires_at`.
- A scheduled command (`exam:close-expired-sessions`) runs every minute via Laravel Scheduler to auto-submit timed-out sessions that were abandoned (no final submit request received).

### Scoring

```
ScoringService:
  - fetch all exam_answers for session
  - for each answer: compare selected_option_id against is_correct option
  - raw_score = sum(points) for correct answers
  - final_score = (raw_score / max_possible) * 100
  - is_passed = final_score >= exam.pass_score
```

### Result Publishing

- Results are NOT visible until `admin` sets `exam_results.published_at` (or batch-publishes).
- Students see "Waiting for result" until published.
- On publish: fire `ResultPublished` event → (future) Notification listener sends email/WA.

### Anti-Cheat (Basic)

- Store `ip_address` and `user_agent` on session start.
- One active session per registration per exam (enforced server-side).
- `is_flagged` field on `exam_answers` for client-side tab-blur detection (JS sends flag, server stores, admin reviews).
- Session token is single-use and tied to registration.

---

## 7. Future Upgrade Strategy

### Upgrading Payment to Gateway (from manual verification)

1. Activate stub `payments` migration (midtrans/xendit fields).
2. Create `GatewayPaymentService` implementing `Contracts\PaymentContract`.
3. The existing `RegistrationPaid` event is already fired by the manual verification flow — gateway replaces the trigger, not the downstream logic.
4. Manual verification (`payment_verifications` table) can remain as a fallback channel.
5. Zero changes to Registration module internals.

### Adding Email / WhatsApp Notifications

1. Create `NotificationService` with channels.
2. Register listeners in `_Shared/Listeners/` for events already fired by core modules:
   - `RegistrationSubmitted` → send confirmation email
   - `ResultPublished` → send WhatsApp via gateway
3. Core modules need no changes — they already fire the events.

### Adding Queue Processing

1. Change `QUEUE_CONNECTION=redis` in `.env`.
2. Make existing Listeners implement `ShouldQueue`.
3. Scoring and notification dispatch automatically become async.
4. No business logic changes.

### Making CBT Fully Online

1. Move the local campus server to a public VPS (or expose it via reverse proxy).
2. Add SSL (Certbot).
3. Tighten rate limiting and `Content-Security-Policy` for public exposure.
4. No application code changes needed — the CBT module is already server-authoritative.

### Adding REST API / Mobile

1. Create `routes/api.php` module files.
2. API controllers in `app/Modules/*/Controllers/Api/` call the **same service classes** as web controllers.
3. Services are transport-agnostic — they don't know if called from web or API.
4. Add Laravel Sanctum for token auth.

---

## 8. Deployment Strategy

### Local Development

```
PHP 8.3, Composer, Laravel Valet or Herd, MySQL 8, Node (for Vite)
.env: QUEUE_CONNECTION=sync, MAIL_MAILER=log, FILESYSTEM_DISK=local
```

### VPS Deployment — PMB (Online, internet-facing)

```
Ubuntu 22.04
Nginx + PHP 8.3-FPM
MySQL 8 (same server or separate)
Certbot (SSL)
Supervisor (queue workers, if enabled)
Laravel Scheduler via crontab
Storage symlink for uploads
```

Deployment flow: `git pull → composer install --no-dev → php artisan migrate → php artisan config:cache → php artisan route:cache`

### Local Campus Server Deployment — CBT (LAN only)

```
Ubuntu 22.04 (or Windows Server + Laragon as fallback)
Nginx + PHP 8.3-FPM (or Apache)
MySQL 8
No SSL required (internal network)
Laravel Scheduler via crontab (exam:close-expired-sessions every minute)
Storage symlink for uploads
Static IP on campus LAN, accessible via http://[local-ip] or hostname
```

CBT-specific notes:
- No public internet exposure required — bind Nginx to LAN interface only.
- Students access via campus WiFi or wired LAN.
- If the same server runs both PMB and CBT, use separate Nginx server blocks (or subdomain). CBT block can be restricted to LAN IP range via `allow`/`deny`.
- Internet connectivity on the server is needed only for initial setup/updates, not during exams.
- A UPS on the server is strongly recommended for exam stability.

### Scaling Strategy (when needed)

```
Phase 1: Vertical scale (bigger VPS)
Phase 2: Separate DB server + read replica
Phase 3: Add Redis (cache + sessions + queues)
Phase 4: Multiple app nodes behind load balancer (Nginx/HAProxy)
         → Sessions must be on Redis or DB (not file) at this point
Phase 5: Move file uploads to S3 (change FILESYSTEM_DISK=s3, no code change)
Phase 6: Kubernetes / Docker Swarm (if needed)
```

### Database Backup Strategy

```
MVP:    mysqldump via cron, daily, upload to remote (rclone to Google Drive/S3)
Future: Automated point-in-time recovery with MySQL binlog
        Retention: 7 daily + 4 weekly + 3 monthly
```

---

## 9. Security Considerations

### Validation
- All input validated via FormRequest classes in each module.
- Enum values validated strictly — no free-text for status fields.
- Exam answer submissions validate session ownership (session token must belong to authed user).

### Upload Security
- Allowed MIME types whitelisted per document type (not relying on extension only).
- Files stored outside `public/` — served via signed temporary URLs.
- Max file size enforced at PHP and Nginx level.
- Filename stored separately from storage path (original name never used as storage key).

### Session Security
- `SESSION_SECURE_COOKIE=true` in production.
- `SESSION_SAME_SITE=strict`.
- Session regeneration on login (Breeze default).
- Exam session token is separate from auth session — UUID, single-use, server-validated.

### Anti-Cheat (Basic)
- Server-authoritative timer (`expires_at` column).
- One active exam session per student enforced in DB (unique constraint on `exam_id + registration_id` where `status = ongoing`).
- Tab/window blur detection via JavaScript sets `is_flagged` on affected answers — admin can audit.
- IP address logged per session.

### Rate Limiting
- Login route: `throttle:5,1` (5 attempts per minute).
- Exam answer save route: `throttle:60,1` (prevents flood).
- Document upload: `throttle:10,1`.

### Other
- CSRF on all forms (Laravel default).
- `Content-Security-Policy` header via middleware (restrictive for exam pages).
- Authorization checks in service layer, not only in controller/middleware — defense in depth.

---

## 10. Recommended Laravel Packages

| Package | Purpose |
|---|---|
| `laravel/breeze` | Auth scaffolding (login, register, password reset) |
| `spatie/laravel-medialibrary` | Document/file management with conversions |
| `maatwebsite/laravel-excel` | Export reports to Excel |
| `barryvdh/laravel-dompdf` | Export reports to PDF |
| `spatie/laravel-permission` | (add when multi-permission needed — not MVP) |
| `spatie/laravel-activitylog` | (add when activity log module activated) |
| `laravel/horizon` | (add when Redis queue enabled) |
| `laravel/sanctum` | (add when API module activated) |

**Avoid for MVP**: Telescope in production, Debugbar in production, premature API versioning packages, any package that duplicates Laravel built-ins.

---

## Architecture Summary

```
┌─────────────────────────────────────────────────────┐
│                   Browser / Client                   │
└──────────────────────┬──────────────────────────────┘
                       │ HTTP
┌──────────────────────▼──────────────────────────────┐
│         Laravel 12 Modular Monolith                  │
│  routes/ → Controllers → Services → Models → MySQL  │
│              ↓ Events                                │
│         Listeners (sync now, async later)            │
│              ↓                                       │
│  [Notification] [Payment] [Log] ← future modules    │
└─────────────────────────────────────────────────────┘
         │ files                    │ cache/queue
   Local Disk → S3 (future)     Redis (future)
```

### Key Principles
- **Thin controllers, fat services** — business logic never in controllers.
- **Event-driven cross-module communication** — decouples modules from each other.
- **Server-authoritative state** — timer, session, scoring never trusted from client.
- **Auto-save on every answer change** — exam answers persisted immediately, not only on submit.
- **Manual payment first, gateway later** — `payment_verifications` table handles manual bank transfer verification; the gateway `payments` table is a stub ready to activate without touching registration logic.
- **Dual deployment model** — PMB lives on an internet-facing VPS; CBT runs on a local campus server over LAN. Same codebase, different Nginx configs.
- **Nullable future columns in core tables** — notification fields already stubbed; gateway payment fields isolated in a separate stub table.
- **Consistent naming** — every module follows the same internal structure, so any developer (or AI assistant) can navigate any module by convention.
