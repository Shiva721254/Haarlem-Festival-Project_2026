# GDPR & Privacy Considerations

How the Haarlem Festival website handles personal data, the lawful bases, the
data-subject rights it implements, and known residual risks. This reflects a
*privacy-by-design* approach: collect the minimum, explain the why, and give
users control.

## 1. Personal data inventory

| Data | Where | Purpose | Lawful basis |
|---|---|---|---|
| Name, email, password (hashed) | `users` | Account, sign-in, order contact | Contract |
| Orders, invoices, payment reference | `orders`, `order_items` | Sell/deliver tickets; bookkeeping | Contract / legal obligation |
| Reservation **special requests** (allergies, diet, accessibility) | `order_items.special_requests` | Passed to the venue to handle the request | **Explicit consent** (optional field) |
| Profile picture (optional) | `users.profile_image` | Personalisation | Consent |
| Session cookie | browser | Keep you signed in | Legitimate interest (strictly necessary) |

Payment card data is handled entirely by **Stripe** (hosted checkout) and is
never stored on our servers.

## 2. Special category data

Allergy / dietary / accessibility notes can constitute **health data**. We:
- make the field **optional**,
- state at the point of entry that it is shared **only** with the venue to
  handle the request, with a link to the privacy policy,
- treat its submission as **explicit consent** for that single purpose.

## 3. Data-subject rights (implemented)

| Right | Where |
|---|---|
| **Information / transparency** | `/privacy` page; notices at registration and on the reservation field |
| **Access / portability** | `/account/data` — downloads a JSON copy of the account + orders |
| **Erasure** | `/account/delete` — anonymises the account (see below) |
| **Rectification** | `/account` — edit name/email, change password |
| **Consent** | required, unticked checkbox at registration; no pre-consent |

### Erasure approach
Deleting an account **anonymises** the user row (name/email/password/tokens
cleared, email replaced with a non-routable placeholder, account deactivated)
rather than hard-deleting it. This removes personal data while preserving the
integrity of linked invoice/transaction records that must be retained for
legal/accounting reasons.

## 4. Retention
- Account data: while the account is active.
- Order/invoice records: retained per legal obligation, anonymised on erasure.
- Verification/reset tokens: short-lived and cleared after use.

## 5. Security measures supporting privacy
- Passwords hashed with `password_hash()` (bcrypt).
- CSRF token verified on every POST.
- SQL injection prevented via prepared statements; sort columns whitelisted.
- Output escaped with `htmlspecialchars` across user-facing views.
- Session cookie hardened: `HttpOnly`, `SameSite=Lax`, `Secure` under HTTPS;
  session id rotated on account deletion.
- Role-based access control (customer / employee / admin) via middleware.
- Uploaded images validated by real MIME type and size; randomised filenames.

## 6. Known residual risks / follow-ups
- **CMS HTML**: homepage content blocks are authored by admins via a WYSIWYG and
  rendered as raw HTML. Admins are trusted, but a dedicated HTML sanitiser
  (e.g. HTMLPurifier) would harden this against stored XSS and is recommended
  before production.
- **HTTPS**: the `Secure` cookie flag activates under HTTPS; production must be
  served over TLS.
- A formal data-processing agreement with Stripe (sub-processor) should be on
  file for production.
