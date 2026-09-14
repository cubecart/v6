#!/usr/bin/env bash
# Build the two shipped JS bundles, minified, with source maps.
#
#   js/vendor/atrium.head.js  jQuery + jquery-migrate                    (<head>)
#   js/vendor/atrium.app.js   js/src/*.js + Alpine plugins + Alpine core (footer)
#
# Output goes to js/vendor/, NOT js/: js/*.js is globbed into $JS_SCRIPTS
# (gui.class.php:192) and run through {combine}, whose JSMin throws on our Alpine
# build and corrupts backtick strings. js/vendor/ is not globbed. The {combine}
# call stays in element.js_foot.php because plugins drop files into js/.
#
# CONCATENATED, not esbuild --bundle: --bundle supplies a CommonJS module/exports
# pair, and jQuery's UMD header would then export itself instead of assigning
# window.jQuery, breaking every plugin that expects the global.
#
# Alpine plugins must precede core: they register on alpine:init, which core fires.
#
# --legal-comments=eof is REQUIRED, not cosmetic: Alpine, focus-trap and jQuery are
# MIT and oblige us to retain their notices.
#
# Target es2020 matches the floor documented in js/src/00-boot.js.
set -euo pipefail
cd "$(dirname "$0")/.."

[ -d node_modules ] || npm install

ESBUILD=./node_modules/.bin/esbuild
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

mkdir -p js/vendor

# ---------------------------------------------------------------- head bundle
cat js/vendor/jquery.min.js  > "$TMP/head.js"
echo ';'                    >> "$TMP/head.js"
cat js/vendor/jquery-migrate.min.js >> "$TMP/head.js"
echo ';'                    >> "$TMP/head.js"

# ----------------------------------------------------------------- app bundle
: > "$TMP/app.js"
for f in js/src/*.js; do
  printf '\n/* ---- %s ---- */\n' "$f" >> "$TMP/app.js"
  cat "$f"                             >> "$TMP/app.js"
  echo ';'                             >> "$TMP/app.js"
done
# Alpine plugins BEFORE Alpine core. See the note above.
for f in js/vendor/alpine-focus.min.js \
         js/vendor/alpine-collapse.min.js \
         js/vendor/alpine-anchor.min.js \
         js/vendor/alpine-persist.min.js \
         js/vendor/alpine.min.js; do
  printf '\n/* ---- %s ---- */\n' "$f" >> "$TMP/app.js"
  cat "$f"                             >> "$TMP/app.js"
  echo ';'                             >> "$TMP/app.js"
done

# Check the readable source, so errors point somewhere human-readable.
if command -v node >/dev/null 2>&1; then
  node --check "$TMP/head.js"
  node --check "$TMP/app.js"
  echo "syntax OK"
fi

# Guard the JSMin rule at build time, not in code review.
python3 "$(dirname "$0")/check-no-template-literals.py"

build_one() {
  local src="$1" out="$2"
  "$ESBUILD" "$src" \
    --minify \
    --target=es2020 \
    --legal-comments=eof \
    --sourcemap \
    --sources-content=true \
    --outfile="$out" \
    --log-level=warning
  if command -v node >/dev/null 2>&1; then
    node --check "$out"
  fi
  printf '  %-28s %7s bytes (from %s)\n' "$(basename "$out")" "$(wc -c < "$out" | tr -d ' ')" "$(wc -c < "$src" | tr -d ' ')"
}

build_one "$TMP/head.js" js/vendor/atrium.head.js
build_one "$TMP/app.js"  js/vendor/atrium.app.js

# "sources" would otherwise name a mktemp path; sourcesContent has the real code.
for m in js/vendor/atrium.head.js.map js/vendor/atrium.app.js.map; do
  php -r '
    $f = $argv[1]; $j = json_decode(file_get_contents($f), true);
    $j["sources"] = array_map(fn($s) => "atrium://" . basename($s), $j["sources"]);
    file_put_contents($f, json_encode($j));
  ' "$m"
done

# Restamp: the bundles feed the cache-buster hash (build/stamp.php).
php "$(dirname "$0")/stamp.php"
