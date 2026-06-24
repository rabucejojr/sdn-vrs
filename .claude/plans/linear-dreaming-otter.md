# SDN-VRS — Email Verification & Forgot Password

## Context

The system uses Laravel Breeze as its auth scaffold, which ships with full email verification and password reset controllers, routes, and Vue pages. However, none of this is actually functional today because of a single root cause: the **User model does not implement `MustVerifyEmail`**. Additionally, the mail transport is set to `log`, meaning no emails are delivered. The `verified` middleware is already on the dashboard but is missing from reservations, profile, admin, and notification routes. This plan enforces full email verification across all protected routes, enables real mail delivery, and changes the admin user creation workflow to a secure password-reset-based account setup flow.

---

## Current Authentication Status

| Item | Status | Notes |
|---|---|---|
| `User` model → `MustVerifyEmail` | ❌ MISSING | Core blocker — nothing else works without this |
| Email verification routes | ✅ Complete | All 3 routes in `auth.php` |
| Verification controllers | ✅ Complete | Prompt, Notify, Verify all implemented |
| `verified` middleware on dashboard | ✅ Exists | Only on dashboard — missing everywhere else |
| `verified` middleware on reservations | ❌ Missing | Only `auth` |
| `verified` middleware on profile | ❌ Missing | Only `auth` |
| `verified` middleware on admin routes | ❌ Missing | Only `admin` |
| `verified` middleware on notifications | ❌ Missing | Only `auth` |
| Forgot password routes | ✅ Complete | All routes in `auth.php` |
| `PasswordResetLinkController` | ✅ Complete | Standard Breeze |
| `NewPasswordController` | ✅ Complete | Standard Breeze |
| `password_reset_tokens` table | ✅ Exists | In initial Laravel migration |
| Reset token expiry | ✅ 60 min | In `config/auth.php` |
| `ForgotPassword.vue` | ✅ Complete | Email input, status display |
| `ResetPassword.vue` | ✅ Complete | Token + email + password form |
| `VerifyEmail.vue` | ✅ Complete | Resend button, status display |
| Login `canResetPassword` prop | ✅ Set | `AuthenticatedSessionController::create()` passes it |
| `MAIL_MAILER` | ❌ `log` | Emails written to log file, never delivered |
| `QUEUE_CONNECTION` | `database` | Queue worker needed for database notifications |
| `jobs` table | ✅ Exists | Initial migration |
| Admin user creation pre-verifies email | ❌ Bypass | `email_verified_at = now()` — to be removed |
| Password fields on admin Create form | ❌ Insecure | Admins should not set passwords for users |
| `PasswordReset` notification for user | ❌ Missing | No feedback to user after password change |
| `Verified` notification for user | ❌ Missing | No feedback to user after email verified |
| Toast button text hardcoded | ⚠️ Bug | "View reservation →" shown for all notification types |

---

## Root Cause: MustVerifyEmail

The **single most important change** is adding `MustVerifyEmail` to the User model:

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
```

Without this:
- `$user->sendEmailVerificationNotification()` uses a different code path
- `$request->user() instanceof MustVerifyEmail` returns `false` in ProfileController
- Laravel's `verified` middleware still works (it checks `hasVerifiedEmail()`), but the `MustVerifyEmail` contract is the semantic trigger for many framework helpers

---

## Email Verification Analysis

**What works (once MustVerifyEmail is added + mail configured):**
- `VerifyEmailController` — validates signed URL, marks verified, redirects with `?verified=1`
- `EmailVerificationNotificationController` — resend flow
- `EmailVerificationPromptController` — shows VerifyEmail.vue for unverified users
- `VerifyEmail.vue` — shows resend button, success status after resend
- Verification routes — all complete with `signed` + `throttle` middleware

**What needs to change:**
1. User model — add `MustVerifyEmail` interface
2. `routes/web.php` — add `verified` to main `auth` group
3. Admin user creation — stop pre-verifying, send password reset email instead
4. `NewPasswordController` — mark email as verified when user sets password via reset link (first-time setup)
5. Add `EmailVerifiedNotification` (database) triggered on `Verified` event

---

## Forgot Password Analysis

**The forgot password flow is completely implemented and functional.** Once mail is configured:
- `Password::sendResetLink()` works out of the box
- `Password::reset()` handles token validation, password hash, fires `PasswordReset` event
- Token expiry is 60 minutes (config/auth.php)
- `remember_token` is regenerated on reset (existing sessions invalidated)
- `ForgotPassword.vue` and `ResetPassword.vue` are both complete

**The only blocker is `MAIL_MAILER=log`.**

---

## Mail Configuration

**Current state:** `MAIL_MAILER=log` — all mail is written to `storage/logs/laravel.log` and never delivered. `MAIL_FROM_ADDRESS` is the placeholder `hello@example.com`.

**Recommendation — Development: Mailtrap**

Mailtrap is a fake SMTP inbox that catches all emails without delivering them to real addresses. Free tier is sufficient. Configuration:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<from mailtrap dashboard>
MAIL_PASSWORD=<from mailtrap dashboard>
MAIL_FROM_ADDRESS="noreply@dostsdn.gov.ph"
MAIL_FROM_NAME="${APP_NAME}"
```

**Recommendation — Production: Gmail SMTP with App Password**

Most practical for a government office already on Google Workspace or Gmail:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=ssl
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=<16-char App Password from Google Account>
MAIL_FROM_ADDRESS="your-gmail@gmail.com"
MAIL_FROM_NAME="SDN Vehicle Reservation System"
```

Requirements: Enable 2FA on the Google account, generate an App Password (Security → 2-Step Verification → App Passwords).

> **Note:** `config/mail.php` requires no changes — it reads from `.env`.

---

## Queue Configuration

**Current state:** `QUEUE_CONNECTION=database` with the `jobs` table present. All six notification classes implement `ShouldQueue`.

**Key distinction:**
- Password reset email and verification email are sent via Laravel's built-in `Notification` classes, which are **synchronous by default** (not queued). These will work without a queue worker once mail is configured.
- The six custom database notifications (`NewReservationNotification`, `UserCreatedNotification`, etc.) implement `ShouldQueue` and will sit in the `jobs` table until a worker processes them.

**Recommendation for development:** Set `QUEUE_CONNECTION=sync` in `.env` during development. This executes all queued jobs immediately in the same request — no separate worker needed. Switch back to `database` for production.

**For production:** Run a persistent queue worker. On Windows, use a scheduled task or NSSM (Non-Sucking Service Manager) to keep `php artisan queue:work` running:

```
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Without a running worker, database notifications will queue but never send.

---

## Admin User Creation — New Workflow

### Current (to be removed)
Admin fills: Name, Email, Role, Password, Confirm Password → user is created with `email_verified_at = now()` (pre-verified, no email sent, admin knows the password).

### New Workflow
Admin fills: Name, Email, Role only → system generates a secure random password → sends a password reset link email to the user.

**Flow after account creation:**
1. User receives a "Welcome — set up your password" email with a reset link
2. User clicks link → `ResetPassword.vue` → sets their own password
3. `NewPasswordController::store()` marks `email_verified_at = now()` if null (first-time setup)
4. User is redirected to login → logs in with full access

**Why mark email verified on password reset?** The user proved they own the email address by receiving and acting on the password reset link. One email, one action. No separate verification step.

**Security:** For subsequent "forgot password" requests, if `email_verified_at` is already set, the reset does nothing to verification status — the behavior is a no-op check.

---

## Backend Changes

### 1. `app/Models/User.php`
Add `MustVerifyEmail` interface:
```php
use Illuminate\Contracts\Auth\MustVerifyEmail;
class User extends Authenticatable implements MustVerifyEmail
```
No other changes — casts, fillable, and traits are already correct.

### 2. `routes/web.php`
Change the outer auth group to require `verified`:
```php
// FROM:
Route::middleware('auth')->group(function () {

// TO:
Route::middleware(['auth', 'verified'])->group(function () {
```
This protects profile, reservations, notifications, and admin routes with a single change. The verification routes in `auth.php` are in a separate `middleware('auth')` group (without `verified`) so they remain accessible to unverified users.

### 3. `app/Http/Controllers/Auth/NewPasswordController.php`
In the `Password::reset()` callback, mark email as verified if not already set:
```php
function ($user) use ($request) {
    $user->forceFill([
        'password'       => Hash::make($request->password),
        'remember_token' => Str::random(60),
    ]);
    if (is_null($user->email_verified_at)) {
        $user->email_verified_at = now();
    }
    $user->save();
    event(new PasswordReset($user));
}
```

### 4. `app/Http/Controllers/UserController.php` — `store()` method
Remove `email_verified_at` pre-verification and password from form data. Generate random password. Send reset link:
```php
public function store(StoreUserRequest $request): RedirectResponse
{
    $user = User::create([
        'name'      => $request->name,
        'email'     => $request->email,
        'role'      => $request->role,
        'password'  => Hash::make(Str::password(32)),
        'is_active' => true,
        // email_verified_at intentionally omitted
    ]);

    Password::sendResetLink(['email' => $user->email]);

    $user->notify(new UserCreatedNotification(auth()->user()));

    return redirect()->route('admin.users.show', $user)
        ->with('success', "Account created. A password setup email has been sent to {$user->email}.");
}
```
Add imports: `use Illuminate\Support\Str;` and `use Illuminate\Support\Facades\Password;` and `use Illuminate\Support\Facades\Hash;`

### 5. `app/Http/Requests/StoreUserRequest.php`
Remove password validation entirely:
```php
public function rules(): array
{
    return [
        'name'  => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
        'role'  => ['required', Rule::in(['admin', 'staff'])],
    ];
}
```

### 6. New: `app/Notifications/EmailVerifiedNotification.php`
Database notification sent when user verifies their email. **Not queued** (instant feedback):
```php
public function via(): array { return ['database']; }
public function toArray(): array {
    return [
        'action'  => 'email_verified',
        'message' => 'Your email address has been successfully verified.',
        'url'     => '/dashboard',
    ];
}
```

### 7. New: `app/Notifications/PasswordChangedNotification.php`
Database notification sent when password is reset:
```php
public function via(): array { return ['database']; }
public function toArray(): array {
    return [
        'action'  => 'password_changed',
        'message' => 'Your password was successfully changed.',
        'url'     => '/dashboard',
    ];
}
```

### 8. `app/Providers/AppServiceProvider.php`
Register event listeners for `Verified` and `PasswordReset` events in `boot()`:
```php
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;

Event::listen(Verified::class, function ($event) {
    $event->user->notify(new EmailVerifiedNotification());
});

Event::listen(PasswordReset::class, function ($event) {
    $event->user->notify(new PasswordChangedNotification());
});
```

### 9. `app/Http/Middleware/HandleInertiaRequests.php`
Add `mustVerifyEmail` to shared auth props so the frontend Profile page can show the re-verify prompt:
```php
'auth' => [
    'user'            => $request->user(),
    'mustVerifyEmail' => $request->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
],
```

### 10. `database/seeders/DatabaseSeeder.php`
Ensure seeded admin/staff users have `email_verified_at` set — they existed before verification was enforced:
```php
'email_verified_at' => now(),
```
Confirm this is already present in the seeder. If not, add it.

### 11. `.env`
Configure Mailtrap for development (credentials obtained from mailtrap.io):
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<mailtrap-username>
MAIL_PASSWORD=<mailtrap-password>
MAIL_FROM_ADDRESS="noreply@dostsdn.gov.ph"
MAIL_FROM_NAME="SDN Vehicle Reservation System"
QUEUE_CONNECTION=sync
```

---

## Frontend Changes

### 1. `resources/js/Pages/Admin/Users/Create.vue`
Remove password and confirm password fields entirely. Update submit to not reset those fields. The form becomes name + email + role only.

Update success message display to reference the email setup link.

### 2. `resources/js/Components/ToastNotification.vue`
Fix hardcoded "View reservation →" button text. Make it dynamic based on `notification.data.action`:
```js
const buttonLabel = computed(() => {
    const action = props.notification?.data?.action
    if (action === 'email_verified' || action === 'password_changed' || action === 'account_created') return 'Go to Dashboard →'
    if (action === 'deactivated') return 'Go to Login →'
    if (action === 'role_changed') return 'View Profile →'
    return 'View →'
})
```

### 3. Auth pages — no changes required
`VerifyEmail.vue`, `ForgotPassword.vue`, `ResetPassword.vue` are all fully implemented and mobile-first. No changes needed.

---

## Middleware Changes Summary

| Route Group | Current | After |
|---|---|---|
| `/dashboard` | `auth, verified` | ✅ unchanged |
| `auth` group in `web.php` | `auth` | `auth, verified` |
| Verification routes in `auth.php` | `auth` | ✅ unchanged (must remain without `verified`) |
| Admin group | `admin` (inherits `auth`) | `admin` (inherits `auth, verified`) |

---

## Notification Changes

| Event | Trigger Point | Notification | Channel |
|---|---|---|---|
| Email verified | `Verified` event listener | `EmailVerifiedNotification` | database |
| Password reset | `PasswordReset` event listener | `PasswordChangedNotification` | database |
| Account created (admin) | `UserController::store()` | `UserCreatedNotification` | database |
| Role changed | `UserController::update()` | `UserRoleChangedNotification` | database |
| Account deactivated | `UserController::toggleActive()` | `UserDeactivatedNotification` | database |

The new `EmailVerifiedNotification` and `PasswordChangedNotification` should NOT implement `ShouldQueue` — they should fire synchronously so the toast appears immediately after the action.

---

## Security Considerations

- **Signed verification URLs** — already enforced via `['signed', 'throttle:6,1']` on `verification.verify`
- **Reset token expiry** — 60 minutes (config/auth.php) — adequate
- **Session invalidation on password reset** — `remember_token` regenerated in `NewPasswordController`, invalidating all "remember me" sessions
- **No password in admin email** — admin never knows or transmits the user's password
- **Rate limiting on resend** — `throttle:6,1` on `verification.send`
- **Email-verified-at on password reset** — only sets if null; no effect on users with existing verified emails
- **Self-deactivation guard** — already implemented in `UserController::toggleActive()`
- **`EnsureUserIsActive`** — already kicks out deactivated users mid-session; if they're unverified after being forced out, they cannot re-enter protected routes

---

## Edge Cases

| Case | Handling |
|---|---|
| Existing users without `email_verified_at` | Update DatabaseSeeder; optionally run one-time `User::whereNull('email_verified_at')->update(['email_verified_at' => now()])` in a migration or tinker |
| Admin creates user → wrong email entered | User never receives reset link; admin must delete and recreate |
| Password reset link expires (60 min) | User requests new one via `/forgot-password` — standard flow |
| Admin changes user's email | `UpdateUserRequest` clears `email_verified_at`; user gets `verified` middleware bounce until re-verified |
| User tries to access protected route while unverified | Laravel redirects to `verification.notice` (VerifyEmail.vue) |
| User accesses `/verify-email` after already verified | `EmailVerificationPromptController` redirects to dashboard |
| Mailtrap in production accidentally | `MAIL_FROM_ADDRESS` switch and Mailtrap credentials in `.env.production` are separate — document clearly |
| Queue worker not running (database queue) | Custom DB notifications pile up in `jobs` table; fix: set `QUEUE_CONNECTION=sync` for dev or run worker |
| `Password::sendResetLink()` returns `INVALID_USER` | Email address not found in users table — this won't happen since we just created the user; safe to ignore error in `store()` |

---

## Step-by-Step Implementation Plan

### Step 1 — MustVerifyEmail (1 line)
- `app/Models/User.php`: add `implements MustVerifyEmail`

### Step 2 — Enforce Verified Middleware
- `routes/web.php`: change outer auth group to `['auth', 'verified']`

### Step 3 — Mail Configuration
- Register at mailtrap.io (free), get SMTP credentials
- Update `.env`: set `MAIL_MAILER=smtp`, Mailtrap credentials, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- Set `QUEUE_CONNECTION=sync` for dev

### Step 4 — NewPasswordController: Mark Email Verified on First Setup
- `app/Http/Controllers/Auth/NewPasswordController.php`: add `email_verified_at` backfill in callback

### Step 5 — Admin User Creation Workflow
- `app/Http/Requests/StoreUserRequest.php`: remove password rules
- `app/Http/Controllers/UserController.php` store(): random password + `Password::sendResetLink()` + remove `email_verified_at`
- `resources/js/Pages/Admin/Users/Create.vue`: remove password/confirm fields

### Step 6 — New Notification Classes
- Create `app/Notifications/EmailVerifiedNotification.php`
- Create `app/Notifications/PasswordChangedNotification.php`

### Step 7 — Event Listeners
- `app/Providers/AppServiceProvider.php`: register `Verified` → `EmailVerifiedNotification` and `PasswordReset` → `PasswordChangedNotification`

### Step 8 — HandleInertiaRequests: mustVerifyEmail Prop
- Add `mustVerifyEmail` to `share()` method

### Step 9 — Fix Toast Button Text
- `resources/js/Components/ToastNotification.vue`: make button label dynamic by `action`

### Step 10 — Existing Users Backfill
- Check `DatabaseSeeder` — add `email_verified_at => now()` if missing from seeded users
- Verify all existing DB users have `email_verified_at` set (run in tinker if needed)

### Step 11 — Build and Verify
- `npm run build`
- Test: register/create user → email received in Mailtrap → verify → toast appears
- Test: forgot password → email received → reset → redirected to login
- Test: unverified user blocked from dashboard, reservations, profile
- Test: verification link redirects verified users to dashboard
- Test: admin creates user → reset email sent → user sets password → full access
