#!/usr/bin/env bash
# Rebuild on change. Unminified, so the browser inspector stays readable.
set -euo pipefail
cd "$(dirname "$0")/.."
[ -d node_modules ] || npm install
exec npx tailwindcss -i ./css/src/input.css -o ./css/tailwind.css --watch
