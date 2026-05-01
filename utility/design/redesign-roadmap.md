# Spikster Redesign Roadmap

## Goal

Build a consistent admin UI system before redesigning individual screens, so visual work is not blocked by token drift, duplicate components, or theme inconsistencies.

## What To Fix Before Redesign

### 1. Stabilize the design foundation

- Define semantic color roles instead of using raw Tailwind colors directly in views.
- Normalize typography, spacing, radius, shadow, border, and focus styles.
- Decide whether Spikster is dark-first or supports full light and dark themes.
- Remove styling logic that depends on dynamic Tailwind class names without a safelist.

### 2. Consolidate shared UI primitives

- Standardize button variants and stop mixing custom button classes with raw utility-only buttons.
- Standardize form fields, validation states, labels, help text, cards, alerts, badges, tables, empty states, and page headers.
- Ensure login, dashboard, and settings screens all consume the same primitives.

### 3. Separate layout concerns

- Treat shell layout, page layout, and content modules as separate layers.
- Keep sidebar, topbar, and page container rules centralized.
- Avoid embedding a separate visual language inside individual screens.

## Current Risks In This Codebase

- Theme behavior is inconsistent: the page shell is effectively always dark while page content supports dark mode toggling.
- Tailwind tokens exist, but components and screens still use many raw `blue`, `purple`, `red`, `green`, and `yellow` classes directly.
- Some views bypass the component layer entirely, especially the login page and parts of the dashboard.
- Dynamic classes in navigation badges are fragile for production builds because Tailwind cannot statically discover every generated class.

## Execution Order

### Phase 0. UI audit

- Inventory all reusable UI patterns in `resources/views/components`.
- Capture the main app surfaces: auth, dashboard, list/detail screens, settings, tables, forms, and empty states.
- Mark each surface as `keep`, `refactor`, or `replace`.

### Phase 1. Foundation cleanup

- Replace direct color usage with semantic tokens.
- Introduce a small theme vocabulary: `surface`, `surface-muted`, `border`, `text`, `text-muted`, `accent`, `success`, `warning`, `danger`.
- Add a Tailwind safelist or convert dynamic badge colors to mapped classes.
- Make dark mode explicit: full dual theme or dark-only shell with dark-tinted content.

### Phase 2. Primitive refactor

- Refactor button, input, select, card, alert, badge, table wrapper, and page header into a single consistent API.
- Remove duplicate styling patterns from Blade views.
- Migrate login and dashboard first because they currently define the visual impression of the product.

### Phase 3. Shell redesign

- Redesign sidebar, topbar, breadcrumb/title area, and content container spacing.
- Validate mobile sidebar behavior, topbar density, and theme readability.
- Ensure the shell reads as one system before touching deep feature screens.

### Phase 4. Screen rollout

- Redesign screens in this order: dashboard, servers, sites, domains, settings, modules.
- For each screen, replace local styling with shared primitives before adding new visual treatments.
- Keep data density appropriate for an admin product; do not optimize for decoration over scanability.

## First Technical Tasks

1. Audit and normalize `tailwind.config.js` and `resources/css/app.css` so tokens and components reflect the intended system.
2. Replace dynamic badge color strings in the sidebar with an explicit color map.
3. Refactor `x-button` and related button components to use the same semantic palette everywhere.
4. Rebuild the login page with shared form and button primitives.
5. Rework the dashboard cards so they follow the same spacing, color, and state rules as the rest of the app.

## Design Constraints

- Keep the product admin-first: clear hierarchy, fast scanning, strong table readability.
- Prefer restrained motion and meaningful emphasis over decorative gradients everywhere.
- Use one primary accent system, not multiple competing accents.
- Preserve visual contrast and keyboard focus visibility as part of the redesign, not as cleanup afterward.

## Definition Of Ready For Redesign

Redesign work should start only once these are true:

- Theme strategy is decided.
- Shared primitives are stable.
- Token usage is mostly semantic.
- Dynamic Tailwind styling risks are removed.
- Login and dashboard no longer use one-off styling patterns.

## Recommended Immediate Next Step

Start with the foundation cleanup and primitive refactor, not with page mockups. The highest-leverage first implementation slice is:

1. Fix Tailwind token usage and dynamic color safety.
2. Normalize buttons, inputs, cards, and alerts.
3. Migrate login and dashboard onto those primitives.
4. Redesign the app shell only after those pieces are stable.