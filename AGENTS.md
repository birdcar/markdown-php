# AGENTS.md — markdown-php

Canonical agent harness for the Birdcar Flavored Markdown monorepo. This repo publishes **three Composer packages to Packagist in lockstep**, so getting releases right is the highest-stakes recurring task — the publishing rules below are load-bearing, not advisory.

> `CLAUDE.md` points here and inlines the critical publishing rules. `~/.claude/CLAUDE.md` global prefs still apply.

## Project shape

- Monorepo, **3 published packages**, lockstep-versioned, inter-linked via Composer `self.version`:
  | Package | Path | Mirror repo (read-only) |
  | --- | --- | --- |
  | `birdcar/markdown-php` | repo root | _is_ this repo |
  | `birdcar/markdown-laravel` | `packages/laravel/` | `github.com/birdcar/markdown-laravel` |
  | `birdcar/markdown-filament` | `packages/filament/` | `github.com/birdcar/markdown-filament` |
- Dependency DAG: `filament` → `laravel` → `php`.
- `spec/` is a **git submodule** (conformance fixtures; dev/test only — `tests/FixtureTestCase.php` reads it).
- Release flow: Conventional Commits → **release-please** (Release PR) → merge → tag `vX.Y.Z` → **snapshot split** to the two mirror repos → **Packagist** auto-update. Full runbook: **`RELEASING.md`**.

## Startup Workflow

Before writing code:

1. **Confirm working directory** with `pwd`.
2. **Read this file** completely.
3. **If the task touches releasing/publishing, read `RELEASING.md`** and the "Publishing rules" below before doing anything.
4. **Run `./init.sh`** to verify baseline health (it initializes the `spec/` submodule).
5. **Read `feature_list.json`** for current feature state.
6. **Review recent commits** with `git log --oneline -5`.

If baseline verification is failing, repair that first before adding new scope.

## Working Rules

- **One feature at a time** from `feature_list.json`.
- **Verification required**: never claim done without running the verification commands and recording evidence.
- **Conventional Commits always** (`feat:`, `fix:`, `chore:`, `ci:`, `docs:`) — release-please derives versions and the changelog from them. A non-conventional commit silently breaks releases.
- **Lockstep versioning**: all three packages release at one shared version. Never hand-pin an inter-package dependency — they use `self.version`.
- **Stay in scope**: don't modify files unrelated to the current feature. Leave a clean state so the next session can run `./init.sh` immediately.

## Publishing rules — DO NOT relearn these the hard way

Every rule below corresponds to a real failure encountered while building this pipeline. Violating one breaks publishing.

1. **`symfony/yaml` must stay `^7.2 || ^8.0` in core.** `^8.0` alone needs PHP 8.4+ and conflicts with Laravel 12 (which pins `symfony/yaml ^7.2`). Widening is what makes PHP 8.2/8.3 and Laravel 12 work.
2. **Support only non-EOL versions.** PHP `^8.2` (test 8.2/8.3/8.4/8.5); Laravel `^12.0|^13.0`. **Laravel 13 requires PHP ^8.3**, so the `L13 × PHP 8.2` matrix legs are excluded. Re-check EOL dates (endoflife.date) when bumping.
3. **Keep `minimum-stability: dev` + `prefer-stable` in the sub-packages.** Root-only directives (invisible to consumers); required for local standalone `composer install` to resolve the `self.version` path dependency. Removing them breaks local dev for zero consumer benefit.
4. **Mirror repos must already exist with at least one commit.** `danharrin/monorepo-split-github-action` does a *snapshot* copy (not a history split) and dies with `src refspec main does not match any` on a totally empty target. If recreating a mirror, seed it with a README commit first.
5. **The split action reads the token from the `GITHUB_TOKEN` env var** — there is **no** `personal_access_token` input. Wired as `env: GITHUB_TOKEN: ${{ secrets.MONOREPO_SPLIT_TOKEN }}` (a fine-grained PAT with Contents:write on both mirror repos).
6. **Merging a release-please PR may NOT trigger workflows** (the `chore: release` commit is authored by `github-actions[bot]`/`GITHUB_TOKEN`). If the pipeline doesn't fire after merging the Release PR, run the `release` workflow's **`workflow_dispatch`** — release-please is idempotent and will cut the release for the already-merged PR.
7. **Privileged release jobs are gated `if: github.ref == 'refs/heads/main'`** so a `workflow_dispatch` from another ref can't read the split PAT. Keep that guard.
8. **The core dist archive is kept clean via `.gitattributes` `export-ignore`** (excludes `packages/`, `docs/`, `tests/`, `spec/`). The split job strips the `repositories` key from each package's `composer.json` before pushing the mirror.
9. **Repo setting**: GitHub Actions must be allowed to create PRs (`can_approve_pull_request_reviews = true`) or release-please can't open the Release PR.
10. **After every release**: confirm all three packages show the new version on Packagist and that the **Packagist GitHub App** is installed on all three repos (otherwise Packagist won't auto-update on future tags).

## Verification Commands

```bash
./init.sh    # baseline: submodule init, composer validate (×3), core tests
```

- Manifests publishable: `composer validate --strict --no-check-lock` in `.`, `packages/laravel`, `packages/filament`
- Core test suite: `composer test` (root)
- **Static analysis (type-level / lint)**: `composer analyse` (PHPStan level 8). Packages are clean; core carries a known baseline of 11 league/commonmark interface errors, so core analyse is not gated in `init.sh`.
- Full matrix (PHP 8.2–8.5 × Laravel 12–13 × lowest/stable + Filament v4): runs in CI — `.github/workflows/ci.yml`. Locally, `cd packages/<pkg> && composer install && composer test`.
- Clean core archive check: `git archive HEAD | tar t | grep -E '^(packages|docs|tests|spec)/'` must be **empty**.

## Definition of Done

A change is done only when ALL are true:

- [ ] Target behavior implemented.
- [ ] Verification actually ran (the commands above); evidence recorded in `feature_list.json` / `progress.md`.
- [ ] Conventional Commit message used.
- [ ] Repo restartable from `./init.sh`.

**Release-specific DoD** — a release is done only when: tag `vX.Y.Z` exists on all three GitHub repos; Packagist shows the version for all three; `composer require birdcar/markdown-filament` resolves `filament → laravel → php` from Packagist in a clean project.

## End of Session

Before ending a session:

1. Update `progress.md` (current state, decisions, blockers).
2. Update `feature_list.json` status + evidence.
3. Fill `session-handoff.md` for multi-session work.
4. Commit in a safe state with a Conventional Commit message.
5. Leave the repo runnable from `./init.sh`.

## Escalation

- **Architecture / release-mechanism questions**: see `RELEASING.md` and `docs/ideation/packagist-publishing/`; otherwise ask the user.
- **Release pipeline failing**: do NOT hand-push tags to mirror repos. Diagnose the `release` workflow run; re-run the failed `split` jobs after fixing root cause (the split is idempotent at a given tag).
- **Repeated failures**: record in `progress.md`, flag for human review.
