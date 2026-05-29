# Implementation Spec: Packagist Publishing for markdown-php - Phase 4 (HUMAN GATE)

**Contract**: ./contract.md
**Estimated Effort**: S (mostly manual, account-bound)
**Prereqs**: Phase 3 (release pipeline + RELEASING.md)

## Technical Approach

This phase is a **human gate**, not automation. It performs the one-time provisioning that lives in your GitHub/Packagist accounts (and therefore can't be done by an agent), then triggers the first real release and verifies the whole chain end-to-end. Execute the steps below in order — most are single `gh` commands. An automated runner (`/ideation:execute-spec`, autopilot) should **stop here and surface this checklist** rather than attempt the account-bound steps.

## Feedback Strategy

**Inner-loop command**: `composer require birdcar/markdown-filament` in a scratch directory (the ultimate proof)

**Playground**: A throwaway project in `/tmp` + `gh`/Packagist web UI.

**Why this approach**: "Published correctly" is only true when an unrelated project can install the full dependency chain from Packagist with no path repos.

## Implementation Steps

### 1. Create the two split (mirror) repositories

```bash
gh repo create birdcar/markdown-laravel  --public \
  --description "Laravel integration for Birdcar Flavored Markdown (read-only mirror of birdcar/markdown-php)"
gh repo create birdcar/markdown-filament --public \
  --description "Filament integration for Birdcar Flavored Markdown (read-only mirror of birdcar/markdown-php)"
```

Leave them empty — the split action force-pushes. Optionally add a notice to each repo's About that it's a read-only mirror.

### 2. Create the split PAT and store it as a secret

Create a **fine-grained PAT** scoped to `birdcar/markdown-laravel` and `birdcar/markdown-filament` with **Contents: Read and write**. Then:

```bash
gh secret set MONOREPO_SPLIT_TOKEN --repo birdcar/markdown-php --body "<PAT>"
```

> A classic PAT with `repo` scope also works but is broader than necessary.

### 3. Submit all three packages to Packagist + enable auto-update

For each of `birdcar/markdown-php`, `birdcar/markdown-laravel`, `birdcar/markdown-filament`:

- Submit at https://packagist.org/packages/submit using the repo URL.
- Install the **Packagist GitHub App** (or add the per-repo webhook) so Packagist re-crawls on every push/tag.

> Submit the split repos **after** their first tag exists (step 5), or re-trigger an update once it does.

### 4. Enable branch protection on `main`

Require the `tests` checks to pass before merging the Release PR:

```bash
gh api -X PUT repos/birdcar/markdown-php/branches/main/protection \
  -F required_status_checks.strict=true \
  -F 'required_status_checks.checks[][context]=core (PHP 8.4 • stable)' \
  -F enforce_admins=false \
  -F required_pull_request_reviews= \
  -F restrictions=
```

> Adjust the check `context` names to match the job names emitted by `tests.yml`. List them with `gh api repos/birdcar/markdown-php/commits/main/check-runs --jq '.check_runs[].name'` after one CI run.

### 5. Cut the first release (v0.1.0)

1. Ensure a `Release-As: 0.1.0` footer is on a commit merged to `main` (or `release-as` is set in `release-please-config.json` for this first run).
2. Release Please opens a Release PR. Review it (version `0.1.0`, CHANGELOG).
3. Merge it. Watch the run:
   ```bash
   gh run watch
   ```
4. The pipeline tags `v0.1.0`, re-runs tests, and splits both packages.

### 6. Verify end-to-end

```bash
# Tags landed on the split repos
gh api repos/birdcar/markdown-laravel/tags  --jq '.[].name'
gh api repos/birdcar/markdown-filament/tags --jq '.[].name'   # expect v0.1.0

# Packagist shows the version (after webhook crawl)
curl -s https://repo.packagist.org/p2/birdcar/markdown-filament.json | jq '.packages | keys, (.[] | map(.version))'

# The real test: fresh install of the whole chain, no path repos
cd "$(mktemp -d)"
composer init --name=scratch/test -n
composer require birdcar/markdown-filament
ls vendor/birdcar  # expect markdown-php, markdown-laravel, markdown-filament
```

## Testing Requirements

### Manual Testing (maps to contract success criteria)

- [ ] `v0.1.0` tag + GitHub Release exist on `birdcar/markdown-php`.
- [ ] `v0.1.0` tag exists on both split repos.
- [ ] Packagist lists `0.1.0` for all three packages.
- [ ] `composer require birdcar/markdown-filament` in a clean dir resolves filament → laravel → php from Packagist.
- [ ] The installed `vendor/birdcar/markdown-php` archive contains `src/` but not `packages/`, `docs/`, `tests/`, or `spec/`.

## Failure Modes

| Component | Failure Mode | Trigger | Impact | Mitigation |
| --- | --- | --- | --- | --- |
| split job | `403 Forbidden` | PAT missing or wrong scope | Tagged but unpublished | Recreate PAT with Contents:write on both split repos; re-run the `split` job (idempotent). |
| Packagist | Version not appearing | Webhook/App not installed | Stale Packagist | Install Packagist GitHub App; click "Update" manually once. |
| self.version | filament install fails | laravel split tag missing | Chain broken | Ensure both legs of `split` succeeded; re-run failed leg at same tag. |
| branch protection | Wrong check context names | Job names differ from guess | PR can't merge / protection ineffective | Read actual check-run names first, then set protection. |
| first release | Version is `1.0.0` or wrong | `Release-As` omitted, no bump rules | Wrong initial tag | Use `Release-As: 0.1.0`; verify the Release PR before merging. |

## Open Items

- [ ] Decide public vs. private for the split repos (assumed public for open-source Packagist).
- [ ] Confirm exact `tests.yml` job/check names before configuring branch protection.

---

_This phase is a human gate. An automated executor should present this checklist and stop; the steps require your GitHub and Packagist accounts._
