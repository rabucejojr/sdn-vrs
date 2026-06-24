# SDN Vehicle Reservation System

**SDN-VRS** is a centralized vehicle reservation and trip ticket management system for the **PSTO Surigao del Norte** office. It replaces fragmented paper-based and chat-based reservation coordination with a structured, role-based web application.

---

## Overview

The system manages the office's single service vehicle — **Isuzu Crosswind (Plate: SJJ 504)** — and enforces a clear workflow from reservation filing through approval and completion. It generates official trip ticket documents compatible with DOST's standard format.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Vue 3 (Composition API, `<script setup>`) |
| SPA Bridge | Inertia.js |
| Styling | Tailwind CSS v3 |
| Icons | lucide-vue-next |
| Database | MySQL |
| Auth Scaffold | Laravel Breeze |
| PDF Export | barryvdh/laravel-dompdf |
| Composables | @vueuse/core |

---

## Features

### Reservation Management
- File vehicle reservations with purpose, destination, travel date range, departure/return times, and passenger list
- Single-day and multi-day trip support with conflict detection
- Real-time conflict alert when requested dates overlap an existing approved reservation (warning only, filing is not blocked)
- Edit or cancel own pending reservations
- View full reservation history with status and date range filters

### Trip Ticket Workflow
- **Status flow:** `pending` → `approved` / `disapproved` → `completed`; any status can be `cancelled`
- Auto-generated ticket numbers in the format `Crosswind-YYYY-MM-XXXX` (zero-padded sequence, resets monthly)
- Admin approve/disapprove with optional remarks
- Mark approved trips as completed

### Trip Ticket Documents
- Printable Blade view styled to trip ticket format
- One-click PDF download via dompdf
- Print and Download PDF buttons on the reservation detail page

### Dashboard
- Stat cards: pending reservations, approvals this month, completions this month
- Monthly calendar view with booked dates highlighted
- Upcoming trips list

### Reporting & Export
- Excel export of reservation records for admins

### User Management (Admin)
- Create, view, edit, and deactivate/reactivate user accounts
- Role assignment: `admin` or `staff`
- New users receive an account setup email with a password-set link — no admin-set passwords
- Password reset marks the account as email-verified simultaneously (one email, one action)
- Deactivated users are immediately blocked from accessing the system

### Email Verification & Password Reset
- Full email verification enforced on all protected routes
- Forgot password flow via emailed reset link (60-minute token expiry)
- Gmail SMTP delivery (configurable via `.env`)

### In-App Notifications
- Toast notifications for: new reservation filed, reservation status change, account created, role changed, account deactivated, email verified, password changed
- Notification bell with unread count and full notification list page
- Mark individual or all notifications as read

### Audit Log
- Status change history on every reservation detail page, showing who acted and when

### Vehicle Registry
- Admin-managed vehicle list (`/admin/vehicles`)
- Active vehicle auto-assigned to new reservations — never input by users

---

## Roles & Permissions

| Action | Staff | Admin |
|---|---|---|
| File a reservation | ✅ | ✅ |
| Edit / cancel own pending reservation | ✅ | ✅ |
| View own reservations | ✅ | ✅ |
| View all reservations | ❌ | ✅ |
| Approve / Disapprove / Complete | ❌ | ✅ |
| Export reservations to Excel | ❌ | ✅ |
| Manage users | ❌ | ✅ |
| Manage vehicles | ❌ | ✅ |

---

## Local Development Setup

### Requirements
- PHP 8.3
- Composer
- Node.js 20+
- MySQL (Laragon recommended on Windows)

### Installation

```bash
git clone <repo-url> sdn-vrs
cd sdn-vrs

composer install
npm install

cp .env.example .env
php artisan key:generate
```

### Database

```bash
php artisan migrate
php artisan db:seed
```

The seeder creates two accounts:

| Email | Password | Role |
|---|---|---|
| admin@psto-sdn.dost.gov.ph | password | Admin |
| staff@psto-sdn.dost.gov.ph | password | Staff |

### Mail (Gmail SMTP)

Edit `.env`:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD="your-16-char-app-password"
MAIL_FROM_ADDRESS="your-gmail@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Generate an App Password at: Google Account → Security → 2-Step Verification → App passwords.

### Build & Run

```bash
npm run dev      # Vite dev server (hot reload)
php artisan serve
```

Or with Laragon, simply start Apache/Nginx and visit `http://sdn-vrs.test`.

---

## Ticket Number Format

```
Crosswind-{YYYY}-{MM}-{SEQUENCE}
```

Example: `Crosswind-2025-07-0001` — first reservation filed in July 2025. Sequence resets each month per vehicle.

---

## Developer

**Roger Jr. H. Abucejo**
PSTO Surigao del Norte
