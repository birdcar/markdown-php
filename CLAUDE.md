# CLAUDE.md — markdown-php

The **canonical agent harness is [`AGENTS.md`](./AGENTS.md)** — read it first (startup workflow, working rules, verification, definition of done, end-of-session). This file mirrors the critical bits Claude Code auto-loads.

This is a monorepo publishing **three Composer packages to Packagist in lockstep** — `birdcar/markdown-php` (root), `birdcar/markdown-laravel` (`packages/laravel/`), `birdcar/markdown-filament` (`packages/filament/`) — linked via `self.version`. Dependency DAG: `filament → laravel → php`.

- **Startup / verification**: run `./init.sh`. **State**: `feature_list.json` + `progress.md`. **Handoff**: `session-handoff.md`.
- **Commits must be Conventional** (`feat:`/`fix:`/`chore:`/…) — release-please depends on it.
- **Full release runbook**: [`RELEASING.md`](./RELEASING.md).

## Publishing rules — DO NOT relearn these the hard way

Each rule is a real failure from building this pipeline. Violating one breaks publishing.

1. **`symfony/yaml` stays `^7.2 || ^8.0` in core** — `^8.0` alone needs PHP 8.4+ and conflicts with Laravel 12 (`symfony/yaml ^7.2`).
2. **Non-EOL only**: PHP `^8.2` (test 8.2–8.5); Laravel `^12.0|^13.0`. **Laravel 13 needs PHP ^8.3**, so `L13 × PHP 8.2` legs are excluded. Re-check endoflife.date when bumping.
3. **Keep `minimum-stability: dev` + `prefer-stable` in sub-packages** — root-only, invisible to consumers, required for local `self.version` resolution.
4. **Mirror repos must already have ≥1 commit** — the split action does a *snapshot* copy and dies (`src refspec main does not match any`) on an empty repo. Seed with a README first.
5. **Split action reads `GITHUB_TOKEN` env, not a `personal_access_token` input** — wired as `env: GITHUB_TOKEN: ${{ secrets.MONOREPO_SPLIT_TOKEN }}`.
6. **Merging the release-please PR may not trigger workflows** (bot/`GITHUB_TOKEN`-authored) — run the `release` workflow's **`workflow_dispatch`**; release-please idempotently cuts the release.
7. **Privileged release jobs gated `if: github.ref == 'refs/heads/main'`** — keep the guard so a dispatch from another ref can't read the split PAT.
8. **Core dist kept clean via `.gitattributes` `export-ignore`** (`packages/`, `docs/`, `tests/`, `spec/`); split job strips `repositories` from each package manifest.
9. **Repo setting**: Actions must be allowed to create PRs (`can_approve_pull_request_reviews = true`).
10. **After every release**: confirm all three on Packagist at the new version + the Packagist GitHub App is installed on all three repos.

> Everything else (startup, scope, DoD, escalation) lives in **`AGENTS.md`**. Keep these 10 rules in sync with `AGENTS.md`'s "Publishing rules" section.
