# SKILL: QU × Fluent UI Design System

Use this skill whenever asked to design, build, or restyle UI for this Laravel application — dashboards, admin panels, forms, tables, landing/marketing pages, or any Blade/Livewire/Filament view. Trigger on requests like "make this page," "style this component," "build a dashboard/admin panel," or "match our design system."

## Before generating any UI, load these rules as your source of truth:

**Reference doc:** `QU-Fluent-Design-Guidelines.md` (attach/paste alongside this skill). It contains the full token set, type scale, Fluent structural rules, and component recipes. Read it before writing any CSS or markup — do not improvise colors, radii, or shadows from general knowledge.

## Non-negotiable rules

1. **Colors — token names only.** Use `brand-*` (maroon), `sand-*` (warm neutral), `gold-*` (accent), `ink-*` (text/neutral) exactly as defined in the guidelines doc. Never introduce indigo, violet, or generic Tailwind gray-100/blue-600 defaults.
2. **Gold is an accent, never a fill.** Small badges, trend chips, active-state rails, icon fills only. If gold covers more than ~10% of any surface, stop and reduce it.
3. **Fluent radius scale.** 4px (small controls) / 6px (buttons, inputs) / 8px (cards, panels). No radius above 8px unless explicitly asked for a pill/avatar.
4. **Fluent depth, not flat shadow.** Always use the layered `--fluent-depth-2/4/8/16` shadow tokens from the guidelines doc. Never a single `box-shadow: 0 4px 6px rgba(0,0,0,.1)`-style flat shadow.
5. **Acrylic on chrome only.** Command bars, sidebars, flyouts, modals may use `backdrop-filter: blur(20px) saturate(1.4)` over a translucent surface. Never apply blur/acrylic to body content or cards.
6. **Typography.** `Inter` for UI/body (pair with `IBM Plex Sans Arabic`/`Cairo` if bilingual). Reserve `Fraunces`/`Source Serif 4` for large display numerals only — never body text, never buttons.
7. **Reveal-highlight interaction pattern.** Hover states = subtle background tint + 1px border, not a solid fill block. Active nav state = accent rail (3px, gold) on the leading edge, not a filled pill.
8. **Consistency check before output.** Before returning code, verify: (a) every color is a named token, (b) every shadow is a depth token, (c) radii are 4/6/8px, (d) gold usage is minor, (e) any blur is on chrome only. If any check fails, revise before responding.

## Output format

- Produce complete, working code (HTML/CSS or Blade + Tailwind `@theme` tokens as appropriate to the stack) — not partial snippets requiring the user to guess integration points.
- Reuse the CSS variable names verbatim so multiple generations stay visually consistent across sessions.
- When building a new page type not covered in the reference doc's component recipes (§7), extrapolate using the same token/radius/depth/acrylic rules rather than inventing a new visual language.
- Briefly state which tokens/components you used, so drift is easy to catch across sessions.

## What "wrong" looks like (reject these outputs)

- Flat single-layer box-shadows instead of the depth scale.
- Rounded-2xl / fully pill-shaped cards.
- Gold as a hero background or large panel fill.
- Indigo/blue accent colors substituted "because they look modern."
- Blur/glass effect applied to a data table or form body instead of just the top bar/sidebar.
- Generic sans-serif stack instead of Inter/Segoe UI Variable fallback chain.

## How to use this with DeepSeek

Paste both files (`QU-Fluent-UI-SKILL.md` as the instruction/system layer, `QU-Fluent-Design-Guidelines.md` as reference material) at the start of a session, or store them as a persistent system prompt / project instructions if your DeepSeek interface supports it. Re-paste them at the start of any new session — without a system-prompt or fine-tuning mechanism, the model will not remember this style between sessions on its own.
