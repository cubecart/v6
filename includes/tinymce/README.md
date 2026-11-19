# TinyMCE

Version 8.10.2, from the official package: https://registry.npmjs.org/tinymce/-/tinymce-8.10.2.tgz
Licensed under the GNU General Public License version 2 or later (see license.md), used here with `license_key: 'gpl'`.
Source code: https://github.com/tinymce/tinymce

CubeCart uses it for the email editor (Documents > Email Templates) and for the rich text boxes of the admin: `<textarea class="cc-editor">`, set up in `admin/skins/default/js/admin.editor.js`. CKEditor is kept for extensions that use `class="fck"`.

Only what those screens load is included: the core, the silver theme, the DOM model, the default icons, the anchor, autolink, charmap, fullscreen, image, link, lists, searchreplace and table plugins, the oxide and oxide-dark skins, and the default and dark content styles, as their minified files. To update, replace each file with the same file from a newer package.

The media plugin is left out on purpose: it writes a fixed width and height onto every stored `<video>` and `<iframe>`, which beats a responsive skin.

`langs/` holds the language packs for TinyMCE 8 from the official package https://www.npmjs.com/package/tinymce-i18n (its `langs8` folder), unchanged. The admin controller picks the one that matches the admin's language.
