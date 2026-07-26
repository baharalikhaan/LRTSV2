---
name: laravel-enterprise-ui
description: Use for Laravel admin panel UI, Livewire/Filament components, Inertia customer-portal UI, navigation, layout, dashboard shells, form/table patterns, access-aware UI, and design system application. Trigger for Laravel + UI/UX work, admin panels, dashboards, "modern"/"enterprise" Laravel apps, or a maroon/sand/gold "QU-inspired" theme request.
---
# Laravel Enterprise UI Skill
Use this skill for `app/Filament`, `app/Livewire`, and Inertia customer-facing UI changes.

## Required Docs
- `app/Filament/` for admin panel resources/pages/widgets
- `app/Livewire/` + `resources/views/livewire/` for custom Livewire components
- `resources/js/Pages/` for Inertia customer-facing pages (if stack uses Inertia)
- `resources/css/app.css` for design tokens (`@theme` block)
- Relevant module skill for module-specific business logic

## Design Baseline
- Preserve the "Doha" visual language: brand maroon 500 `#8d1b3d`, dark `#63102b` (brand-700), sand/dune neutrals `#faf7f0`-`#362715`, gold accent `#cf9a2f` used sparingly.
- Use Inter (Latin) paired with IBM Plex Sans Arabic/Cairo where the app is bilingual.
- Preserve existing layout language rather than introducing generic templates - don't default to indigo-600/gray-100 stock Tailwind palettes.
- Ensure desktop and mobile behavior both work; ship dark-mode tokens deliberately, not as a naive invert.

## Admin Panel Rules (Livewire/Filament)
- Respect existing auth/authorization: Laravel Gates/Policies, spatie/laravel-permission roles, and any custom middleware guarding panel access.
- Use Filament's own resource/policy wiring for access-aware UI - don't hand-roll permission checks inside Blade views when a Policy already exists.
- Use Filament panel ->colors([...]) to apply brand tokens rather than overriding component CSS ad hoc.
- Use URL query string / Livewire #[Url] attributes for tab and filter state where an established pattern exists.
- Reuse the shared dashboard shell (sidebar, topbar, search, notifications, avatar menu) - don't rebuild layout chrome per-page.

## Workflow/Transactional UI Rules
- For multi-step or stateful business processes (approvals, applications, review stages), use a shared stages/history/action panel pattern rather than one-off per-module implementations.
- Do not duplicate action-form logic (approve/reject/remarks/attachments) across module-specific components - extract to a shared Livewire/Blade component.
- Render dynamic required fields, remarks, attachments, and confirmation/PIN-style modals through standard shared components, not inline per-page markup.

## Customer-Facing / Portal UI Rules (Inertia, if used)
- Preserve wizard-style draft/resume behavior for multi-step forms.
- Surface clear status: progress stage, fees/costs, required documents, and backend processing state.
- Keep role-based UI assumptions (e.g. which fields are locked/editable per role) visible and consistent with backend authorization.
- Handle locked/unlocked field corrections precisely - don't silently allow edits the backend will reject.

## Testing Expectations
- Test loading, empty, success, validation-error, unauthorized, and server-error states for every data-bound component.
- Assert real data from Eloquent/API responses renders in the DOM (Pest/Livewire component tests), not just static markup.
- Test mobile nav/drawer behavior and direct deep links to nested routes.
- Verify no blank/broken states from Livewire lazy loading, Filament relation managers, or stale Vite builds - run npm run build before asserting production behavior.

## Related Skills
- references/design-system.md - full token set (colors, type scale, spacing, motion, dark mode)
- references/stack-guide.md - Livewire 4 + Filament 5 + Flux vs. Inertia 3 + React decision matrix and install commands
- references/component-patterns.md - nav, hero/stat-strip, dashboard shell, tables, forms, card grids (Blade+Flux and Inertia+React variants)
- frontend-design skill (if available) - general layout/typography judgment to pair with this skill's Laravel-specific conventions
