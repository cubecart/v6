# Flags

SVG country flags from [lipis/flag-icons](https://github.com/lipis/flag-icons) (4x3 set),
copyright (c) 2013 Panayiotis Lipiridis, MIT licensed — see `LICENSE` in this directory.
MIT is compatible with CubeCart's GPL-3.0; the licence text must be retained, which is why
`LICENSE` sits here rather than being folded into the skin's own notice.

Filenames are **ISO 3166-1 alpha-2 country codes**, not CubeCart language codes. CubeCart
names languages `<lang>-<COUNTRY>` (`en-GB`, `pt-BR`), so `box.language.php` maps a language
code to a flag by stripping everything up to the first `-` and lower-casing the remainder.

Only the 32 countries matching CubeCart's shipped language packs are included, plus `xx.svg`
as the unknown placeholder. If a merchant installs a language with no flag here, the template
falls back to core's `language/flags/<code>.png` via `onerror`.

To add one: copy `flags/4x3/<cc>.svg` from the upstream repo. Do not hand-edit — re-copy on update.
