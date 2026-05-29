# Implementation Spec: Packagist Publishing for markdown-php - Phase 1

**Contract**: ./contract.md
**Estimated Effort**: S

## Technical Approach

Make all three packages produce valid, installable Composer releases. Three things change: (1) inter-package dependencies switch from `@dev` path-repo constraints to Composer's `self.version` so a tagged release of one package requires the identical tagged version of its siblings; (2) third-party constraints narrow to currently non-EOL versions (PHP `^8.2` covers 8.2–8.5; Laravel `^12.0|^13.0`; Testbench aligned); (3) the published archives are cleaned up via `.gitattributes` `export-ignore` and MIT `LICENSE` files are added.

This phase only edits static config and adds files — no application code changes. It is parallelizable with Phase 2 (CI), which touches only `.github/workflows/`.

**Important correction to the contract's MVP wording.** The contract says "drop/relax `minimum-stability: dev` + `prefer-stable`." During spec authoring this proved wrong: both keys are **root-only directives** — Composer ignores them when the package is consumed as a dependency, so they have zero effect on downstream `composer require`. They are required for local standalone `cd packages/laravel && composer install` to resolve the `self.version` path dependency (which is a dev version until a tag exists). **We keep them.** The `branch-alias` added below makes that local dev version resolve as `0.x-dev`.

## Feedback Strategy

**Inner-loop command**: `composer validate --strict` (run in each package directory)

**Playground**: CLI — `composer validate` and a standalone `composer update --dry-run` in each package dir.

**Why this approach**: All changes are to composer.json/static files; `composer validate --strict` is the fastest signal that a manifest is still well-formed and resolvable.

## File Changes

### New Files

| File Path | Purpose |
| --- | --- |
| `.gitattributes` | `export-ignore` rules so the core `birdcar/markdown-php` dist archive excludes monorepo scaffolding and the `spec/` submodule. |
| `packages/laravel/.gitattributes` | `export-ignore` for the laravel split repo's dist (tests, phpunit, phpstan). |
| `packages/filament/.gitattributes` | `export-ignore` for the filament split repo's dist. |
| `LICENSE` | MIT license for the core package. |
| `packages/laravel/LICENSE` | MIT license; ships in the laravel split repo. |
| `packages/filament/LICENSE` | MIT license; ships in the filament split repo. |

### Modified Files

| File Path | Changes |
| --- | --- |
| `composer.json` (root) | Add `extra.branch-alias` (`dev-main` => `0.x-dev`). No dependency changes — core has no inter-package deps. |
| `packages/laravel/composer.json` | `birdcar/markdown-php`: `@dev` → `self.version`. `illuminate/support`: `^10.0\|^11.0\|^12.0\|^13.0` → `^12.0\|^13.0`. `orchestra/testbench`: `^8.0\|^9.0\|^10.0` → `^10.0\|^11.0`. Add `extra.branch-alias`. **Keep** `minimum-stability`, `prefer-stable`, and the `repositories` path block. |
| `packages/filament/composer.json` | `birdcar/markdown-laravel`: `@dev` → `self.version`. `orchestra/testbench`: `^9.0\|^10.0` → `^10.0\|^11.0`. Keep `filament/filament: ^4.0` (Laravel 13 compat is verified in Phase 2; narrow here only if CI proves it incompatible). Add `extra.branch-alias`. **Keep** `minimum-stability`, `prefer-stable`, and both `repositories` path blocks. |

## Implementation Details

### Composer manifest edits

**Overview**: Switch inter-package deps to `self.version` and narrow third-party ranges to non-EOL.

`packages/laravel/composer.json` `require` becomes:

```jsonc
"require": {
    "php": "^8.2",
    "birdcar/markdown-php": "self.version",
    "illuminate/support": "^12.0|^13.0"
}
```

`packages/laravel/composer.json` `require-dev` `orchestra/testbench` becomes `"^10.0|^11.0"` (Testbench 10 → Laravel 12, Testbench 11 → Laravel 13).

`packages/filament/composer.json` `require` becomes:

```jsonc
"require": {
    "php": "^8.2",
    "birdcar/markdown-laravel": "self.version",
    "filament/filament": "^4.0"
}
```

Add to all three composer.json (root, laravel, filament):

```jsonc
"extra": {
    "branch-alias": {
        "dev-main": "0.x-dev"
    }
    // ...merge with existing "laravel" provider blocks in the sub-packages
}
```

**Key decisions**:

- `self.version` over a pinned `^0.1` constraint: lockstep tagging guarantees siblings always share the exact version, so `self.version` is both correct and maintenance-free. A pinned range would need hand-editing every breaking release.
- Keep `minimum-stability: dev` + `prefer-stable` (root-only, invisible to consumers; needed for standalone dev). Documented above.
- Keep the `repositories` path blocks in the monorepo for local standalone dev. They are harmless to published consumers (Composer ignores a dependency's `repositories`). Phase 3's split job strips them from the *published* mirror so the split repos are clean.
- Narrow Laravel to `^12.0|^13.0`: Laravel 10 (EOL 2025-02-04) and 11 (EOL 2026-03-12) are both end-of-life as of 2026-05-29. Nothing is published yet, so narrowing is free.

**Implementation steps**:

1. Edit `packages/laravel/composer.json` per above.
2. Edit `packages/filament/composer.json` per above.
3. Add `extra.branch-alias` to root `composer.json`.
4. Run `composer validate --strict` in all three dirs.
5. In `packages/laravel`, run `composer update --dry-run` to confirm `self.version` resolves against the root path repo (expect `birdcar/markdown-php 0.x-dev`).

### `.gitattributes` (root / core package)

**Overview**: `git archive` (what Packagist/GitHub use to build dist zips) honors `export-ignore`. Strip everything a consumer of the core library doesn't need.

```gitattributes
/.github          export-ignore
/.claude          export-ignore
/docs             export-ignore
/packages         export-ignore
/spec             export-ignore
/tests            export-ignore
/.phpunit.cache   export-ignore
phpunit.xml       export-ignore
.gitattributes    export-ignore
.gitignore        export-ignore
.gitmodules       export-ignore
```

> `composer.json` and `composer.lock` are **never** export-ignored — Composer needs the manifest. `/spec` is the git submodule; excluding it prevents an unresolvable submodule reference in the dist.

### `packages/{laravel,filament}/.gitattributes`

These live inside the subtree, so after Phase 3's split they apply at the split repo root:

```gitattributes
/tests            export-ignore
/.phpunit.cache   export-ignore
phpunit.xml       export-ignore
phpstan.neon      export-ignore
.gitattributes    export-ignore
```

### LICENSE files

Standard MIT text, copyright `2026 Nick Cannariato (Birdcar)`. One at repo root (core), one in each package dir (rides along in the split).

## Testing Requirements

### Manual Testing

- [ ] `composer validate --strict` passes in `.`, `packages/laravel`, `packages/filament`.
- [ ] `cd packages/laravel && composer update --dry-run` resolves `birdcar/markdown-php` to the local path version (no Packagist lookup).
- [ ] `git archive HEAD -o /tmp/core.tar && tar tf /tmp/core.tar | grep -E '^(packages|docs|tests|spec)/'` returns **nothing**.
- [ ] `git archive HEAD -o /tmp/core.tar && tar tf /tmp/core.tar | grep -E '^(src/|composer.json|LICENSE)'` returns the expected entries.

## Failure Modes

| Component | Failure Mode | Trigger | Impact | Mitigation |
| --- | --- | --- | --- | --- |
| laravel composer.json | `self.version` unresolvable locally | minimum-stability removed, so dev path dep rejected | `composer install` fails in standalone dev | Keep `minimum-stability: dev` + `prefer-stable` (this spec does). |
| `.gitattributes` | Over-broad export-ignore strips `src/` or `composer.json` | Wrong glob | Published package is empty/broken | Validate via the `git archive` tar listing above before tagging. |
| illuminate constraint | `^12.0\|^13.0` excludes a consumer on Laravel 11 | Consumer still on EOL Laravel | They can't install | Accepted — we only support non-EOL Laravel by decision. |
| `/spec` submodule | Submodule left in archive | Forgot `/spec export-ignore` | Consumers hit a dangling submodule | Explicit `/spec export-ignore` + tar-listing check. |

## Validation Commands

```bash
# In each of: . , packages/laravel , packages/filament
composer validate --strict

# Resolve check (laravel)
( cd packages/laravel && composer update --dry-run )

# Clean-archive check (core)
git archive HEAD -o /tmp/core.tar && tar tf /tmp/core.tar | grep -E '^(packages|docs|tests|spec)/' && echo "DIRTY" || echo "CLEAN"
```

## Open Items

- [ ] Confirm the copyright holder string for the LICENSE files (`Nick Cannariato (Birdcar)` assumed).

---

_This spec is ready for implementation. Follow the patterns and validate at each step._
