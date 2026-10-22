# Implementation checklist

- [x] Laravel 12, Blade, Bootstrap 5, Eloquent MVC, MySQL/MariaDB configuration
- [x] Home, About, Contact, browse, details, register, login, profile pages
- [x] Student sessions, logout, password hashing, forced student registration role
- [x] Listing create/read/update/delete, optional cover photo and file cleanup
- [x] My listings and persistent sold status
- [x] Subject/title/description search, category/subject/semester/price/condition filters
- [x] Sort whitelist and server pagination preserving filters
- [x] Sent and received private enquiries, explicit seller response and status changes
- [x] Own-listing and sold-listing enquiry rejection
- [x] Admin dashboard with six live statistics
- [x] Student search, suspension/reactivation, protected admin accounts
- [x] Admin listing inspection/removal, category and subject CRUD with safe deletion
- [x] Student reports, deduplication, admin review/resolution
- [x] Policy/gate authorization, active account middleware, CSRF, escaped text
- [x] Server validation, upload restrictions, foreign keys, decimal prices
- [x] User/material/category/subject/enquiry/report factories and relationships
- [x] Configurable development-only accounts and meaningful demo materials
- [x] Custom 403, 404, 500 pages
- [x] Automated core workflow tests, Composer lock file, local assets
- [x] README with Windows/XAMPP setup, demo sequence and troubleshooting

Scope decisions: subject is consistently stored as subject_id; account management uses reversible suspension; Contact provides enquiry/report/support directions rather than a form with no configured recipient. Seller replies have an explicit response column. No payments, delivery, live chat or document downloads are included.
