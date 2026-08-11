#!/usr/bin/env bash
# Compile Atrium's stylesheet. Run from anywhere.
#
# The output css/tailwind.css IS COMMITTED and IS shipped. Merchants never run
# this — they have no Node, and requiring one would make the skin uninstallable.
set -euo pipefail
cd "$(dirname "$0")/.."

[ -d node_modules ] || npm install

npx tailwindcss -i ./css/src/input.css -o ./css/tailwind.css --minify
php ./build/stamp.php

echo "built css/tailwind.css ($(wc -c < ./css/tailwind.css) bytes)"
