# Implementation Spec: Packagist Publishing for markdown-php - Phase 3

**Contract**: ./contract.md
**Estimated Effort**: M
**Prereqs**: Phase 1 (self.version + composer readiness), Phase 2 (`tests.yml` reusable workflow)

## Technical Approach

Wire the automated release pipeline. Release Please runs on every push to `main`, maintaining a "Release PR" that bumps a single lockstep version and updates `CHANGELOG.md` from Conventional Commits. Merging that PR creates the GitHub Release + `v0.x.0` tag. In the **same workflow run**, gated on `release_created == true`, a test job re-runs the Phase 2 matrix and a split job mirrors `packages/laravel` and `packages/filament` into their standalone repos via `splitsh-lite`, propagating the new tag. Packagist's auto-update webhook then crawls all three.

Chaining the split into the release workflow (rather than a separate `on: push: tags` workflow) sidesteps the GitHub gotcha that `GITHUB_TOKEN`-created tags don't trigger new workflow runs. The cross-repo `git push` the split performs **does** require a PAT (`GITHUB_TOKEN` can't write to other repos) — that secret is provisioned in Phase 4.

Release Please uses the `simple` release strategy with a single root `.` component and `include-component-in-tag: false`, so tags are plain `v0.1.0` and **no `version` field is written into any composer.json** (Composer best practice — Packagist derives versions from tags). `self.version` (Phase 1) handles inter-package deps untouched.

## Feedback Strategy

**Inner-loop command**: `gh run view --log` on the latest `release` run (after pushing a Conventional Commit to a test branch)

**Playground**: GitHub Actions + a scratch branch. Release Please can be dry-run by pointing the action at a test branch and inspecting the Release PR it opens.

**Why this approach**: The pipeline's behavior (does a Release PR open? does the split fire on merge?) is only observable on GitHub; the run logs are the authoritative signal.

## File Changes

### New Files

| File Path | Purpose |
| --- | --- |
| `release-please-config.json` | Release Please config: `simple` type, single root component, pre-1.0 bump rules, plain `v` tags. |
| `.release-please-manifest.json` | Tracks the current released version. Bootstrap `0.0.0`. |
| `.github/workflows/release.yml` | `release-please` → `test` → `split` jobs. |
| `RELEASING.md` | Runbook: one-time provisioning + ongoing release flow (consumed in Phase 4). |

## Implementation Details

### `release-please-config.json`

```json
{
  "$schema": "https://raw.githubusercontent.com/googleapis/release-please/main/schemas/config.json",
  "release-type": "simple",
  "bump-minor-pre-major": true,
  "bump-patch-for-minor-pre-major": false,
  "include-component-in-tag": false,
  "packages": {
    ".": {
      "package-name": "birdcar/markdown-php",
      "changelog-path": "CHANGELOG.md"
    }
  }
}
```

**Key decisions**:

- `simple` strategy: tracks version in the manifest + `CHANGELOG.md` only; never edits composer.json (which carries no `version` field). Lockstep is achieved by one component + the split propagating one tag — so **no `linked-versions` plugin and no per-package components are needed**.
- `bump-minor-pre-major: true` → breaking changes bump `0.x` minor (stay sub-1.0). `bump-patch-for-minor-pre-major: false` → `feat:` bumps minor (`0.1.0`→`0.2.0`), `fix:` bumps patch (`0.1.0`→`0.1.1`).
- `include-component-in-tag: false` → tag is `v0.1.0`, not `birdcar/markdown-php-v0.1.0`.

### `.release-please-manifest.json`

```json
{ ".": "0.0.0" }
```

First release: force `0.1.0` via a Conventional Commit footer `Release-As: 0.1.0` on a commit merged to `main` (preferred — no config churn), or temporarily add `"release-as": "0.1.0"` to the config for the first run. See Open Items.

### `.github/workflows/release.yml`

```yaml
name: release
on:
  push:
    branches: [main]

permissions:
  contents: write
  pull-requests: write

jobs:
  release-please:
    runs-on: ubuntu-latest
    outputs:
      release_created: ${{ steps.rp.outputs.release_created }}
      tag_name: ${{ steps.rp.outputs.tag_name }}
    steps:
      - uses: googleapis/release-please-action@v4
        id: rp
        with:
          token: ${{ secrets.GITHUB_TOKEN }}

  test:
    needs: release-please
    if: needs.release-please.outputs.release_created == 'true'
    uses: ./.github/workflows/tests.yml

  split:
    needs: [release-please, test]
    if: needs.release-please.outputs.release_created == 'true'
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        include:
          - package: packages/laravel
            split_repo: markdown-laravel
          - package: packages/filament
            split_repo: markdown-filament
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0   # splitsh-lite needs full history

      - name: Strip dev path repositories from the published manifest
        run: |
          tmp=$(mktemp)
          jq 'del(.repositories)' "${{ matrix.package }}/composer.json" > "$tmp"
          mv "$tmp" "${{ matrix.package }}/composer.json"
          git config user.name  "github-actions[bot]"
          git config user.email "github-actions[bot]@users.noreply.github.com"
          git commit -am "chore: strip dev path repositories for split [skip ci]"

      - name: Split ${{ matrix.package }} → birdcar/${{ matrix.split_repo }}
        uses: danharrin/monorepo-split-github-action@v2.3.0
        with:
          tag: ${{ needs.release-please.outputs.tag_name }}
          package_directory: ${{ matrix.package }}
          repository_organization: birdcar
          repository_name: ${{ matrix.split_repo }}
          user_name: "github-actions[bot]"
          user_email: "github-actions[bot]@users.noreply.github.com"
          personal_access_token: ${{ secrets.MONOREPO_SPLIT_TOKEN }}
```

**Key decisions**:

- **Re-gate**: `split` needs both `release-please` and `test`; a red matrix blocks publication even though `main` was presumably already green.
- **Path-repo stripping**: the local (un-pushed, ephemeral) commit removes the `repositories` key so the mirrored composer.json is clean for standalone use. `[skip ci]` prevents accidental loops. If you'd rather not rewrite history, delete this step — the path repos are harmless to `composer require` consumers (Composer ignores a dependency's `repositories`). Kept because Stretch scope = maximum polish.
- **Lockstep ordering**: both packages are split at the same `tag_name`, so `self.version` resolves (both `0.1.0` land before Packagist crawls). Split order is irrelevant.
- Pin `danharrin/monorepo-split-github-action@v2.3.0` (verify latest tag at implementation time).

**Feedback loop**:

- **Playground**: A scratch branch with a `feat:`/`fix:` commit; let the action open a Release PR. Inspect the PR's diff (version + CHANGELOG).
- **Experiment**: Merge a `fix:` and confirm next version is a patch bump; merge a `feat:` and confirm a minor bump; confirm `release_created` gates the split.
- **Check command**: `gh run view --log` / `gh pr list --label "autorelease: pending"`

### `RELEASING.md`

Documents what Phase 4 executes. Sections:

1. **One-time setup** — create the two split repos; create the PAT and store it as `MONOREPO_SPLIT_TOKEN`; submit all three repos to Packagist and install the Packagist GitHub App (auto-update); enable branch protection on `main` requiring the `tests` checks.
2. **Cutting a release** — merge the Release PR; the pipeline tags, re-tests, splits, and Packagist publishes. Nothing manual.
3. **Versioning rules** — Conventional Commit → bump mapping; how to force a version with `Release-As:`.
4. **Troubleshooting** — split 403 (PAT scope), Packagist not updating (webhook), Release PR not opening (commit types).

## Testing Requirements

### Manual Testing

- [ ] Pushing a `feat:` commit to `main` opens/updates a Release PR with a correct `0.x` bump and CHANGELOG entry.
- [ ] The `split` job is skipped when `release_created != true` (normal pushes).
- [ ] `release.yml` successfully calls `./.github/workflows/tests.yml` as the `test` job.
- [ ] `jq 'del(.repositories)'` produces valid JSON for both package manifests (`composer validate` on the rewritten file).

## Failure Modes

| Component | Failure Mode | Trigger | Impact | Mitigation |
| --- | --- | --- | --- | --- |
| split job | `403` pushing to split repo | `MONOREPO_SPLIT_TOKEN` missing/insufficient scope | Release tagged but not published | Provision PAT with Contents:write on both split repos (Phase 4); fail loudly. |
| release-please | No Release PR opens | Commits aren't Conventional (`feat:`/`fix:`) | No releases happen | Document commit conventions in RELEASING.md; the repo already uses them. |
| split job | `self.version` unresolvable downstream | Only one of the two packages tagged (partial failure) | filament install breaks | `fail-fast: false` + both split in one run; re-run the failed leg (idempotent at same tag). |
| strip step | Invalid composer.json after `jq` | `jq` filter typo | Broken published manifest | `composer validate` the rewritten file in the same step before splitting. |
| GITHUB_TOKEN | Split can't push (used instead of PAT) | Wrong secret wired | 403 | Pipeline uses `MONOREPO_SPLIT_TOKEN` explicitly. |
| tag trigger | Expecting tag push to start split | Misunderstanding GITHUB_TOKEN behavior | Split never fires | Split is a chained job gated on `release_created`, not a tag trigger. |

## Validation Commands

```bash
# Validate the rewritten (stripped) manifest locally before trusting the job
jq 'del(.repositories)' packages/laravel/composer.json | php -r 'json_decode(file_get_contents("php://stdin")); echo json_last_error_msg();'

# Watch a release run
gh run watch
gh pr list --label "autorelease: pending"
```

## Open Items

- [ ] Choose the first-release mechanism: `Release-As: 0.1.0` commit footer (preferred) vs. temporary `release-as` in config.
- [ ] Confirm latest `danharrin/monorepo-split-github-action` tag and the `personal_access_token` input name at implementation time.

---

_This spec is ready for implementation. Follow the patterns and validate at each step._
