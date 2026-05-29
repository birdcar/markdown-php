# Packagist Publishing for markdown-php Contract

**Created**: 2026-05-29
**Confidence Score**: 95/100
**Status**: Approved
**Supersedes**: None

## Problem Statement

markdown-php is a Composer monorepo with three packages — `birdcar/markdown-php` at the repo root, `birdcar/markdown-laravel`, and `birdcar/markdown-filament` — but Packagist serves exactly one package per Git repository. Submitting the monorepo as-is publishes only the core package; the Laravel and Filament packages stay uninstallable. The `composer require birdcar/markdown-laravel` and `birdcar/markdown-filament` lines already advertised in the READMEs do not work for anyone.

There is no release automation, no tags, and no CI. The sub-packages wire themselves together with `@dev` path-repo constraints and `minimum-stability: dev`, which cannot produce stable published releases. The root package is also a dirty publish target: its dist archive would bundle `packages/`, `docs/`, `tests/`, and an unresolvable `spec/` git submodule, none of which a consumer of the core library needs.

## Goals

1. All three packages are installable from Packagist by external users via `composer require birdcar/markdown-{php,laravel,filament}`.
2. Releases are fully automated from Conventional Commits: merging a Release PR cuts a tag, splits the sub-packages, and publishes to Packagist with no manual tagging.
3. One lockstep version across all three packages, starting at v0.1.0, with inter-package dependencies expressed via Composer's `self.version`.
4. CI verifies every PHP and Laravel version the composer.json files claim — constrained to currently non-EOL releases only (PHP 8.2–8.5, Laravel 12–13).
5. The published core package archive is clean: no monorepo scaffolding, no submodule.

## Success Criteria

- [ ] Merging the Release PR creates tag v0.1.0 and a GitHub Release on the monorepo.
- [ ] The split job mirrors `packages/laravel` → `birdcar/markdown-laravel` and `packages/filament` → `birdcar/markdown-filament` and pushes the v0.1.0 tag to both split repos.
- [ ] Packagist shows version 0.1.0 for all three packages after the auto-update webhook fires.
- [ ] In a clean scratch project with no path repositories, `composer require birdcar/markdown-filament` resolves the full chain (filament → laravel → php) entirely from Packagist.
- [ ] The CI matrix passes: PHP 8.2 / 8.3 / 8.4 / 8.5 × Laravel 12 / 13, run with both `--prefer-lowest` and `--prefer-stable`, plus Filament v4.
- [ ] `composer validate --strict` passes for all three composer.json files.
- [ ] A downloaded dist archive of `birdcar/markdown-php` contains `src/` but not `packages/`, `docs/`, `tests/`, or `spec/`.

## Scope Boundaries

### In Scope

- composer.json release-readiness across all 3 packages: `@dev` → `self.version`; `extra.branch-alias` (`dev-main` => `0.x-dev`); keep root-only `minimum-stability`/`prefer-stable`.
- Narrow dependency constraints to non-EOL versions: `illuminate/support` → `^12.0|^13.0`, `orchestra/testbench` aligned (`^10.0|^11.0`); keep `php ^8.2` (covers 8.2–8.5).
- `.gitattributes` `export-ignore` (root + each package) so dist archives are clean.
- release-please config + manifest: `simple` type, single root `.` component, lockstep, `bump-minor-pre-major=true`, `bump-patch-for-minor-pre-major=false`, plain `v${version}` tags.
- `release.yml`: `release-please` job → re-gated `test` job → `split` job (splitsh-lite via `danharrin/monorepo-split-github-action`, path-repos stripped) propagating the tag.
- Reusable `tests.yml` (workflow_call) matrix + `ci.yml` on PR/push/weekly schedule; PHPStan job (Stretch).
- `RELEASING.md` runbook; per-package MIT `LICENSE` files; `--prefer-lowest`/`--prefer-stable` matrix legs.

### Out of Scope

- Creating the `birdcar/markdown-laravel` and `birdcar/markdown-filament` GitHub repos — requires your GitHub account (documented in RELEASING.md, executed in the human-gate phase).
- Generating the split PAT secret — requires your account and repo admin rights.
- Submitting packages to Packagist and enabling the auto-update webhook/GitHub App — requires your Packagist account.
- Configuring branch protection requiring the tests check — repo admin action; documented in the runbook.
- Independent per-package versioning — explicitly rejected in favor of lockstep + `self.version`.

### Future Considerations

- Promote to v1.0.0 once the BFM API stabilizes.
- Add/remove PHP and Laravel matrix legs as versions release or reach EOL.

## Execution Plan

_Added during Phase 5 handoff. Pick up this contract cold and know exactly how to execute._

### Dependency Graph

```
Phase 1: Package release-readiness ─┐
Phase 2: CI test matrix ────────────┤  (1 & 2 independent — no shared files)
                                     └──> Phase 3: Release automation
                                               └──> Phase 4: Provision & first release (HUMAN GATE)
```

### Execution Steps

**Strategy**: Hybrid (Phases 1 & 2 in parallel, then 3, then 4 gate)

1. **Phases 1 & 2** — parallel (no shared files; Phase 1 = composer/.gitattributes/LICENSE, Phase 2 = `.github/workflows/`)

   ```bash
   /ideation:execute-spec docs/ideation/packagist-publishing/spec-phase-1.md
   /ideation:execute-spec docs/ideation/packagist-publishing/spec-phase-2.md
   ```

2. **Phase 3** — Release automation _(blocked by Phases 1 & 2)_

   ```bash
   /ideation:execute-spec docs/ideation/packagist-publishing/spec-phase-3.md
   ```

3. **Phase 4** — Provision & first release _(HUMAN GATE, blocked by Phase 3)_

   ```bash
   /ideation:execute-spec docs/ideation/packagist-publishing/spec-phase-4.md
   ```

### Agent Team Prompt

```
Lead orchestrates four phases for Packagist publishing of the markdown-php monorepo.

Dispatch Phase 1 (Package release-readiness — composer.json edits, .gitattributes,
LICENSE files) and Phase 2 (CI test matrix — tests.yml + ci.yml) in PARALLEL: they
share no files (Phase 1 touches composer.json/.gitattributes/LICENSE, Phase 2 touches
.github/workflows/tests.yml + ci.yml).

When both complete, run Phase 3 (Release automation — release-please config + release.yml
+ RELEASING.md), which depends on both (self.version from Phase 1, tests.yml from Phase 2).

Phase 4 is a HUMAN GATE — do not automate it. Surface the RELEASING.md checklist
(create split repos, add PAT secret, submit to Packagist, set branch protection,
merge the first Release PR) to the user and stop.
```

---

_This contract was generated from brain dump input. Review and approve before proceeding to specification._
