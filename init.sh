#!/bin/bash
set -euo pipefail

echo "=== markdown-php harness init ==="

# spec/ is a git submodule (conformance fixtures used by tests/FixtureTestCase.php)
echo "--- initializing spec/ submodule ---"
git submodule update --init --recursive

echo "--- core (birdcar/markdown-php) ---"
composer install --no-interaction --no-progress
composer validate --strict --no-check-lock
composer test

echo "--- validating package manifests (publishable?) ---"
( cd packages/laravel  && composer validate --strict --no-check-lock )
( cd packages/filament && composer validate --strict --no-check-lock )

echo ""
echo "=== Baseline verification complete ==="
echo ""
echo "NOTE: the full Laravel/Filament test matrix (PHP 8.2-8.5 x Laravel 12-13 x"
echo "      lowest/stable + Filament v4) runs in CI: .github/workflows/ci.yml"
echo "      To test a package locally:  cd packages/<pkg> && composer install && composer test"
echo ""
echo "Next steps:"
echo "1. Read feature_list.json for current feature state"
echo "2. For release work, read RELEASING.md and the 'Publishing rules' in CLAUDE.md"
echo "3. Pick ONE unfinished feature; implement only that; re-verify before claiming done"
