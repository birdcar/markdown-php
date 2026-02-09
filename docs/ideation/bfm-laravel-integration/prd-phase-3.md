# PRD: BFM Laravel Integration - Phase 3

**Contract**: ./contract.md
**Phase**: 3 of 3
**Focus**: Default CSS/styling, publishable assets, and cross-package polish

## Phase Overview

This phase provides the visual layer that makes BFM output look good out of the box. Without styling, BFM renders semantic HTML with BEM classes but no visual treatment — task markers have no color coding, callouts have no background/border, mentions have no highlight, and embeds have no frame.

This is sequenced last because the CSS must cover all output elements produced by both the core library and the Filament components. With Phases 1 and 2 stable, we know exactly which HTML elements and class names need styling.

After this phase, a developer adding `@bfmStyles` to their layout gets a polished rendering of all BFM syntax. Power users can publish and customize the stylesheet.

## User Stories

1. As a Laravel developer, I want `@bfmStyles` to give me sensible default styling so that BFM output looks polished without writing custom CSS.
2. As a developer, I want to publish and override the default BFM stylesheet so that I can match my application's design system.
3. As a Filament developer, I want the BfmEditor preview and display components to render with the same styles as the frontend so that content looks consistent between admin and public views.
4. As a developer, I want the styles to work with both light and dark mode so that they adapt to the user's system or app preference.

## Functional Requirements

### Default Stylesheet

- **FR-3.1**: Ship a CSS file covering all BFM output classes: `.task-item`, `.task-marker`, `.task-marker__icon`, `.task-mod`, `.mention`, `.callout`, `.callout__header`, `.callout__body`, `.embed`, `.embed__link`, `.embed__caption`
- **FR-3.2**: Task markers are color-coded by state (e.g., completed=green, high-priority=red, scheduled=blue, migrated=purple, irrelevant=gray)
- **FR-3.3**: Callouts have visual treatment per type (info=blue, warning=amber, error=red, tip=green, note=gray) with left border accent and subtle background
- **FR-3.4**: Mentions have a distinct visual indicator (e.g., subtle background highlight with rounded pill shape)
- **FR-3.5**: Embeds render in a figure frame with caption styling
- **FR-3.6**: All styles support light and dark mode via `prefers-color-scheme` media query and/or CSS custom properties for manual toggling

### Asset Distribution

- **FR-3.7**: `@bfmStyles` Blade directive injects a `<link>` tag pointing to a published CSS file, or falls back to inline `<style>` if assets aren't published
- **FR-3.8**: `php artisan vendor:publish --tag=bfm-assets` publishes the CSS file to `public/vendor/bfm/bfm.css`
- **FR-3.9**: Optionally provide a Tailwind CSS plugin or utility classes for projects using Tailwind (as a separate, opt-in file)

### Filament Integration

- **FR-3.10**: BfmEditor preview panel and display components (`BfmTextColumn`, `BfmTextEntry`) automatically include BFM styles in the Filament admin panel
- **FR-3.11**: Filament styles should respect the admin panel's dark mode setting

### Documentation Examples

- **FR-3.12**: Include a kitchen-sink HTML fixture showing all styled elements for visual regression testing

## Non-Functional Requirements

- **NFR-3.1**: CSS file should be under 10KB minified (no heavy framework dependency)
- **NFR-3.2**: Styles must not conflict with common CSS frameworks (Tailwind, Bootstrap) — use specific BFM class prefixes
- **NFR-3.3**: CSS custom properties allow theming without editing the stylesheet directly
- **NFR-3.4**: Accessible color contrast ratios (WCAG AA minimum) for all text-on-background combinations

## Dependencies

### Prerequisites

- Phase 1 complete: `@bfmStyles` directive registered, asset publishing infrastructure
- Phase 2 complete: Filament components finalized (need to know which elements to style)
- Final HTML output for all BFM extensions confirmed (class names, data attributes, element structure)

### Outputs for Next Phase

- N/A (final phase). Future: Tiptap extension package would reuse these styles client-side.

## Acceptance Criteria

- [ ] CSS file exists at `packages/laravel/resources/css/bfm.css` (or equivalent)
- [ ] `@bfmStyles` in a Blade template produces valid CSS output covering all BFM elements
- [ ] `php artisan vendor:publish --tag=bfm-assets` publishes CSS to `public/vendor/bfm/`
- [ ] Task markers are visually differentiated by state (7 distinct visual treatments)
- [ ] Callout blocks have type-specific visual treatment (at least 5 types)
- [ ] Mentions have a visible inline highlight
- [ ] Embeds have a figure frame with caption
- [ ] Light mode and dark mode both produce readable, accessible output
- [ ] Color contrast meets WCAG AA (4.5:1 for normal text, 3:1 for large text)
- [ ] CSS file is under 10KB minified
- [ ] No class name conflicts with Tailwind or Bootstrap default classes
- [ ] Filament preview panel renders with BFM styles applied
- [ ] Kitchen-sink fixture renders correctly with all styles applied

## Open Questions

- Should we ship a Tailwind plugin that integrates with `@apply` and Tailwind's color system, or just provide a standalone CSS file that works everywhere? Standalone is simpler; Tailwind plugin is nicer for Tailwind projects.
- Should styles be scoped to a `.bfm-content` wrapper class to prevent leaking, or apply globally to the BFM class names? Scoping is safer but requires users to add a wrapper.

---

*Review this PRD and provide feedback before spec generation.*
