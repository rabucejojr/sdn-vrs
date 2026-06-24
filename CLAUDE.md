# CLAUDE.md — SDN Vehicle Reservation System (sdn-vrs)

## Project Overview

**System Name:** SDN Vehicle Reservation System (sdn-vrs)
**Agency:** DOST-PSTO Surigao del Norte (DOST-CARAGA)
**Purpose:** Centralized vehicle reservation and trip ticket management for the office's single service vehicle — Crosswind SJJ504 (Plate: SJJ 504). Replaces fragmented paper-based and chat-based reservation coordination.
**Developer:** Roger Jr. H. Abucejo

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 13 |
| Frontend Framework | Vue 3 (Composition API, `<script setup>`) |
| SPA Bridge | Inertia.js |
| Styling | Tailwind CSS v3 (Tailwind UI components) |
| Icons | lucide-vue-next |
| Database | MySQL |
| Auth Scaffold | Laravel Breeze (Inertia + Vue preset) |
| PDF Export | barryvdh/laravel-dompdf |
| Excel Export | maatwebsite/excel |
| Composables | @vueuse/core |

---

## Vehicle Registry

| Plate Number | Make/Model | Assigned Name in System |
|---|---|---|
| SJJ 504 | Toyota Crosswind | Crosswind |

> Vehicles are stored in the `vehicles` table and managed via `/admin/vehicles`. Only one vehicle is currently registered. `vehicle_id` is always auto-assigned from the single active vehicle — it is never taken from form input.

---

## Ticket Number Format

```
Crosswind-{YYYY}-{MM}-{SEQUENCE}
```

- `YYYY` — 4-digit year of filing
- `MM` — 2-digit month of filing (zero-padded)
- `SEQUENCE` — 4-digit zero-padded count, reset per month per vehicle

**The `Crosswind-` prefix is hardcoded.** `vehicle_id` is auto-resolved via `Vehicle::getActive()` in `TripTicket::boot()` if not explicitly set.

**Examples:**
- `Crosswind-2025-07-0001` — first reservation filed in July 2025
- `Crosswind-2025-07-0002` — second reservation filed in July 2025
- `Crosswind-2025-08-0001` — first reservation filed in August 2025

**Generation logic lives in:** `app/Models/TripTicket.php` → `boot()` → `creating` hook.

---

## Database Schema

### `vehicles`
| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(255) | e.g. "Crosswind" |
| plate_number | VARCHAR(255) | e.g. "SJJ 504" |
| is_active | BOOLEAN | default true |
| timestamps | | |

### `users`
| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(255) | |
| email | VARCHAR(255) | unique |
| password | VARCHAR(255) | hashed |
| role | ENUM | `admin`, `staff` |
| timestamps | | |

### `trip_tickets`
| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| vehicle_id | FK → vehicles.id | nullable, auto-set from active vehicle on create |
| ticket_number | VARCHAR(30) | unique, auto-generated |
| date_filed | DATE | auto-set on creation |
| purpose | TEXT | reason for travel |
| date_of_travel | DATE | **retained for backward compat** — equals `date_start` for all new records |
| date_start | DATE | nullable — travel start date (use this going forward) |
| date_end | DATE | nullable — travel end date; equals `date_start` for single-day trips |
| time_departure | TIME | nullable |
| time_return | TIME | nullable |
| destination | VARCHAR(255) | |
| status | ENUM | `pending`, `approved`, `disapproved`, `completed`, `cancelled` |
| requested_by | FK → users.id | |
| approved_by | FK → users.id | nullable |
| remarks | TEXT | nullable, filled on approve/disapprove |
| timestamps | | |

### `passengers`
| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| trip_ticket_id | FK → trip_tickets.id | cascade delete |
| name | VARCHAR(255) | full name |
| designation | VARCHAR(255) | nullable |
| timestamps | | |

### `trip_ticket_logs`
| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| trip_ticket_id | FK → trip_tickets.id | cascade delete |
| from_status | VARCHAR(20) | nullable (null = initial filing) |
| to_status | VARCHAR(20) | |
| changed_by | FK → users.id | |
| remarks | TEXT | nullable |
| timestamps | | |

---

## Roles & Authorization

| Action | `staff` | `admin` |
|---|---|---|
| File a reservation | ✅ | ✅ |
| View own reservations | ✅ | ✅ |
| View all reservations | ❌ | ✅ |
| Edit own pending reservation | ✅ | ✅ |
| Cancel own pending reservation | ✅ | ✅ |
| Approve / Disapprove | ❌ | ✅ |
| Mark as Completed | ❌ | ✅ |
| Manage users | ❌ | ✅ |
| Manage vehicles | ❌ | ✅ |

**Middleware:** `AdminMiddleware` (alias `admin`) → checks `auth()->user()->role === 'admin'`; registered in `bootstrap/app.php`.

---

## Routes

```
GET  /                                          → redirect to /dashboard
GET  /login                                     → Auth\Login (guest)
GET  /dashboard                                 → DashboardController
GET  /reservations                              → Reservations\Index
GET  /reservations/create                       → Reservations\Create
POST /reservations                              → store
GET  /reservations/{ticket}                     → Reservations\Show
GET  /reservations/{ticket}/edit                → Reservations\Edit  (pending + owner/admin)
PUT  /reservations/{ticket}                     → update
DELETE /reservations/{ticket}                   → cancel (sets status = cancelled)
GET  /reservations/{ticket}/print               → printable view (Blade, no Inertia)
GET  /reservations/{ticket}/pdf                 → download PDF trip ticket
GET  /api/reservations/check-conflict           → conflict check (?date_start=&date_end=&exclude=)

PATCH /admin/reservations/{ticket}/approve      → approve
PATCH /admin/reservations/{ticket}/disapprove   → disapprove
PATCH /admin/reservations/{ticket}/complete     → complete
GET   /admin/reservations/export                → Excel download

GET  /admin/vehicles                            → Admin\Vehicles\Index
GET  /admin/vehicles/create                     → Admin\Vehicles\Create
POST /admin/vehicles                            → store
GET  /admin/vehicles/{vehicle}/edit             → Admin\Vehicles\Edit
PUT  /admin/vehicles/{vehicle}                  → update
```

> `{ticket}` resolves by `ticket_number` (route key on `TripTicket`).

---

## Inertia Page Components

```
resources/js/
├── Pages/
│   ├── Auth/
│   │   └── Login.vue
│   ├── Dashboard.vue               ← stat cards + calendar + upcoming trips
│   ├── Reservations/
│   │   ├── Index.vue               ← table with status filter, date range filter
│   │   ├── Create.vue              ← reservation form (date range, passengers, conflict alert)
│   │   ├── Show.vue                ← detail + admin actions + activity log + print/PDF buttons
│   │   └── Edit.vue                ← edit pending reservation
│   └── Admin/
│       └── Vehicles/
│           ├── Index.vue
│           ├── Create.vue
│           └── Edit.vue
├── Components/
│   ├── StatusBadge.vue             ← color-coded pill per status
│   ├── PassengerForm.vue           ← dynamic add/remove passenger rows
│   └── ConflictAlert.vue           ← warning banner for date range conflicts
└── Layouts/
    └── AuthenticatedLayout.vue     ← main nav (Dashboard, Reservations, Vehicles[admin])
```

---

## Controller Reference

| Controller | Methods |
|---|---|
| `DashboardController` | `__invoke` |
| `TripTicketController` | `index`, `create`, `store`, `show`, `edit`, `update`, `cancel` |
| `TripTicketAdminController` | `approve`, `disapprove`, `complete` |
| `ConflictCheckController` | `check` (JSON API) |
| `TripTicketPrintController` | `print`, `pdf` |
| `TripTicketExportController` | `__invoke` (Excel) |
| `VehicleController` | `index`, `create`, `store`, `edit`, `update` |

---

## Model Reference

| Model | Key methods / notes |
|---|---|
| `Vehicle` | `getActive(): self` — returns the single active vehicle; `getLabelAttribute()` — "Crosswind (SJJ 504)" |
| `TripTicket` | `isMultiDay(): bool`, `travelDateLabel(): string`, `logs()`, `vehicle()`, `requester()`, `approver()`, `passengers()` |
| `TripTicketLog` | `actor()` → User, `tripTicket()` |
| `User` | `isAdmin(): bool`, `tripTickets()`, `approvedTickets()` |

---

## Conflict Detection

**Endpoint:** `GET /api/reservations/check-conflict?date_start=YYYY-MM-DD&date_end=YYYY-MM-DD[&exclude=ticket_number]`

**Logic:** Returns `{ conflict: bool, ticket: string|null }`. Checks whether any `approved` reservation overlaps the requested date range using a three-clause OR:

```php
$q->whereBetween('date_start', [$dateStart, $dateEnd])
  ->orWhereBetween('date_end', [$dateStart, $dateEnd])
  ->orWhere(fn($q2) => $q2
      ->where('date_start', '<=', $dateStart)
      ->where('date_end',   '>=', $dateEnd));
```

**Frontend behavior:** In `Create.vue` and `Edit.vue`, a `watch` on `[form.date_start, form.date_end]` debounces (300 ms via `@vueuse/core`) and calls the endpoint. `ConflictAlert.vue` renders reactively. Filing is **not blocked** — it is a warning only. The `exclude` param skips the ticket being edited.

---

## Trip Ticket Auto-Generation (Model Boot)

```php
static::creating(function ($ticket) {
    $now   = now();
    $month = $now->format('m');
    $year  = $now->format('Y');

    if (empty($ticket->vehicle_id)) {
        $ticket->vehicle_id = Vehicle::getActive()->id;
    }

    $count = TripTicket::where('vehicle_id', $ticket->vehicle_id)
                       ->whereBetween('created_at', [
                           $now->copy()->startOfMonth()->toDateTimeString(),
                           $now->copy()->endOfMonth()->toDateTimeString(),
                       ])
                       ->count() + 1;

    $ticket->ticket_number  = 'Crosswind-' . $year . '-' . $month . '-'
                            . str_pad($count, 4, '0', STR_PAD_LEFT);
    $ticket->date_filed     = $now->toDateString();
    $ticket->date_of_travel = $ticket->date_start; // backward compat sync
});
```

---

## Status Flow

```
[Filed] → pending
              ↓
        ┌─────┴─────┐
     approved    disapproved
        ↓
     completed

Any status → cancelled  (by filer while pending, or by admin)
```

---

## Development Phases

### Phase 1 — Core MVP ✅
- [x] Laravel Breeze scaffold (Inertia + Vue 3)
- [x] `users` migration + role column seeder
- [x] `trip_tickets` + `passengers` migrations and models
- [x] Ticket number auto-generation in model boot
- [x] `TripTicketController` CRUD
- [x] `Reservations/Create.vue` with dynamic passenger rows
- [x] `Reservations/Index.vue` with status badge and date filter
- [x] `Reservations/Show.vue` with passenger list
- [x] Admin approve / disapprove with remarks
- [x] `ConflictAlert.vue` + conflict check API endpoint
- [x] `AdminMiddleware`

### Phase 2 — Trip Ticket Printing ✅
- [x] Printable Blade view styled to DOST trip ticket format
- [x] PDF export via `barryvdh/laravel-dompdf`
- [x] Print / Download PDF buttons on Show page

### Phase 3 — Dashboard & Reporting ✅
- [x] Dashboard stat cards: Pending, Approved this month, Completed this month
- [x] Monthly calendar view with booked date highlights
- [x] Excel export via `maatwebsite/excel`

### Phase 4 — Notifications & Audit ✅
- [x] Email notification to admin on new reservation (queued mail)
- [x] `trip_ticket_logs` table for status change history
- [x] Activity log display on Show page

### Vehicle Registration + Multi-Day Travel ✅
- [x] `vehicles` table + `Vehicle` model with `getActive()`
- [x] `vehicle_id` FK on `trip_tickets`, auto-assigned from active vehicle
- [x] `date_start` / `date_end` columns; `date_of_travel` retained for backward compat
- [x] `VehicleSeeder` backfills `vehicle_id` on existing records
- [x] `isMultiDay()` + `travelDateLabel()` helpers on `TripTicket`
- [x] `VehicleController` + Admin/Vehicles CRUD pages
- [x] Conflict detection updated to range-overlap query
- [x] Reservations/Index travel date range display
- [x] Reservations/Show vehicle row + multi-day badge + travel date label
- [x] Dashboard booked-dates updated to date_start/date_end range expansion

---

## Key Conventions

- Use `<script setup>` syntax for all Vue components.
- Use `useForm()` from `@inertiajs/vue3` for all form submissions.
- Use `route()` helper (Ziggy) for named routes in Vue — available as `window.route` global (no import needed).
- All API calls for reactive checks (conflict detection) use `axios` directly, not Inertia.
- Tailwind UI components are used as-is; do not introduce other UI libraries.
- Icons are sourced exclusively from `lucide-vue-next`.
- Admin-only UI sections are conditionally rendered using `$page.props.auth.user.role === 'admin'`.
- MySQL strict mode is enabled; all nullable columns must be explicitly declared.
- Soft deletes are **not** used; cancelled reservations remain in the table with `status = 'cancelled'`.
- `vehicle_id` is **always auto-assigned** via `Vehicle::getActive()` in `TripTicket::boot()`. Never pass it from form input.
- Avoid `whereYear()`/`whereMonth()` — use `whereBetween()` with pre-computed date strings instead (Intelephense stubs issue).

---

## Environment Notes

- **Local DB:** MySQL (via Laragon)
- **Dev OS:** Windows (MSI Thin 15, i5-13420H, 8GB RAM)
- **IDE:** VSCode with Claude Code
- **PHP:** 8.3
- **Node:** 20+

---

## Naming Reference

| Term | Meaning |
|---|---|
| sdn-vrs | Project folder name |
| Crosswind | Vehicle name used in ticket number prefix |
| SJJ 504 | Official plate number of the vehicle |
| PSTO-SDN | DOST-PSTO Surigao del Norte |
| Trip Ticket | The official travel authorization document generated from an approved reservation |
| date_filed | Date the reservation request was submitted |
| date_of_travel | Legacy single-date field (= date_start for new records) |
| date_start | Travel start date — use this for all new queries |
| date_end | Travel end date — equals date_start for single-day trips |
| isMultiDay | True when date_end differs from date_start |
| travelDateLabel | Human-readable date range string, e.g. "Jul 10 – Jul 12, 2025" |
