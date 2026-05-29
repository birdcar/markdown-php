# Session Handoff

## Current Objective

- Goal: Reliable lockstep publishing of the three BFM packages to Packagist.
- Current status: **Pipeline shipped; v0.1.0 published to all three packages.** Harness added so future sessions publish correctly.
- Branch / commit: `main` (release pipeline + harness landed).

## Completed This Session

- [x] Built release-please → split → Packagist pipeline; published v0.1.0.
- [x] Added agent harness (`CLAUDE.md`, `AGENTS.md`, `feature_list.json`, `progress.md`, `init.sh`, this file) encoding the publishing rules + gotchas.

## Verification Evidence

| Check | Command | Result | Notes |
| --- | --- | --- | --- |
| Chain installs | `composer require birdcar/markdown-filament` (clean dir) | ✅ | Resolved filament→laravel→php @ 0.1.0 from Packagist |
| CI matrix | `.github/workflows/ci.yml` | ✅ green | PHP 8.2–8.5 × Laravel 12–13 × lowest/stable + Filament v4 |
| All 3 on Packagist | repo.packagist.org p2 | ✅ v0.1.0 | php, laravel, filament |

## Decisions Made

- Lockstep + `self.version`; non-EOL matrix; `CLAUDE.md` + `AGENTS.md` for instructions.

## Blockers / Risks

- Confirm the Packagist GitHub App is installed on all three repos (auto-update on future tags).

## Next Session Startup

1. Read `CLAUDE.md` (especially "Publishing rules").
2. Read `feature_list.json` and `progress.md`.
3. Review this handoff.
4. Run `./init.sh` before editing.

## Recommended Next Step

- When feat/fix commits accumulate, cut the next release (feat-002): merge the release-please Release PR; if the pipeline doesn't fire, run the `release` workflow's `workflow_dispatch`.
