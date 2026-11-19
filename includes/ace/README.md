# Ace

Version 1.45.2, from the official package: https://registry.npmjs.org/ace-builds/-/ace-builds-1.45.2.tgz
Licensed under the BSD 3-Clause License (see LICENSE).
Source code: https://github.com/ajaxorg/ace

CubeCart uses it for code boxes in the admin: `<textarea class="cc-code">`, set up in `admin/skins/default/js/admin.js`, and the code views of the email editor and the email templates.

Only part of the package is included, as its minified `src-min-noconflict` files:

- the editor (`ace.js`);
- the languages PHP, Smarty, HTML, CSS, JavaScript, JSON, SQL and XML: each one's `mode-`, `worker-` (where it has one) and `snippets/` file, and `worker-base.js`;
- the themes `github` (light) and `tomorrow_night` (dark), which the admin switches between with the colour scheme;
- the extensions `searchbox`, `language_tools`, `error_marker`, `prompt` and `keybinding_menu`.

An extension that needs another language or theme can ship that file itself and tell Ace where it is with `ace.config.setModuleUrl()`.

To update, replace each file with the same file from a newer package.
