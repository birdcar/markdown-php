# Implementation Spec: Packagist Publishing for markdown-php - Phase 2

**Contract**: ./contract.md
**Estimated Effort**: M

## Technical Approach

Build a **reusable** test workflow (`workflow_call`) that runs the full non-EOL matrix across all three packages, plus PHPStan (Stretch) and a weekly scheduled drift run (Stretch). It's reusable so Phase 3's release pipeline can invoke the exact same matrix as a final gate before splitting — one source of truth for "is this green."

The matrix verifies every version the composer.json files claim: PHP `8.2 / 8.3 / 8.4 / 8.5` against Laravel `12 / 13` (driven through `orchestra/testbench`), run with both `--prefer-lowest` and `--prefer-stable` to catch floor and ceiling breakages. The core package job must check out with `submodules: recursive` because its `tests/FixtureTestCase.php` reads conformance fixtures from the `spec/` submodule.

This phase is parallelizable with Phase 1 — it touches only `.github/workflows/` and shares no files. It assumes the non-EOL constraints from Phase 1's contract decision (Laravel 12/13) but does not need Phase 1's code to be authored first.

## Feedback Strategy

**Inner-loop command**: `act pull_request -W .github/workflows/ci.yml` (or push to a throwaway branch and watch `gh run watch`)

**Playground**: GitHub Actions — iterate by pushing to a branch and observing `gh run list` / `gh run view --log-failed`.

**Why this approach**: Workflows can only be truly validated by running on GitHub's runners; `gh run watch` gives the fastest authoritative signal.

## File Changes

### New Files

| File Path | Purpose |
| --- | --- |
| `.github/workflows/tests.yml` | Reusable (`on: workflow_call`) matrix: `core`, `laravel`, `filament`, and `phpstan` jobs. |
| `.github/workflows/ci.yml` | Triggers on `pull_request`, `push` to `main`, and weekly `schedule`; calls `tests.yml`. |

## Implementation Details

### `tests.yml` — reusable matrix workflow

**Overview**: One reusable workflow with a job per package. Each job sets the dependency versions per matrix leg, then runs that package's existing `composer test`.

```yaml
name: tests
on:
  workflow_call:

jobs:
  core:
    name: core • PHP ${{ matrix.php }} • ${{ matrix.deps }}
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: ['8.2', '8.3', '8.4', '8.5']
        deps: [lowest, stable]
    steps:
      - uses: actions/checkout@v4
        with:
          submodules: recursive   # spec/ fixtures are required by FixtureTestCase
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: none
      - run: composer update --no-interaction --prefer-dist --prefer-${{ matrix.deps }}
      - run: composer test

  laravel:
    name: laravel • PHP ${{ matrix.php }} • L${{ matrix.laravel }} • ${{ matrix.deps }}
    runs-on: ubuntu-latest
    defaults: { run: { working-directory: packages/laravel } }
    strategy:
      fail-fast: false
      matrix:
        php: ['8.2', '8.3', '8.4', '8.5']
        laravel: ['12', '13']
        deps: [lowest, stable]
        include:
          - laravel: '12'
            testbench: '10'
          - laravel: '13'
            testbench: '11'
    steps:
      - uses: actions/checkout@v4
        with: { submodules: recursive }
      - uses: shivammathur/setup-php@v2
        with: { php-version: '${{ matrix.php }}', coverage: none }
      - run: |
          composer require --no-update --dev \
            "orchestra/testbench:^${{ matrix.testbench }}.0"
          composer require --no-update \
            "illuminate/support:^${{ matrix.laravel }}.0"
          composer update --no-interaction --prefer-dist --prefer-${{ matrix.deps }} --with-all-dependencies
      - run: composer test

  filament:
    name: filament • PHP ${{ matrix.php }} • L${{ matrix.laravel }} • ${{ matrix.deps }}
    runs-on: ubuntu-latest
    defaults: { run: { working-directory: packages/filament } }
    strategy:
      fail-fast: false
      matrix:
        php: ['8.2', '8.3', '8.4', '8.5']
        laravel: ['12', '13']
        deps: [lowest, stable]
        include:
          - laravel: '12'
            testbench: '10'
          - laravel: '13'
            testbench: '11'
        # exclude:  # <-- add here if Filament v4 proves incompatible with Laravel 13
        #   - { laravel: '13' }
    steps:
      - uses: actions/checkout@v4
        with: { submodules: recursive }
      - uses: shivammathur/setup-php@v2
        with: { php-version: '${{ matrix.php }}', coverage: none }
      - run: |
          composer require --no-update --dev "orchestra/testbench:^${{ matrix.testbench }}.0"
          composer update --no-interaction --prefer-dist --prefer-${{ matrix.deps }} --with-all-dependencies
      - run: composer test

  phpstan:   # Stretch
    runs-on: ubuntu-latest
    strategy:
      matrix:
        dir: ['.', 'packages/laravel', 'packages/filament']
    steps:
      - uses: actions/checkout@v4
        with: { submodules: recursive }
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4', coverage: none }
      - run: composer install --no-interaction --prefer-dist
        working-directory: ${{ matrix.dir }}
      - run: composer analyse
        working-directory: ${{ matrix.dir }}
```

**Key decisions**:

- `fail-fast: false` so one broken leg doesn't hide the status of the others.
- `submodules: recursive` on every job — the laravel/filament packages pull `birdcar/markdown-php` via the root path repo, whose tests touch `spec/`. Cheap insurance even where unused.
- Testbench version pinned per Laravel leg via the `include` map (Testbench 10 → L12, 11 → L13).
- `--with-all-dependencies` so the new testbench/illuminate constraints actually update transitive deps.
- Core PHPStan already has 11 known pre-existing errors (per project memory) — the `phpstan` job for `.` may need a baseline or `|| true`. See Open Items.

**Implementation steps**:

1. Write `tests.yml` as above.
2. Write `ci.yml` (below).
3. Push to a branch; `gh run watch`.
4. Triage failing legs (especially Filament × Laravel 13 and `prefer-lowest`).

**Feedback loop**:

- **Playground**: A throwaway branch `ci/bootstrap`; push and watch `gh run list --branch ci/bootstrap`.
- **Experiment**: Confirm all of `{8.2,8.5} × {12,13} × {lowest,stable}` resolve and pass for laravel + filament; confirm core passes 8.2–8.5.
- **Check command**: `gh run view --log-failed`

### `ci.yml` — trigger wrapper

```yaml
name: ci
on:
  pull_request:
  push:
    branches: [main]
  schedule:
    - cron: '0 6 * * 1'   # weekly drift run (Stretch)
jobs:
  tests:
    uses: ./.github/workflows/tests.yml
```

**Key decisions**: A thin wrapper keeps triggers separate from matrix logic, so Phase 3's `release.yml` can `uses: ./.github/workflows/tests.yml` without inheriting CI's triggers.

## Testing Requirements

### Manual Testing

- [ ] All `core` legs green (PHP 8.2–8.5).
- [ ] All `laravel` legs green (8.2–8.5 × L12/L13 × lowest/stable).
- [ ] `filament` legs green, or a documented `exclude` added with rationale.
- [ ] `phpstan` job green for laravel + filament; core handled per Open Items.
- [ ] `schedule` trigger present; `ci.yml` calls `tests.yml` via local `uses:`.

## Failure Modes

| Component | Failure Mode | Trigger | Impact | Mitigation |
| --- | --- | --- | --- | --- |
| filament job | Dependency resolution fails | Filament v4 doesn't allow Laravel 13 yet | Red matrix leg blocks releases | Add `exclude: { laravel: '13' }` to the filament matrix **and** narrow `filament/filament` / document the gap; revisit when Filament supports L13. |
| core job | Tests fail: missing fixtures | `submodules: recursive` omitted | Core legs fail confusingly | Explicit `submodules: recursive` (this spec). |
| any job | `prefer-lowest` pulls ancient transitive dep that breaks | Loose lower bounds in composer.json | Red `lowest` legs | Raise the offending minimum in composer.json (feeds back to Phase 1). |
| PHP 8.5 legs | `setup-php` can't provision 8.5 | Action/runner lag | Red 8.5 legs | `shivammathur/setup-php` supports 8.5; pin `@v2`. If unavailable, drop 8.5 temporarily and note it. |
| phpstan (core) | 11 known pre-existing errors fail the job | Known league/commonmark interface issues | Red phpstan leg | Generate a PHPStan baseline for core, or scope the job to laravel/filament initially. |

## Validation Commands

```bash
# Iterate on a branch
git push -u origin ci/bootstrap
gh run watch
gh run view --log-failed
```

## Open Items

- [ ] Decide core PHPStan handling: baseline the 11 known errors vs. exclude `.` from the phpstan job.
- [ ] Confirm Filament v4's supported Laravel range; add the `exclude` only if CI proves L13 unsupported.

---

_This spec is ready for implementation. Follow the patterns and validate at each step._
