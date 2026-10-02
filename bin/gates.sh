#!/usr/bin/env bash
# Gates — Rector + Pint + PHPStan; rebuild dist/ when resources/js changed
# (unpushed commits, edits or untracked files vs. the upstream merge-base).
set -euo pipefail
cd "$(dirname "$0")/.."
composer clean
if base=$(git merge-base HEAD '@{u}' 2>/dev/null || git merge-base HEAD origin/master 2>/dev/null); then
    changed=$( { git diff --name-only --no-renames "$base" -- resources/js || echo unknown; git ls-files --others --exclude-standard -- resources/js || true; } )
else
    changed="no base found — rebuilding to be safe"
fi
if [ -n "$changed" ]; then
    npm run prod
    echo "dist/ rebuilt — commit it together with the source change"
else
    echo "resources/js unchanged — npm run prod skipped"
fi
echo "✓ gates green"
