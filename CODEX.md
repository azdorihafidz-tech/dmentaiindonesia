# CODEX.md — D'Mentai Indonesia Official Website

## 1. PROJECT IDENTITY

Project name:
**D'Mentai Indonesia Official Website**

Brand:
**D'Mentai — Dimsum & Gyoza**

Domain target:
**https://dmentaiindonesia.com**

Project purpose:
Build a modern, responsive, secure, SEO-friendly official website for the D'Mentai Indonesia food business, with a complete admin/CMS panel so authorized administrators can manage the public website without editing source code.

The website is a **company/brand website**, not an ERP and not a marketplace.

The main objectives are:

- Present D'Mentai Indonesia professionally.
- Display products/menu.
- Display promotions.
- Display outlets.
- Publish articles/content for SEO.
- Display gallery, testimonials, FAQ, contact information, and brand information.
- Allow administrators to manage website content through an admin panel.
- Provide SEO management.
- Provide website visitor statistics.
- Provide media management.
- Provide activity/audit logging.
- Be responsive on desktop, tablet, and mobile.
- Be secure and production-ready.
- Be maintainable and easy to extend.

---

# 2. NON-NEGOTIABLE TECHNOLOGY STACK

Use these technologies unless the project owner explicitly approves a change.

## Backend

- Laravel 12.x
- PHP 8.2+
- MariaDB provided by XAMPP during local development
- Eloquent ORM
- Laravel migrations
- Laravel validation
- Laravel authorization/policies
- Laravel queues only when genuinely needed

## Frontend

- Blade
- Livewire
- Tailwind CSS
- Vite
- Modern responsive HTML/CSS/JavaScript

## Admin Panel

- Filament 5.x
- Use the version compatible with Laravel 12 and the installed PHP version.
- Do not upgrade to Filament 6 or another major version without explicit approval.

## Development environment

Operating system:
Windows

Local web root:
`D:\xampp\htdocs`

IDE:
Visual Studio Code

AI coding agent:
Codex

Local database:
MariaDB through XAMPP

Source control:
Git

Remote repository:
GitHub

Production:
cPanel hosting

Target domain:
`dmentaiindonesia.com`

---

# 3. IMPORTANT VERSION POLICY

Laravel 12 is intentional.

DO NOT automatically upgrade this project to Laravel 13.

DO NOT replace Laravel with another framework.

DO NOT replace Blade/Livewire with React/Vue/Next.js unless explicitly requested.

DO NOT replace Filament with another admin framework unless explicitly requested.

Minor and patch updates may be considered only when they remain compatible with the locked major versions and do not introduce unnecessary risk.

Before installing or changing a major dependency:

1. Check compatibility with Laravel 12.
2. Check compatibility with the installed PHP version.
3. Check compatibility with other existing dependencies.
4. Explain the reason for the change.
5. Do not proceed with a breaking major upgrade without approval.

---

# 4. PROJECT LOCATION

The intended local project path is:

`D:\xampp\htdocs\dmentaiindonesia`

If this directory does not exist, Codex may create it.

Before creating a new Laravel project:

1. Check whether the directory already contains a Laravel project.
2. Check whether `.git` exists.
3. Check whether `composer.json` exists.
4. Check whether important files already exist.
5. NEVER delete an existing project just because the directory is not empty.
6. If an existing project is detected, inspect it before making changes.

The target URL during local development may be:

`http://localhost/dmentaiindonesia`

or another local Laravel development URL, depending on the development setup.

---

# 5. BRAND / DESIGN DIRECTION

The supplied D'Mentai logo is the primary visual reference.

Brand identity:

- Orange
- Black
- White

The logo contains a playful food mascot/character.

The visual direction should be:

- Modern
- Clean
- Premium but friendly
- Food-focused
- Energetic
- Youthful
- Easy to navigate
- Strong visual hierarchy
- High-quality product photography
- Mobile-first
- Fast loading

Avoid:

- Generic Laravel-looking pages.
- Generic Bootstrap-looking layouts.
- Excessive animations.
- Excessive gradients.
- Overly complicated navigation.
- Low-contrast text.
- Heavy pages that load slowly.
- Designs that look like an ERP.
- Unnecessary UI decoration.

The logo must remain visually recognizable.

Do not distort the logo.

Do not stretch the logo.

Do not change the logo colors unless an approved alternate logo is supplied.

---

# 6. BRAND ASSET

The project owner has supplied a D'Mentai logo image.

The logo should eventually be stored in the project in an appropriate public/media location.

Suggested structure:

`public/images/brand/`

or through the CMS media system once implemented.

Recommended assets:

- Logo
- Favicon
- OG image
- Product images
- Promotional banners
- Outlet photos
- Gallery photos

Do not hardcode a temporary logo path throughout the application.

Use a centralized settings/media mechanism where appropriate.

---

# 7. PUBLIC WEBSITE STRUCTURE

The initial public website should support:

- Home
- About / Tentang D'Mentai
- Menu / Produk
- Product detail
- Promo
- Outlet
- Gallery
- Article / Blog
- FAQ
- Contact
- Privacy Policy
- Terms / legal pages if needed

Suggested URLs:

`/`
`/tentang-kami`
`/menu`
`/menu/{slug}`
`/promo`
`/promo/{slug}`
`/outlet`
`/outlet/{slug}`
`/galeri`
`/artikel`
`/artikel/{slug}`
`/faq`
`/kontak`

URLs should be clean and SEO-friendly.

Avoid unnecessary query parameters for normal public content pages.

---

# 8. HOMEPAGE CONCEPT

The homepage should be visually strong.

Suggested sections:

1. Header / Navigation
2. Hero section
3. Main call-to-action
4. Featured menu/products
5. Product categories
6. Current promotions
7. Why D'Mentai
8. Outlet/location section
9. Testimonials
10. Gallery/social proof
11. Article/SEO content
12. Contact/CTA
13. Footer

The exact sections may be refined during implementation.

All major homepage content should be manageable from the admin panel whenever practical.

---

# 9. ADMIN PANEL

Admin panel URL:

`/admin`

Use Filament 5.

The admin panel should include, progressively:

## Dashboard

Show useful information such as:

- Visitors today
- Visitors this week
- Visitors this month
- Popular pages
- Product count
- Active promotions
- Outlet count
- Article count
- Recent activity

Avoid fake statistics.

If data does not exist yet, display zero or an appropriate empty state.

Do not generate fake visitor numbers.

---

# 10. CMS MODULES

The planned CMS modules are:

## Website Settings

Manage:

- Website name
- Tagline
- Logo
- Favicon
- Contact email
- WhatsApp
- Phone
- Instagram
- TikTok
- Facebook
- YouTube
- Address
- Google Maps URL
- Business hours
- Footer text
- Copyright
- Google Analytics ID
- Search Console verification
- Maintenance mode if implemented

## Pages

Manage:

- Title
- Slug
- Content
- Featured image
- Status
- SEO metadata

## Products

Manage:

- Product name
- Slug
- Category
- Main image
- Gallery images
- Description
- Price
- Promotional price if applicable
- Featured status
- Availability/status
- Sort order
- SEO metadata

Example products may include:

- Dimsum Ayam
- Dimsum Ayam Jamur
- Dimsum Mentai
- Dimsum Udang
- Gyoza Original
- Gyoza Pedas
- Frozen Dimsum
- Frozen Gyoza

These are examples only. Do not assume this is the final catalog.

## Product Categories

Manage categories and ordering.

## Promotions

Manage:

- Title
- Slug
- Description
- Banner/image
- Start date
- End date
- CTA
- Link
- Active/inactive status
- SEO metadata

## Outlets

Manage:

- Outlet name
- Slug
- Address
- Phone/WhatsApp
- Opening hours
- Google Maps URL
- Latitude/longitude if required
- Image
- Status

## Articles

Manage:

- Title
- Slug
- Excerpt
- Content
- Featured image
- Category
- Author
- Published date
- Draft/published status
- SEO metadata

## Gallery

Manage:

- Image
- Title
- Caption
- Category
- Alt text
- Sort order
- Status

## Testimonials

Manage:

- Customer name
- Customer text
- Rating if appropriate
- Image/avatar if supplied
- Status
- Sort order

Do not invent customer testimonials.

## FAQ

Manage:

- Question
- Answer
- Category if needed
- Sort order
- Status

## Media

The media system should support:

- Upload
- Preview
- Search
- Delete
- Alt text
- Caption
- File information

Validate uploaded files.

Do not allow arbitrary executable files to be uploaded.

---

# 11. SEO REQUIREMENTS

SEO must be designed from the beginning.

Global SEO settings:

- Site title
- Meta description
- Default OG image
- Canonical URL
- Robots configuration
- Google verification
- Social metadata

Per-page SEO:

- SEO title
- Meta description
- Canonical URL
- OG title
- OG description
- OG image
- Robots directive if required

SEO-friendly features:

- Clean URLs
- Semantic HTML
- Proper heading hierarchy
- Image alt text
- Canonical URLs
- XML sitemap
- robots.txt
- Open Graph metadata
- Twitter/X card metadata where appropriate
- Structured data/schema where appropriate
- Breadcrumbs where appropriate
- Fast loading
- Mobile-friendly layout

Do not keyword-stuff content.

Do not generate misleading metadata.

Do not automatically overwrite manually configured SEO metadata.

Fallback rules may be used:

Page SEO title -> global site title fallback.

Page meta description -> page excerpt/content fallback -> global description fallback.

---

# 12. SITEMAP

Provide:

`/sitemap.xml`

The sitemap should include relevant public content such as:

- Home
- Public pages
- Published products
- Active/public promotions
- Public outlets
- Published articles

Do not include:

- Admin pages
- Draft content
- Private pages
- Login pages
- Internal system routes

---

# 13. ROBOTS.TXT

Provide:

`/robots.txt`

The admin panel should not be indexed.

Do not expose sensitive routes.

The exact robots configuration should be reviewed before production.

---

# 14. VISITOR ANALYTICS

Implement a lightweight first-party visitor tracking system only where appropriate.

Possible data:

- Visit timestamp
- Route/page
- Session identifier
- Device type
- Browser
- Operating system
- Referrer
- IP address only if genuinely necessary and handled appropriately

Privacy requirements must be considered.

Do not collect unnecessary personal information.

Do not store sensitive information.

If IP storage is implemented, document retention and privacy behavior.

Dashboard statistics should be based on real records.

Potential dashboard metrics:

- Visitors today
- Visitors yesterday
- Visitors this week
- Visitors this month
- Top pages
- Device breakdown
- Referrer summary

For advanced analytics, allow Google Analytics integration.

---

# 15. GOOGLE ANALYTICS / SEARCH CONSOLE

The system should allow administrators to configure:

- Google Analytics measurement ID
- Google Search Console verification code

Do not hardcode IDs in source code.

Use environment variables or CMS settings according to the security/design requirements.

Do not expose private API credentials in frontend HTML.

---

# 16. SECURITY RULES

Security is mandatory.

Always:

- Validate input.
- Authorize administrative actions.
- Use CSRF protection.
- Use Laravel authentication.
- Use policies/gates where required.
- Validate uploaded files.
- Limit upload size.
- Restrict dangerous file extensions.
- Escape output appropriately.
- Avoid raw SQL when Eloquent/query builder is sufficient.
- Use parameterized queries.
- Protect sensitive routes.
- Use rate limiting where appropriate.
- Do not expose debug information in production.
- Do not commit `.env`.
- Do not commit API keys.
- Do not commit passwords.
- Do not commit private credentials.
- Do not hardcode production secrets.

Production must use:

`APP_DEBUG=false`

Never deploy with debugging enabled.

---

# 17. ADMIN AUTHORIZATION

Not every admin user should automatically receive every permission.

Prepare for roles such as:

- Super Admin
- Content Admin
- SEO Admin

Permission granularity should be introduced only where useful.

At minimum, destructive actions should be protected.

For sensitive operations:

- Require authorization.
- Ask for confirmation.
- Record activity when appropriate.

---

# 18. ACTIVITY LOG

The system should eventually record important administrative activity.

Examples:

- Login
- Logout
- Create product
- Update product
- Delete product
- Publish article
- Update settings
- Upload media
- Delete media

Activity logs should contain useful information without storing passwords or secrets.

---

# 19. DATABASE DESIGN RULES

Use migrations for schema changes.

Never manually modify production database structure without a migration unless there is an explicit emergency procedure.

Use:

- Foreign keys where appropriate
- Indexes
- Unique constraints where appropriate
- Nullable fields only when logically justified
- Appropriate data types
- Timestamps
- Soft deletes where appropriate

Do not create excessive tables just because they might be useful later.

Build the schema based on actual requirements.

Avoid duplicated data.

Use relationships properly.

---

# 20. INITIAL DATA / SEEDERS

Use seeders for safe development/demo data.

Seeders must clearly distinguish:

- Demo/test data
- Real production data

Never put private production data into seeders.

Never create fake visitor statistics as if they were real.

If demo products are needed, clearly identify them as demo data.

---

# 21. CODING STYLE

Follow Laravel conventions.

Prefer:

- Form Requests
- Policies
- Service classes when business logic is complex
- Eloquent relationships
- Reusable Blade components
- Reusable Livewire components
- Small methods
- Meaningful names
- Clear separation of responsibilities

Avoid:

- Giant controllers
- Giant Blade files
- Repeated business logic
- Raw SQL without a reason
- Inline secrets
- Hardcoded URLs where configuration is appropriate
- Copy/paste implementations when reusable components make sense

---

# 22. FRONTEND RESPONSIVENESS

Every public page must be tested conceptually for:

- Mobile
- Tablet
- Desktop

Important widths include approximately:

- 320px
- 375px
- 390px
- 430px
- 768px
- 1024px
- 1280px+

Do not assume a desktop design automatically works on mobile.

Check:

- Navigation
- Images
- Cards
- Typography
- Buttons
- Forms
- Tables
- Footer
- Spacing
- Horizontal overflow

No unwanted horizontal scrolling.

---

# 23. ACCESSIBILITY

Use:

- Semantic HTML
- Proper labels
- Keyboard-friendly controls
- Sufficient color contrast
- Alt text for meaningful images
- Decorative images appropriately marked
- Proper heading hierarchy
- Accessible buttons and links

Do not rely only on color to communicate important information.

---

# 24. PERFORMANCE

Performance matters.

Avoid:

- Loading huge original images unnecessarily.
- Excessive JavaScript.
- Excessive third-party scripts.
- Unnecessary database queries.
- N+1 queries.
- Repeated expensive queries.

Use:

- Image optimization
- Lazy loading where appropriate
- Responsive images where appropriate
- Query eager loading where needed
- Caching where justified
- Laravel/Vite production builds

Do not introduce caching blindly where it can make CMS updates confusing.

---

# 25. IMAGE RULES

Product and promotional images should be optimized.

Where possible:

- Use WebP/modern formats where compatible.
- Provide meaningful alt text.
- Generate appropriate thumbnails.
- Avoid serving a 5MB image to a small card.
- Preserve aspect ratio.
- Never stretch images.

The admin should be able to manage alt text.

---

# 26. CONTENT RULES

Do not invent:

- Prices
- Outlet addresses
- Phone numbers
- Promotions
- Customer testimonials
- Business claims
- Awards
- Certifications
- Statistics

Use placeholders only when clearly marked as placeholders.

Example:

`[CONTENT TO BE PROVIDED]`

Do not present placeholder content as real information.

---

# 27. WHATSAPP / ORDER CTA

The website may provide CTA buttons such as:

- Order Now
- WhatsApp
- Find Outlet
- View Menu

Do not hardcode an unverified WhatsApp number.

Use website settings.

If no number is configured, hide the WhatsApp CTA or show an appropriate configuration warning in admin instead of generating a fake number.

---

# 28. ROUTING RULES

Public routes must remain clean.

Admin routes should be separated.

Do not expose internal controller/action information through URLs.

Use route names.

Prefer:

`route('products.show', $product)`

over hardcoded URLs.

---

# 29. ERROR HANDLING

Errors must be handled gracefully.

Production users should not see stack traces.

Create appropriate:

- 404 page
- 403 page
- 419 page
- 429 page
- 500 page

The error pages should match the D'Mentai visual identity.

Do not expose:

- SQL errors
- File paths
- Environment variables
- API keys
- Stack traces

in production responses.

---

# 30. LOGGING

Use Laravel logging.

Logs should help diagnose errors.

Never log:

- Passwords
- API secrets
- Authentication tokens
- Private credentials

Do not create noisy logs for normal page views unless specifically required.

---

# 31. TESTING POLICY — VERY IMPORTANT

This project must not be developed using the mindset:

"Code looks correct, therefore it is finished."

A task is only considered complete after verification.

For every meaningful feature:

1. Implement.
2. Format/lint.
3. Run automated tests.
4. Run relevant application checks.
5. Check routes.
6. Check migrations.
7. Check frontend build.
8. Check for obvious runtime errors.
9. Fix errors.
10. Re-run tests after fixes.
11. Only then mark the feature complete.

If a test fails:

DO NOT continue pretending the task is complete.

Fix the failure first.

---

# 32. REQUIRED VERIFICATION COMMANDS

Use commands appropriate to the actual project and installed tooling.

At minimum, regularly use:

`php artisan test`

`php artisan route:list`

`php artisan migrate:status`

`php artisan about`

`composer validate`

`npm run build`

If Laravel Pint is installed:

`vendor/bin/pint --test`

or the appropriate project command.

If static analysis is installed:

Run the configured static analysis command.

Do not invent commands for packages that are not installed.

Before running a command, inspect the project configuration when necessary.

---

# 33. TEST TYPES

Create tests for important behavior.

Examples:

## Public pages

- Home loads.
- Product listing loads.
- Product detail loads.
- Published article loads.
- Draft article does not appear publicly.
- Outlet page loads.

## Admin

- Admin authentication works.
- Unauthorized users cannot access protected resources.
- Authorized users can create/update allowed content.
- Validation prevents invalid records.

## SEO

- Canonical URL exists where expected.
- Meta title exists.
- Meta description exists.
- Sitemap excludes drafts.
- Robots rules behave correctly.

## Security

- Unauthorized destructive actions are rejected.
- Upload validation works.
- Protected admin routes require authentication.

---

# 34. DATABASE SAFETY

Before running destructive database commands:

- Confirm environment.
- Never run destructive commands against production unless explicitly instructed.
- Do not use `migrate:fresh` against production.
- Do not drop production tables casually.
- Do not delete real production data to solve a development problem.

During local development, destructive commands may be used only when appropriate and understood.

---

# 35. GIT RULES

Use Git from the beginning.

Recommended branches:

- `main` = production-ready
- `develop` = development
- feature branches when useful

Do not commit:

- `.env`
- credentials
- API keys
- private certificates
- local machine secrets
- unnecessary generated files

Commit messages should be meaningful.

Examples:

`feat: add product management`
`feat: add SEO settings`
`fix: prevent draft articles from public listing`
`test: add product resource tests`

Do not make giant commits containing unrelated changes.

---

# 36. GITHUB

The project will eventually be connected to GitHub.

Before pushing:

1. Check `git status`.
2. Review changed files.
3. Confirm no secrets are included.
4. Run relevant tests.
5. Build frontend.
6. Commit.
7. Push.

Do not push broken code to the production branch.

---

# 37. CPANEL DEPLOYMENT

Production will eventually use cPanel hosting with Git deployment.

The deployment architecture must respect Laravel's public directory.

The web server/document root should point to:

`/public`

or the hosting environment must be configured so that Laravel's `public/index.php` is the application entry point.

Do not expose:

- `.env`
- `storage`
- `vendor`
- source files
- Git metadata

through the public web root.

Do not move Laravel's `index.php` into the project root merely to make hosting easier without evaluating the security implications.

Production environment:

`APP_ENV=production`

`APP_DEBUG=false`

Use HTTPS.

Configure correct:

- APP_URL
- database credentials
- filesystem
- mail if needed
- cache
- queue if used

---

# 38. DEPLOYMENT SAFETY

Do not automatically run production migrations without understanding the migration.

Before production deployment:

1. Backup database.
2. Pull/checkout correct Git commit.
3. Install dependencies with production flags where appropriate.
4. Build assets.
5. Run migrations only after verifying them.
6. Clear/rebuild relevant caches.
7. Check application health.
8. Check public website.
9. Check admin panel.
10. Check logs.

---

# 39. ENVIRONMENT SEPARATION

Local and production configuration must be separate.

Never copy the local `.env` blindly to production.

Never copy production secrets into GitHub.

Use `.env.example` for documentation only.

---

# 40. CODEX BEHAVIOR RULES

Codex must behave as a careful senior Laravel developer.

## Rule 1 — Inspect before changing

Before modifying existing code:

- Inspect the relevant files.
- Understand existing relationships.
- Check migrations.
- Check routes.
- Check tests.
- Avoid unnecessary rewrites.

## Rule 2 — Minimal safe changes

Prefer the smallest correct change.

Do not rewrite working modules just for style.

## Rule 3 — No silent architecture changes

Do not change:

- Laravel major version
- Admin framework
- Frontend framework
- Database engine
- Authentication architecture

without explicit approval.

## Rule 4 — No destructive actions without confirmation

Never delete an existing project, database, migration, important file, or Git history simply to make the task easier.

## Rule 5 — Verify

After implementation, test it.

## Rule 6 — Fix errors

If verification fails, investigate and fix the root cause.

## Rule 7 — Do not fake success

Never report:

"Done"

when tests are failing.

Never claim:

"Everything works"

unless reasonable verification was actually performed.

## Rule 8 — Explain blockers

If something cannot be verified because a required service is unavailable, say exactly what could not be verified.

Example:

`The application tests pass, but production deployment could not be verified because the hosting server is not connected.`

Do not pretend it was verified.

---

# 41. TASK EXECUTION FORMAT

For each development task, follow:

### STEP A — Understand

Identify:

- Requirement
- Existing implementation
- Dependencies
- Expected behavior

### STEP B — Plan

Briefly identify files/components that need to change.

### STEP C — Implement

Make the change.

### STEP D — Verify

Run relevant tests/checks.

### STEP E — Fix

Fix failures.

### STEP F — Re-test

Run verification again.

### STEP G — Report

Report:

- What changed
- Files changed
- Tests/checks executed
- Result
- Any remaining limitation

---

# 42. DO NOT OVERENGINEER

Do not install packages simply because they exist.

Every dependency should have a reason.

Before installing a package, check:

- Does Laravel/Filament already provide the functionality?
- Is the package compatible with Laravel 12?
- Is it maintained?
- Does it add unnecessary complexity?
- Does it introduce security or upgrade risk?

Prefer first-party Laravel functionality when it is sufficient.

---

# 43. CONTENT EDITOR

For rich text content, use a controlled editor appropriate for Filament.

Sanitize HTML output appropriately.

Do not allow arbitrary unsafe HTML/JavaScript from ordinary content editors.

Never allow an admin content field to become an unrestricted script injection point.

---

# 44. SLUG RULES

Public content slugs must be:

- lowercase
- URL-safe
- readable
- unique

Example:

`dimsum-mentai`

not:

`Dimsum Mentai!!!`

Slugs should not silently change after publication unless there is a clear redirect strategy.

---

# 45. SEO REDIRECTS

If a published URL changes, consider preserving the old URL through a redirect mechanism.

Do not create broken links unnecessarily.

If a redirect module is implemented, avoid redirect chains.

---

# 46. ADMIN UX

Admin forms should be easy for non-developers.

Use:

- Clear labels
- Helpful descriptions
- Validation messages
- Grouped fields
- Tabs/sections where useful
- Image previews
- Search
- Filters
- Sort order
- Status controls
- Confirmation dialogs for destructive actions

The administrator should not need programming knowledge.

---

# 47. EMPTY STATES

If there is no content, show useful empty states.

Example:

`Belum ada produk.`

Do not show broken layouts.

Do not show fake content.

---

# 48. MOBILE ADMIN

The admin panel should remain usable on smaller screens where practical.

Public website responsiveness is mandatory.

---

# 49. ACCESSIBILITY OF IMAGES

Every meaningful image should have appropriate alt text.

Examples:

Good:

`Dimsum Mentai D'Mentai Indonesia`

Bad:

`IMG_1234.jpg`

Decorative images should not unnecessarily add noise to screen readers.

---

# 50. BRAND CONTENT HIERARCHY

The site should make it immediately clear:

1. What D'Mentai is.
2. What products are offered.
3. Why customers should care.
4. Where customers can find D'Mentai.
5. How customers can order/contact D'Mentai.

Do not bury the main CTA.

---

# 51. FUTURE EXTENSIBILITY

The architecture should leave room for future features such as:

- Online ordering
- WhatsApp ordering
- Customer accounts
- Loyalty program
- Franchise information
- Online payment
- Delivery integration
- Multiple languages
- More advanced analytics

However:

DO NOT build these features now unless requested.

Build clean foundations without implementing unnecessary future functionality.

---

# 52. SEO CONTENT STRATEGY

The article system should support long-term content creation.

Potential article categories:

- Dimsum
- Gyoza
- Food
- Tips
- D'Mentai
- Promo
- Outlet

Categories can be changed later.

Do not hardcode these categories as final business requirements.

---

# 53. PRODUCTION CHECKLIST

Before declaring production ready:

## Application

- [ ] Laravel configured correctly
- [ ] APP_ENV production
- [ ] APP_DEBUG false
- [ ] APP_URL correct
- [ ] Database configured
- [ ] Storage configured
- [ ] Cache configured
- [ ] Queue configured if needed

## Security

- [ ] No secrets in Git
- [ ] Admin protected
- [ ] Upload validation enabled
- [ ] Authorization checked
- [ ] HTTPS enabled
- [ ] Debug disabled
- [ ] Sensitive files inaccessible

## Website

- [ ] Homepage works
- [ ] Menu works
- [ ] Product pages work
- [ ] Promo works
- [ ] Outlet works
- [ ] Articles work
- [ ] Contact works
- [ ] 404 page works
- [ ] Mobile layout checked
- [ ] Desktop layout checked

## SEO

- [ ] Title tags
- [ ] Meta descriptions
- [ ] Canonical URLs
- [ ] Sitemap
- [ ] robots.txt
- [ ] Open Graph
- [ ] Alt text
- [ ] Search Console verification

## Analytics

- [ ] Visitor tracking checked
- [ ] Google Analytics configured if used
- [ ] Dashboard statistics verified

## Deployment

- [ ] GitHub repository clean
- [ ] Production branch tested
- [ ] Database backup
- [ ] Migration reviewed
- [ ] Assets built
- [ ] Domain connected
- [ ] SSL enabled

---

# 54. IMPORTANT: CURRENT PROJECT OWNER PREFERENCES

The project owner prefers a safe, step-by-step approach when configuration can affect connectivity or production.

Therefore:

- Do not make risky infrastructure changes silently.
- Do not reset servers or hosting configuration unnecessarily.
- Do not change DNS without instruction.
- Do not change production database credentials without instruction.
- Do not reset Git repositories without instruction.
- Do not delete working code to start over.
- When an operation could disconnect the application, explain it first.

For local coding tasks that are safe and reversible, Codex may proceed automatically.

---

# 55. FIRST PROJECT CREATION TASK

When instructed to start the project from scratch:

1. Inspect `D:\xampp\htdocs`.
2. Confirm whether `dmentaiindonesia` exists.
3. If it does not exist, create the Laravel 12 project.
4. Confirm PHP version.
5. Confirm Composer version.
6. Confirm Node/npm availability.
7. Create/configure the Laravel project.
8. Configure MariaDB connection using local environment values.
9. Install/configure Filament 5 compatible with Laravel 12.
10. Configure Blade/Livewire/Tailwind/Vite as appropriate.
11. Create the initial Git repository if needed.
12. Create `.env.example`.
13. Create the initial README.
14. Preserve this `CODEX.md`.
15. Run migrations.
16. Run tests.
17. Build frontend assets.
18. Verify the application boots.
19. Only after foundation is healthy, continue to the next phase.

DO NOT create the entire CMS and frontend in one uncontrolled operation.

Build the foundation first.

---

# 56. DEVELOPMENT PHASES

Follow this order.

## Phase 1 — Foundation

- Laravel 12
- MariaDB
- Git
- Filament
- Authentication
- Admin panel
- Basic frontend shell
- Testing setup

## Phase 2 — Core CMS

- Site settings
- Media
- Pages
- SEO

## Phase 3 — Business Content

- Product categories
- Products
- Promotions
- Outlets
- Gallery
- Testimonials
- FAQ
- Articles

## Phase 4 — Public Website

- Homepage
- Menu
- Product detail
- Promotions
- Outlet
- Gallery
- Articles
- About
- FAQ
- Contact

## Phase 5 — Analytics

- Visitor tracking
- Dashboard
- Google Analytics integration
- Search Console integration

## Phase 6 — Security & Optimization

- Authorization
- Activity log
- Upload security
- Rate limiting
- Caching where appropriate
- Image optimization
- Performance review

## Phase 7 — Testing

- Unit tests
- Feature tests
- Public route tests
- Admin tests
- SEO tests
- Security tests
- Build verification

## Phase 8 — Production

- GitHub
- cPanel Git deployment
- Database
- Domain
- SSL
- Production configuration
- Final smoke tests

---

# 57. DEFINITION OF DONE

A feature is NOT DONE merely because code has been written.

A feature is DONE when:

- Code is implemented.
- Relevant tests exist or existing tests cover it.
- Tests pass.
- Relevant route works.
- Database migration works.
- Frontend build succeeds if frontend assets changed.
- No obvious runtime error remains.
- Authorization is correct.
- Validation is correct.
- Responsive behavior has been considered.
- SEO has been considered if the feature is public.
- Documentation is updated when necessary.

---

# 58. FINAL PRINCIPLE

The priority order for this project is:

1. Correctness
2. Security
3. Maintainability
4. User experience
5. Performance
6. SEO
7. Visual polish

Never sacrifice security or correctness merely to make development faster.

Never hide errors.

Never fake successful verification.

Build D'Mentai Indonesia as a real production website, not merely as a demo.

---

# 59. PROJECT OWNER APPROVAL RULE

If Codex encounters an architectural decision that materially changes the project, stop and ask for approval.

Examples:

- Changing Laravel major version
- Changing database engine
- Replacing Filament
- Replacing Livewire
- Introducing React/Vue
- Adding a major third-party service
- Changing deployment architecture
- Destructive database changes
- Removing an existing module
- Changing authentication architecture

For ordinary implementation details that remain inside this specification, Codex may proceed.

END OF CODEX.md
