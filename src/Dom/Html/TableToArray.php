<?php

declare(strict_types=1);

namespace LWP\Dom\Html;

class TableToArray
{
    private array $keys;
    private array $result;
    private int $rows_found = 0;
    private bool $capture_keys_block = false;
    private bool $array_formed = false;
    private int $tr_offset = 0;
    private int $tbody_offset = 0;


    // $table - HTML table DOMElement
    // $capture_keys - when true, values from the first found row will be used as keys in the rest of the rows

    public function __construct(
        private \DOMElement $table,
        private bool $capture_keys = false,
    ) {

        $this->result = self::getResultArrayStructure();
    }


    // Gets result array carcass.

    public static function getResultArrayStructure(): array
    {

        return [
            '__index' => [
                'first_row_container' => null,
            ],
            'header' => [],
            'rows' => [],
            'body' => [],
            'footer' => [],
        ];
    }


    // Get rows from $elem DOMElement.
    // $node_type - containing node type, eg. THEAD or TBODY
    // $offset - offset for the loop
    // $length - the length for the loop to run

    private function getRows(\DOMElement $elem, ?string $node_type = null, int $offset = 0, int $length = -1): array
    {

        $rows = [];
        $i = -1;

        foreach ($elem->childNodes as $node) {

            if ($node->nodeName == 'tr') {

                $i++;

                if ($i < $offset) {
                    continue;
                }

                $cells = $this->getCells($node);
                $this->rowFound($cells, $node_type);

                $rows[] = $cells;

                if (($i + 1) == $length) {
                    break;
                }
            }
        }

        return $rows;
    }


    // Register a row found.
    // $node_type - containing node type, eg. THEAD or TBODY

    private function rowFound(array $cells, ?string $node_type = null): void
    {

        if (!$this->capture_keys_block) {

            if (empty($this->keys) && $this->capture_keys) {

                $this->setKeys($cells);
            }

            if ($this->rows_found < 1) {

                $this->result['__index']['first_row_container'] = $node_type;
            }
        }

        $this->rows_found++;
    }


    // Get cell from $elem DOMElement.

    private function getCells(\DOMElement $elem): array
    {

        $i = 0;
        $cells = [];

        foreach ($elem->childNodes as $node) {

            if ($node->nodeType == XML_ELEMENT_NODE && ($node->nodeName == 'th' || $node->nodeName == 'td')) {

                $k = (!empty($this->keys) && $this->capture_keys)
                    ? $this->keys[$i]
                    : $i;

                // remove no-breake space and whitespace
                $cells[$k] = trim(preg_replace('/[\xC2\xA0]/', ' ', $node->nodeValue));

                $i++;
            }
        }

        return $cells;
    }


    // Set keys to be used for rows.

    public function setKeys(array $keys): void
    {

        $this->keys = $keys;
    }


    // Go through the table DOM and write the result array.

    public function toArray(bool $first_row = false): array
    {

        $thead_found = $tbody_found = $rows_found = 0;

        foreach ($this->table->childNodes as $child) {

            // in valid HTML TABLE, there can be only one THEAD
            if ($child->nodeName == 'thead' && !$thead_found) {

                $rows = $this->getRows($child, 'thead', count($this->result['header']));

                $this->result['header'] = array_merge($this->result['header'], $rows);

                $thead_found++;
            }

            // in valid HTML TABLE, there can be only one TFOOT
            if ($child->nodeName == 'tfoot' && empty($this->result['footer'])) {

                $this->result['footer'] = $this->getRows($child, 'tfoot');
            }

            // capture TR children, no matter if TBODY is available or not
            if ($child->nodeType == XML_ELEMENT_NODE && $child->nodeName == 'tr') {

                $rows_found++;

                if ($this->tr_offset < $rows_found) {

                    $cells = $this->getCells($child);
                    $this->rowFound($cells, 'tr');

                    $this->result['rows'][] = $cells;

                    if ($first_row) {

                        $this->tr_offset++;

                        return $cells;
                    }
                }
            }

            // capture child TBODY
            if ($child->nodeName == 'tbody') {

                $length = ($first_row) ? 1 : -1;
                $offset = 0;

                if ($tbody_found == 0 && $this->tbody_offset > 0) {
                    $offset = 1;
                }

                $rows = $this->getRows($child, 'tbody', $offset, $length);

                if (!empty($rows)) {

                    $this->result['body'][] = $rows;
                }

                $tbody_found++;

                if ($first_row) {

                    $this->tbody_offset++;

                    return $rows[0];
                }
            }
        }

        $this->array_formed = true;

        return $this->result;
    }


    // Get header based on indexed information.
    // $first_row_only - when true, and header is available, and it contains multiple rows, will return first row only
    // $first_row - should it retrieve any first row, when header is not available

    private function getHeaderByIndex(bool $first_row_only = false, bool $first_row = false): ?array
    {

        $container = $this->result['__index']['first_row_container'];

        if (!$container) {
            return null;
        }

        if ($container == 'thead') {

            return (!$first_row_only)
                ? $this->result['header']
                : $this->result['header'][0];
        }

        if ($container == 'tbody' && $first_row) {
            return $this->result['body'][0][0];
        }

        return null;
    }


    // Get header row(s).
    // $first_row_only - should it retrieve the first row only, when header contains multiple rows
    // $first_row_any - should it retrieve any first row, when header is not available

    public function getHeader($first_row_only = false, $first_row_any = false): ?array
    {

        if ($this->array_formed) {

            return $this->getHeaderByIndex($first_row_only, $first_row_any);
        }

        foreach ($this->table->childNodes as $child) {

            if ($child->nodeName == 'thead') {

                $this->result['header'] = $this->getRows($child, 'thead', 0, ($first_row_only) ? 1 : -1);

                if ($first_row_only) {
                    break;
                }
            }
        }

        if (!empty($this->result['header'])) {

            return (!$first_row_only)
                ? $this->result['header']
                : $this->result['header'][0];
        }

        if ($first_row_any) {

            return $this->toArray(true);
        }

        return null;
    }


    // Get full footer rows.

    public function getFooter(): ?array
    {

        if ($this->array_formed) {

            return $this->result['footer'];
        }

        $this->capture_keys_block = true;

        foreach ($this->table->childNodes as $child) {

            if ($child->nodeName == 'tfoot') {

                $this->result['footer'] = $this->getRows($child, 'tfoot');

                // in valid HTML TABLE, there can be only one TFOOT
                break;
            }
        }

        $this->capture_keys_block = false;

        return $this->result['footer'];
    }


    // Get full result array.

    public function getResult(): array
    {

        return $this->result;
    }
}
