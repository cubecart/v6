#!/usr/bin/env bash
# Copy MIT vendor libraries from node_modules into js/vendor/ (which IS shipped).
#
# js/vendor/ is NOT auto-globbed by CubeCart: the loader glob is
#   cc_glob('skins/<skin>/{js,scripts}/*.js')   (classes/gui.class.php:192)
# which is non-recursive. Every file here needs a hand-written <script> tag in
# templates/element.js_foot.php (or element.js_head.php for jQuery).
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p js/vendor

cp node_modules/alpinejs/dist/cdn.min.js            js/vendor/alpine.min.js
cp node_modules/@alpinejs/focus/dist/cdn.min.js     js/vendor/alpine-focus.min.js
cp node_modules/@alpinejs/collapse/dist/cdn.min.js  js/vendor/alpine-collapse.min.js
cp node_modules/@alpinejs/anchor/dist/cdn.min.js    js/vendor/alpine-anchor.min.js
cp node_modules/@alpinejs/persist/dist/cdn.min.js   js/vendor/alpine-persist.min.js
cp node_modules/jquery/dist/jquery.min.js           js/vendor/jquery.min.js
cp node_modules/jquery-migrate/dist/jquery-migrate.min.js js/vendor/jquery-migrate.min.js

{
  echo "Vendored libraries — all MIT, all safe to redistribute under GPL-3.0."
  echo "Regenerate with build/vendor.sh. Do not hand-edit."
  echo
  for p in alpinejs @alpinejs/focus @alpinejs/collapse @alpinejs/anchor @alpinejs/persist jquery jquery-migrate; do
    printf '%-22s %s\n' "$p" "$(node -p "require('./node_modules/$p/package.json').version")"
  done
} > js/vendor/VERSIONS.txt

echo "vendored:"; ls -1 js/vendor/
