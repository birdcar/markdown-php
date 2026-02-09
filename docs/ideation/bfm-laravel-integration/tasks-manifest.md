# Tasks Manifest

**Created:** 2026-02-09
**Project:** bfm-laravel-integration

## Quick Start

```bash
# Start fresh session for implementation
claude
```

Then run: `/execute-spec docs/ideation/bfm-laravel-integration/spec-phase-1.md`

## Phases

| Phase | Status | Spec File | Description |
|-------|--------|-----------|-------------|
| 1 | pending | spec-phase-1.md | Monorepo structure + `birdcar/markdown-laravel` package |
| 2 | blocked (by 1) | spec-phase-2.md | `birdcar/markdown-filament` with server-side preview editor |
| 3 | blocked (by 1, 2) | spec-phase-3.md | Default CSS stylesheet, light/dark mode, Filament styles |

## Artifacts

```
docs/ideation/bfm-laravel-integration/
├── contract.md          # Approved contract
├── prd-phase-1.md       # Phase 1 PRD (monorepo + Laravel)
├── prd-phase-2.md       # Phase 2 PRD (Filament)
├── prd-phase-3.md       # Phase 3 PRD (CSS/styling)
├── spec-phase-1.md      # Phase 1 implementation spec
├── spec-phase-2.md      # Phase 2 implementation spec
├── spec-phase-3.md      # Phase 3 implementation spec
└── tasks-manifest.md    # This file
```

## Implementation Order

1. **Phase 1** (M effort) — Start here. Creates the monorepo structure and the foundational Laravel package.
2. **Phase 2** (L effort) — Filament integration. Depends on Phase 1's service provider and Str macros.
3. **Phase 3** (S effort) — CSS styling. Depends on both packages being stable to know the full set of HTML elements to style.

## Notes

- Each phase is independently committable
- Run tests after each phase: `composer test:all`
- Phases 1 and 3 could potentially be parallelized if the CSS class names are considered stable (they are defined by the core library, not the integration packages)
