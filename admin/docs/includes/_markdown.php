<?php
/**
 * Minimal Markdown-to-HTML converter for CubeCart help docs.
 * Supports: headers, paragraphs, tables, ordered/unordered lists, bold, italic, code, tip/note blocks.
 */
function markdown_to_html($text) {
    $lines = preg_split('/\r?\n/', $text);
    $html = '';
    $in_table = false;
    $in_list = false;
    $list_type = '';
    $in_block = false;
    $block_type = '';
    $block_lines = [];
    $paragraph = [];

    $flush_paragraph = function() use (&$html, &$paragraph) {
        if (!empty($paragraph)) {
            $html .= '<p>' . implode("\n", $paragraph) . "</p>\n";
            $paragraph = [];
        }
    };

    $flush_list = function() use (&$html, &$in_list, &$list_type) {
        if ($in_list) {
            $html .= ($list_type === 'ol') ? "</ol>\n" : "</ul>\n";
            $in_list = false;
            $list_type = '';
        }
    };

    $flush_table = function() use (&$html, &$in_table) {
        if ($in_table) {
            $html .= "</table>\n";
            $in_table = false;
        }
    };

    $flush_block = function() use (&$html, &$in_block, &$block_type, &$block_lines) {
        if ($in_block) {
            $class = ($block_type === 'NOTE') ? 'note' : 'tip';
            $html .= '<div class="' . $class . '">' . inline(implode(' ', $block_lines)) . "</div>\n";
            $in_block = false;
            $block_type = '';
            $block_lines = [];
        }
    };

    foreach ($lines as $line) {
        // Blockquote lines (tip/note blocks)
        if (preg_match('/^>\s?(.*)$/', $line, $m)) {
            $flush_paragraph();
            $flush_list();
            $flush_table();
            $inner = trim($m[1]);
            if (preg_match('/^\[!(TIP|NOTE)\]$/', $inner, $bm)) {
                $flush_block();
                $in_block = true;
                $block_type = $bm[1];
            } elseif ($in_block) {
                if ($inner !== '') {
                    $block_lines[] = $inner;
                }
            }
            continue;
        }

        // End blockquote block if we hit a non-blockquote line
        $flush_block();

        // Blank line
        if (trim($line) === '') {
            $flush_paragraph();
            $flush_list();
            $flush_table();
            continue;
        }

        // Headers
        if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $m)) {
            $flush_paragraph();
            $flush_list();
            $flush_table();
            $level = strlen($m[1]);
            $html .= '<h' . $level . '>' . inline(trim($m[2])) . '</h' . $level . ">\n";
            continue;
        }

        // Table separator row (skip)
        if (preg_match('/^\|[\s\-:|]+\|$/', $line)) {
            continue;
        }

        // Table row
        if (preg_match('/^\|(.+)\|$/', $line, $m)) {
            $flush_paragraph();
            $flush_list();
            $cells = array_map('trim', explode('|', trim($m[1])));
            if (!$in_table) {
                $in_table = true;
                $html .= "<table>\n<tr>";
                foreach ($cells as $cell) {
                    $html .= '<th>' . inline($cell) . '</th>';
                }
                $html .= "</tr>\n";
            } else {
                $html .= '<tr>';
                foreach ($cells as $cell) {
                    $html .= '<td>' . inline($cell) . '</td>';
                }
                $html .= "</tr>\n";
            }
            continue;
        }

        // Ordered list
        if (preg_match('/^\d+\.\s+(.+)$/', $line, $m)) {
            $flush_paragraph();
            $flush_table();
            if (!$in_list || $list_type !== 'ol') {
                $flush_list();
                $in_list = true;
                $list_type = 'ol';
                $html .= "<ol>\n";
            }
            $html .= '<li>' . inline(trim($m[1])) . "</li>\n";
            continue;
        }

        // Unordered list
        if (preg_match('/^[-*]\s+(.+)$/', $line, $m)) {
            $flush_paragraph();
            $flush_table();
            if (!$in_list || $list_type !== 'ul') {
                $flush_list();
                $in_list = true;
                $list_type = 'ul';
                $html .= "<ul>\n";
            }
            $html .= '<li>' . inline(trim($m[1])) . "</li>\n";
            continue;
        }

        // Paragraph text
        $flush_list();
        $flush_table();
        $paragraph[] = inline($line);
    }

    $flush_block();
    $flush_paragraph();
    $flush_list();
    $flush_table();

    return $html;
}

function inline($text) {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    // Code (backticks) - process first to protect contents
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // Bold
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    // Italic
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    return $text;
}
