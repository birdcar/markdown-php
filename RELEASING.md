# Releasing

`markdown-php` is a monorepo published as three Packagist packages that version
in lockstep:

| Package | Source | Mirror repo |
| --- | --- | --- |
| `birdcar/markdown-php` | repo root | `birdcar/markdown-php` (this repo) |
| `birdcar/markdown-laravel` | `packages/laravel` | `birdcar/markdown-laravel` |
| `birdcar/markdown-filament` | `packages/filament` | `birdcar/markdown-filament` |

Releases are driven by [Release Please](https://github.com/googleapis/release-please)
and [`danharrin/monorepo-split-github-action`](https://github.com/danharrin/monorepo-split-github-action),
wired together in [`.github/workflows/release.yml`](.github/workflows/release.yml).

## How the pipeline works

On every push to `main`, the `release` workflow runs three jobs:

1. **`release-please`** — maintains a "Release PR" that bumps a single lockstep
   version (tracked in `.release-please-manifest.json`) and regenerates
   `CHANGELOG.md` from [Conventional Commits](https://www.conventionalcommits.org/).
   Merging that PR creates a GitHub Release and a plain `v0.x.0` tag.
2. **`test`** — re-runs the full Phase 2 matrix (`./.github/workflows/tests.yml`).
   Gated on `release_created == 'true'`, so it only fires on the merge that cuts
   a release.
3. **`split`** — also gated on `release_created == 'true'` and on `test` passing.
   For each package it strips the dev `repositories` key from the package
   `composer.json` (a local, un-pushed commit), then mirrors the package into its
   standalone repo at the same `tag_name`. Packagist's auto-update webhook then
   crawls all three packages.

The split is chained into the release run (not a separate `on: push: tags`
workflow) on purpose: tags created with `GITHUB_TOKEN` do **not** trigger new
workflow runs, so a tag-triggered split would never fire. The cross-repo push
the split performs requires a PAT — the built-in `GITHUB_TOKEN` cannot write to
other repos. The split action reads that PAT from the `GITHUB_TOKEN` **environment
variable** (set to `secrets.MONOREPO_SPLIT_TOKEN` in the workflow), not from a
`with:` input — the action has no token input.

No `version` field is ever written into any `composer.json`. Packagist derives
versions from tags; inter-package dependencies use Composer's `self.version`.

## One-time setup

These steps live in your GitHub and Packagist accounts and only need to be done
once. (This is Phase 4 of the packagist-publishing plan — it is a human gate.)

### 1. Create the two split (mirror) repositories

```bash
gh repo create birdcar/markdown-laravel  --public \
  --description "Laravel integration for Birdcar Flavored Markdown (read-only mirror of birdcar/markdown-php)"
gh repo create birdcar/markdown-filament --public \
  --description "Filament integration for Birdcar Flavored Markdown (read-only mirror of birdcar/markdown-php)"
```

Leave them empty — the split action force-pushes into them.

### 2. Create the split PAT and store it as `MONOREPO_SPLIT_TOKEN`

Create a **fine-grained PAT** scoped to both `birdcar/markdown-laravel` and
`birdcar/markdown-filament` with **Contents: Read and write**, then store it as a
secret on this repo:

```bash
gh secret set MONOREPO_SPLIT_TOKEN --repo birdcar/markdown-php --body "<PAT>"
```

> A classic PAT with `repo` scope also works but is broader than necessary.

### 3. Submit all three packages to Packagist + enable auto-update

For each of `birdcar/markdown-php`, `birdcar/markdown-laravel`, and
`birdcar/markdown-filament`:

- Submit the repo URL at <https://packagist.org/packages/submit>.
- Install the **Packagist GitHub App** (or add the per-repo webhook) so Packagist
  re-crawls on every push/tag.

> Submit the split repos **after** their first tag exists (see "Cutting a
> release"), or click "Update" once it does.

### 4. Enable branch protection on `main`

Require the `tests` checks to pass before the Release PR can merge. The check
`context` names must match the job names emitted by `tests.yml`; list them after
one CI run with:

```bash
gh api repos/birdcar/markdown-php/commits/main/check-runs --jq '.check_runs[].name'
```

Then set protection (adjust the contexts to the real names):

```bash
gh api -X PUT repos/birdcar/markdown-php/branches/main/protection \
  -F required_status_checks.strict=true \
  -F 'required_status_checks.checks[][context]=core (PHP 8.4 • stable)' \
  -F enforce_admins=false \
  -F required_pull_request_reviews= \
  -F restrictions=
```

## Cutting a release

Ongoing releases are fully automated. The only manual action is merging the
Release PR.

1. Land Conventional Commits on `main` as usual.
2. Release Please opens (and keeps updating) a Release PR. Review its diff — the
   computed version and the `CHANGELOG.md` entry.
3. Merge the Release PR. Watch the run:
   ```bash
   gh run watch
   ```
4. The pipeline tags `vX.Y.Z`, re-runs the test matrix, splits both packages at
   that tag, and Packagist publishes all three. Nothing else is manual.

### The first release (forcing `v0.1.0`)

The manifest bootstraps at `0.0.0`. To make the first release `0.1.0` (rather
than letting the bump rules compute it), add a `Release-As` footer to any commit
merged to `main`:

```
chore: prepare first release

Release-As: 0.1.0
```

This is the preferred mechanism — it requires no config churn. Do **not** add a
`release-as` field to `release-please-config.json`; the footer is removed by the
next normal commit, whereas a config value would have to be reverted in a
follow-up PR.

## Versioning rules

The repo is pre-1.0, configured with `bump-minor-pre-major: true` and
`bump-patch-for-minor-pre-major: false`:

| Commit type | Example | Bump |
| --- | --- | --- |
| `fix:` | `fix: correct footnote rendering` | patch (`0.1.0` → `0.1.1`) |
| `feat:` | `feat: add callout directive` | minor (`0.1.0` → `0.2.0`) |
| breaking (`feat!:` / `BREAKING CHANGE:`) | — | minor while pre-1.0 (`0.1.0` → `0.2.0`) |
| `chore:`, `docs:`, `ci:`, `refactor:`, `test:` | — | no release on their own |

Tags are plain `v0.1.0` (`include-component-in-tag: false`), not
`birdcar/markdown-php-v0.1.0`.

## Troubleshooting

| Symptom | Likely cause | Fix |
| --- | --- | --- |
| `split` job `403 Forbidden` on push | `MONOREPO_SPLIT_TOKEN` missing or wrong scope | Recreate a PAT with **Contents: write** on both split repos; re-run the failed `split` leg (idempotent at the same tag). |
| Packagist version not updating | Webhook / GitHub App not installed | Install the Packagist GitHub App on the repo, then click "Update" on the package page. |
| `filament` install fails on `self.version` | Only one of the two packages got tagged (partial split failure) | `fail-fast: false` keeps both legs independent — re-run the failed leg at the same tag. |
| No Release PR opens | No `feat:`/`fix:` commits since the last release | Commits must be Conventional. `chore:`/`docs:` alone won't trigger a release. |
| First release tagged `1.0.0` or wrong | `Release-As: 0.1.0` footer omitted | Add the footer to a commit on `main`; verify the Release PR's version before merging. |
