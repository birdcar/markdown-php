# Session Progress Log

## Current State

**Last Updated:** 2026-05-29
**Active Feature:** feat-002 (Cut a release) is the next recurring task; pipeline (feat-001) is shipped.

## Status

### What's Done

- [x] **Packagist publishing pipeline (feat-001)** — release-please + snapshot split + non-EOL CI matrix. Shipped **v0.1.0** to all three packages on Packagist.
- [x] Version matrix aligned to non-EOL (feat-003): PHP ^8.2 / Laravel ^12|^13; `symfony/yaml` widened to `^7.2 || ^8.0`.
- [x] Mirror repos created + seeded; `MONOREPO_SPLIT_TOKEN` set; `workflow_dispatch` + main-ref guard on the release workflow.

### What's Next

1. **feat-002** — cut the next release when feat/fix commits accumulate (merge the release-please Release PR).
2. **feat-005** (optional) — branch protection on `main`; move the split PAT into a main-scoped GitHub Environment.
3. **feat-004** (later) — promote to v1.0.0 when the BFM API stabilizes.

## Blockers / Risks

- [ ] Confirm the **Packagist GitHub App** is installed on all three repos so future tags auto-update (otherwise manual "Update" needed each release).

## Decisions Made

- **Lockstep versioning + `self.version`** for inter-package deps (not independent versioning) — see `docs/ideation/packagist-publishing/contract.md`.
- **Instruction files**: both `CLAUDE.md` (canonical harness) and `AGENTS.md` (pointer).

## Evidence of Completion

- [x] `composer require birdcar/markdown-filament` in a clean project resolved filament → laravel → php at 0.1.0 from Packagist.
- [x] CI matrix green (PHP 8.2–8.5 × Laravel 12–13 × lowest/stable + Filament v4).

## Notes for Next Session

The release pipeline's non-obvious failure modes are captured as "Publishing rules" in `CLAUDE.md` and in project memory. Read them before any release work. Full runbook: `RELEASING.md`.
