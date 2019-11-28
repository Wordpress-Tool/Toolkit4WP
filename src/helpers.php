<?php

/**
 * Useful functions.
 *
 * @author  Viktor Szépe <viktor@szepe.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

use Traversable;

/**
 * Create an HTML element with pure PHP.
 *
 * @see https://www.w3.org/TR/html/syntax.html#void-elements
 *
 * @param string $name Tag name.
 * @param array<string, string|null> $attrs HTML attributes.
 * @param string|\Traversable $content Raw HTML content.
 * @return string
 * @throws \Exception
 */
function tag(string $name = 'div', array $attrs = [], $content = ''): string
{
    $voids = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img',
        'input', 'link', 'meta', 'param', 'source', 'track', 'wbr', ];

    // Void elements.
    $name = \sanitize_key($name);
    $isVoid = \in_array($name, $voids, true);
    if ($content instanceof Traversable) {
        $content = \implode(\iterator_to_array($content));
    }
    if ($isVoid && $content !== '') {
        throw new \Exception('Void HTML element with content.');
    }

    // Attributes.
    $attrString = '';
    foreach ($attrs as $attrName => $attrValue) {
        $attrName = \strtolower($attrName);
        $attrName = \preg_replace('/[^a-z0-9-]/', '', $attrName);
        // Boolean Attributes.
        if ($attrValue === null) {
            $attrString .= \sprintf(' %s', $attrName);
            continue;
        }
        $attrString .= \sprintf(' %s="%s"', $attrName, esc_attr($attrValue));
    }

    // Element.
    $html = \sprintf('<%s%s>', $name, $attrString);
    if (! $isVoid) {
        $html .= \sprintf('%s</%s>', $content, $name);
    }

    return $html;
}

/**
 * Create an HTML list.
 *
 * @see https://www.w3.org/TR/html/syntax.html#void-elements
 *
 * @param string $name Parent tag name.
 * @param array<string, string> $attrs HTML attributes of the parent.
 * @param array<int, string> $childrenContent Raw HTML content of children.
 * @param string $childTagName Name of children tags.
 * @return string
 */
function tagList(
    string $name = 'ul',
    array $attrs = [],
    array $childrenContent = [],
    string $childTagName = 'li'
): string {
    $content = \array_map(
        static function (string $child) use ($childTagName): string {
            return \sprintf('<%s>%s</%s>', $childTagName, $child, $childTagName);
        },
        $childrenContent
    );

    return tag($name, $attrs, \implode('', $content));
}