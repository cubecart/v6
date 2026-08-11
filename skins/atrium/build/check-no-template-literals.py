#!/usr/bin/env python3
"""
Fail the build if a backtick appears in real CODE in js/src/.

WHY: CubeCart minifies skin JS with JSMin (Crockford 2002), which has no concept
of template literals. A backtick string containing an apostrophe throws an
uncaught PHP exception — a white page for every customer on every page. Other
backtick strings are silently corrupted (spaces eaten, // read as a comment).

Backticks in COMMENTS are fine and common (`like this`), so this strips comments
first rather than pattern-matching line prefixes — the naive version flagged
block-comment continuation lines that do not start with '*'.
"""
import sys, glob, os

def strip_comments(src):
    """Remove // and /* */ comments, preserving string contents and line count."""
    out = []
    i, n = 0, len(src)
    state = None          # None | "'" | '"' | '`' | '//' | '/*'
    while i < n:
        ch = src[i]
        nxt = src[i+1] if i + 1 < n else ''

        if state is None:
            if ch == '/' and nxt == '/':
                state = '//'; i += 2; continue
            if ch == '/' and nxt == '*':
                state = '/*'; i += 2; continue
            if ch in ('"', "'", '`'):
                state = ch; out.append(ch); i += 1; continue
            out.append(ch); i += 1; continue

        if state == '//':
            if ch == '\n':
                state = None; out.append(ch)
            i += 1; continue

        if state == '/*':
            if ch == '*' and nxt == '/':
                state = None; i += 2; continue
            if ch == '\n':
                out.append(ch)      # keep line numbers aligned
            i += 1; continue

        # inside a string
        out.append(ch)
        if ch == '\\':
            if i + 1 < n:
                out.append(src[i+1]); i += 2; continue
        elif ch == state:
            state = None
        i += 1
    return ''.join(out)

root = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
bad = []
for path in sorted(glob.glob(os.path.join(root, 'js', 'src', '*.js'))):
    code = strip_comments(open(path, encoding='utf8').read())
    for lineno, line in enumerate(code.split('\n'), 1):
        if '`' in line:
            bad.append((os.path.relpath(path, root), lineno, line.strip()[:80]))

if bad:
    print('ERROR: template literal in js/src — JSMin will corrupt or fatal on this.', file=sys.stderr)
    print('       See the house rule at the top of js/src/00-boot.js.\n', file=sys.stderr)
    for f, l, t in bad:
        print(f'  {f}:{l}  {t}', file=sys.stderr)
    sys.exit(1)

print(f'no template literals in {len(glob.glob(os.path.join(root, "js", "src", "*.js")))} source files')
