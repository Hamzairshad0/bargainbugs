<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Turns an eBay listing description (often a full HTML template with styles,
 * scripts and tracking) into clean, safe, simple HTML for the website.
 */
class HtmlSanitizer
{
    private const ALLOWED = ['p', 'br', 'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'u', 'h2', 'h3', 'h4', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'blockquote', 'hr'];

    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'link', 'meta', 'img', 'svg', 'video', 'audio', 'noscript', 'head', 'title'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        // Plain text description → paragraphs
        if ($html === strip_tags($html)) {
            $parts = preg_split("/\R{2,}/", e($html));

            return '<p>'.implode('</p><p>', array_map(fn ($p) => nl2br(trim($p)), $parts)).'</p>';
        }

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();

        $root = $doc->getElementById('__root') ?? $doc->documentElement;
        if (! $root) {
            return e(strip_tags($html));
        }

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        // Collapse empty paragraphs / excessive breaks
        $out = preg_replace('#<(p|li|td|th|h[2-4])>\s*(&nbsp;|\s|<br>)*\s*</\1>#i', '', $out);
        $out = preg_replace('#(<br>\s*){3,}#i', '<br><br>', $out);

        return trim($out);
    }

    private static function walk(DOMNode $node): void
    {
        $children = iterator_to_array($node->childNodes);
        foreach ($children as $child) {
            if (! $child instanceof DOMElement) {
                if ($child->nodeType === XML_COMMENT_NODE) {
                    $node->removeChild($child);
                }

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);

                continue;
            }

            self::walk($child);

            if ($tag === 'div' || $tag === 'section' || $tag === 'article') {
                // keep block structure as paragraphs
                $p = $child->ownerDocument->createElement('p');
                while ($child->firstChild) {
                    $p->appendChild($child->firstChild);
                }
                $node->replaceChild($p, $child);

                continue;
            }

            if ($tag === 'h1' || $tag === 'h5' || $tag === 'h6') {
                $h = $child->ownerDocument->createElement('h3');
                while ($child->firstChild) {
                    $h->appendChild($child->firstChild);
                }
                $node->replaceChild($h, $child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED, true)) {
                // unwrap: keep the text, drop the tag (span, font, a, center...)
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            while ($child->attributes->length) {
                $child->removeAttribute($child->attributes->item(0)->nodeName);
            }
        }
    }
}
