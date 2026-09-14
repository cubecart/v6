<?php
if (PHP_SAPI !== 'cli') {
    // skins/ is web-servable; this script is developer tooling only.
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}
/**
 * Split Tailwind's inline source map into css/tailwind.css.map.
 *
 * The CLI's --map only INLINES, as a base64 data URI, which takes the shipped
 * stylesheet from 63 KB to 317 KB for every visitor. External keeps it at 63 KB
 * and devtools still fetch the map on demand.
 */
$root = dirname(__DIR__);
$css  = $root . '/css/tailwind.css';
$map  = $root . '/css/tailwind.css.map';

$c = file_get_contents($css);
$pattern = '#/\*\# sourceMappingURL=data:application/json;base64,([A-Za-z0-9+/=]+) \*/\s*$#';

if (!preg_match($pattern, $c, $m)) {
    // Already external, or the CLI stopped inlining.
    echo "no inline source map in css/tailwind.css, leaving it alone\n";
    exit(0);
}

$json = base64_decode($m[1], true);
if ($json === false || json_decode($json) === null) {
    fwrite(STDERR, "inline source map is not valid base64 JSON\n");
    exit(1);
}

file_put_contents($map, $json);
file_put_contents($css, preg_replace($pattern, "/*# sourceMappingURL=tailwind.css.map */\n", $c));

printf("css/tailwind.css %d bytes + css/tailwind.css.map %d bytes\n", filesize($css), filesize($map));
