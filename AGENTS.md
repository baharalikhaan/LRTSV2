# Development Guidelines — RTS

## Version History Rule

**Every key feature added or bug fixed MUST be documented in the version history.**

When making changes to the application:

1. **Identify** if the change is a new feature, enhancement, or bug fix.
2. **Add a changelog entry** to `resources/views/layouts/app.blade.php` in the `$versionHistory` array (rendered in the footer version-history modal) under the current version's `changes` array.
3. **Update the version number** if shipping a new release:
   - `resources/views/layouts/app.blade.php` — `$versionHistory` array (add new entry) and footer version text (the `vX.Y.Z` link)

Note: the About page (`/about`) was removed along with `AboutController::index()`; the changelog now lives only in the layout's `$versionHistory` array.

### What counts as a "key change" worthy of the version history:

- New feature or page
- Redesign of existing UI (dashboard, forms, tables)
- Role-based access changes (new restrictions, new permissions)
- Bug fix that affects user-facing behavior
- Security fix
- Removal of deprecated feature or page

### What does NOT need a changelog entry:

- Code refactoring with no behavior change
- CSS-only tweaks (spacing, colors) unless user-visible
- Internal comment updates

### Example entry format:

```php
[
    'version'   => '2.1.0',
    'date'      => 'August 2026',
    'tagline'   => 'Role-Specific Dashboards & Access Control',
    'changes'   => [
        'Redesigned Admin dashboard — KPI cards, donut chart, active/inactive research calls',
        'Admin restricted from LPI and Reviewer operational pages',
    ],
],
```
