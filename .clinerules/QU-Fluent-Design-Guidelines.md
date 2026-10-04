# QU × Fluent Design Guidelines
### "Doha" theme — for Laravel (Blade / Livewire / Filament) admin & portal UI

This document is a portable design reference. Paste it into any model (DeepSeek, GPT, etc.) alongside a UI request and it should reproduce a consistent visual language: Qatar University's maroon/sand/gold identity, executed with Microsoft Fluent UI's structural conventions (acrylic surfaces, depth/elevation scale, tight corner radii, reveal-highlight interactions).

---

## 1. Color tokens

Use these exact names and hex values. Never substitute stock Tailwind colors (indigo-600, gray-100, etc.).

```css
/* Brand — maroon (primary) */
--color-brand-50:  #fbeef1;
--color-brand-100: #f3d2da;
--color-brand-200: #e6a5b6;
--color-brand-300: #d3738f;
--color-brand-400: #b8496b;
--color-brand-500: #8d1b3d;   /* primary */
--color-brand-600: #7a1636;   /* hover */
--color-brand-700: #63102b;
--color-brand-800: #4c0c21;
--color-brand-900: #350818;

/* Sand/dune — secondary warm neutral */
--color-sand-50:  #faf7f0;
--color-sand-100: #f2ead6;
--color-sand-200: #e4d3ac;
--color-sand-300: #d3b57c;
--color-sand-400: #c39c58;
--color-sand-500: #ab8140;
--color-sand-600: #8c6733;
--color-sand-700: #6d4f29;
--color-sand-800: #503a1e;
--color-sand-900: #362715;

/* Gold — accent only, sparing use */
--color-gold-400: #e3b04b;
--color-gold-500: #cf9a2f;
--color-gold-600: #a97b22;

/* Ink — text & neutral surfaces */
--color-ink-50:  #f7f7f8;
--color-ink-100: #eeedf0;
--color-ink-200: #d8d6dc;
--color-ink-300: #b4b0ba;
--color-ink-400: #8b8592;
--color-ink-500: #675f6e;
--color-ink-600: #4c4553;
--color-ink-700: #38333e;
--color-ink-800: #241f2a;
--color-ink-900: #16131a;

/* Semantic */
--color-success: #1f8a5f;
--color-warning: #cf9a2f;
--color-danger:  #b3261e;
--color-info:    #2563a8;
```

**Rules:**
- Primary actions, active nav, links → `brand-500` resting / `brand-600` hover.
- Backgrounds → `white` or `sand-50` for section alternation. Never gray-100.
- Gold → accents only: stat trend chips, badges, icon fills, one highlight element. **Never a large fill area** — it reads gaudy at scale.
- Dark mode → build a deliberate second palette on `ink-900`/`ink-800` surfaces with `brand-400`/`gold-400` as lighter-on-dark accents. Don't just invert the light palette.

---

## 2. Typography

- **UI/body font:** `Inter` (fallback `Segoe UI Variable`, `Segoe UI`, `ui-sans-serif`, `system-ui`, `sans-serif`).
- **Arabic-inclusive projects:** pair with `IBM Plex Sans Arabic` or `Cairo`.
- **Display/hero numerals only:** `Fraunces` or `Source Serif 4` at large sizes — gives an "institutional gravitas" feel. Never use for body text or UI chrome.

Type scale:
| Role | Classes/spec |
|---|---|
| Display/hero | 48–60px, weight 600, tight tracking |
| Section heading | 24–30px, weight 600 |
| Card/component title | 18px, weight 500 |
| Body | 13–14px, `ink-600` |
| Micro/meta/label | 11px, uppercase, letter-spacing .06–.08em, `ink-400` |

---

## 3. Fluent UI structural system

This is what distinguishes the look from a generic SaaS dashboard.

- **Corner radius scale** — tight, not rounded: `4px` (small controls), `6px` (buttons/inputs), `8px` (cards/panels). Avoid `12px+`/pill-everything; that reads consumer-app, not enterprise/institutional.
- **Depth/elevation** — layered box-shadows, not one soft blur:
  ```css
  --fluent-depth-2:  0 1px 2px rgba(22,19,26,.07), 0 0px 1px rgba(22,19,26,.06);
  --fluent-depth-4:  0 2px 4px rgba(22,19,26,.09), 0 0px 2px rgba(22,19,26,.07);
  --fluent-depth-8:  0 4px 8px rgba(22,19,26,.12), 0 0px 2px rgba(22,19,26,.08);
  --fluent-depth-16: 0 8px 16px rgba(22,19,26,.16), 0 0px 2px rgba(22,19,26,.10);
  ```
  Resting cards use `depth-2`; hover/elevated states use `depth-4`–`depth-8`; modals/flyouts use `depth-16`.
- **Acrylic surfaces** — top command bars and flyout panels use translucency over blur, not flat white:
  ```css
  background: rgba(255,255,255,.72);
  backdrop-filter: blur(20px) saturate(1.4);
  -webkit-backdrop-filter: blur(20px) saturate(1.4);
  ```
  On brand-colored surfaces (e.g. a sidebar), use a brand-tinted acrylic: `rgba(99,16,43,.82)` over a maroon gradient.
- **Mica texture (optional, sidebars only)** — a barely-visible noise/dot pattern over a gradient surface, applied via a `::before` pseudo-element radial-gradient at 1–2px dot size, ~4–5% opacity. Never on content areas — chrome only.
- **Reveal-highlight interaction** — nav items and icon buttons get a subtle background + 1px border on hover (not a filled block), and the active state gets a 3px accent rail (gold) on the leading edge rather than a full-color fill.

---

## 4. Layout & spacing

- Sidebar: fixed ~260–280px, brand gradient (`brand-700 → brand-800 → brand-900`), acrylic mica texture, nav grouped under uppercase micro-labels.
- Command bar: 56–64px tall, acrylic, sticky top, breadcrumb left / search + actions right.
- Dashboard content: `24–28px` horizontal padding, stat-card grid (`4-up` desktop, `2-up` mobile) above a data table/panel.
- Cards/panels: `16–18px` internal padding, 1px `ink-100` border, `depth-2` shadow.
- Section padding (marketing/landing pages only): generous, `64–96px` vertical rhythm.

## 5. Motion

Minimal and functional. Fade/slide-up on scroll-reveal for marketing sections (150–250ms). No bouncy easing on dashboards. Data interactions get instant/near-instant feedback (loading states), not decorative animation.

---

## 6. Do / Don't

**Do:**
- Use the token names above verbatim so output is re-themeable.
- Keep gold restricted to small accent moments.
- Use tight Fluent radii and layered shadows.
- Add acrylic blur only to chrome (bars, flyouts), never body content.

**Don't:**
- Default to indigo/violet accents, gray-100 backgrounds, or `rounded-2xl`+ card corners.
- Fill large areas with gold.
- Use a single flat drop-shadow instead of the layered depth scale.
- Mix in unrelated display fonts — display serif is for large numerals only.

---

## 7. Reference component recipes

### Sidebar nav item (Blade/HTML)
```html
<div class="nav-item active">
  <svg class="icon">…</svg>
  Dashboard
</div>
```
```css
.nav-item {
  display: flex; align-items: center; gap: 11px;
  padding: 9px 12px; border-radius: 6px;
  font-size: 13.5px; font-weight: 500; color: rgba(250,247,240,.82);
  border: 1px solid transparent;
}
.nav-item:hover { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.08); }
.nav-item.active { background: rgba(255,255,255,.1); color: #fff; border-color: rgba(255,255,255,.14); }
.nav-item.active::before {
  content: ""; position: absolute; left: -14px; top: 6px; bottom: 6px; width: 3px;
  background: var(--color-gold-400); border-radius: 0 3px 3px 0;
}
```

### Stat card
```css
.stat-card {
  background: #fff; border: 1px solid var(--color-ink-100);
  border-radius: 8px; padding: 16px 18px;
  box-shadow: var(--fluent-depth-2);
}
.stat-card:hover { box-shadow: var(--fluent-depth-8); border-color: var(--color-ink-200); }
```

### Primary button
```css
.btn-primary {
  background: var(--color-brand-500); color: #fff;
  font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 6px;
  border: 1px solid var(--color-brand-600); box-shadow: var(--fluent-depth-2);
}
.btn-primary:hover { background: var(--color-brand-600); box-shadow: var(--fluent-depth-4); }
```

### Status pill
```css
.pill { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 600; padding: 3px 9px; border-radius: 999px; }
.pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; }
/* variants: .review (gold), .accepted (success), .docs (info), .waitlist (ink) */
```

### Laravel stack notes
- **Filament:** set panel primary color via `->colors([Color::hex('#8d1b3d')])`; theme badges/status colors to the semantic tokens above.
- **Livewire + Flux:** override component color via `class="!bg-brand-500 hover:!bg-brand-600"` rather than forking component markup.
- **Tailwind v4:** define all tokens above inside an `@theme` block in `resources/css/app.css` so utility classes (`bg-brand-500`, `text-ink-600`, etc.) are generated automatically.
- **RTL/bilingual:** use logical properties (`ps-4`/`pe-4`, `text-start`/`text-end`) so layout mirrors correctly under `dir="rtl"`.

---
*Token values above are original, built to evoke Qatar University's public maroon/gold identity — not extracted from QU's official stylesheet. Verify against QU's official brand guidelines PDF (qu.edu.qa/en-us/Offices/cpr/Pages/branding-guidelines.aspx) before shipping anything QU-affiliated externally.*
