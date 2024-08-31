<?php

declare(strict_types=1);

namespace LWP\Common\String;

class EnclosedCharsIterator implements \Iterator
{
    public const DEFAULT_ESCAPE_CHAR = '\\';

    public const CURRENT_STRIPPED_OFF = 1;


    private array $chars = [
        // Format:
        // opening char => [closing char, escaped]
        '"' => ['"', true],
        '\'' => ['\'', true],
    ];
    private string $opening_chars;
    private int $position = 0;
    private array $segments = [];
    private int $iterator_num = 0;
    private int $max_position;


    public function __construct(
        private readonly string $str,
        ?array $chars = null,
        public readonly ?int $flags = null,
    ) {

        $this->max_position = strlen($str);

        if ($chars) {
            $this->chars = $chars;
        }

        $this->opening_chars = implode(array_keys($this->chars));
    }


    // Gets current segment.

    public function current(): string
    {

        $data = $this->getCurrentData();

        return (!($this->flags & self::CURRENT_STRIPPED_OFF) || !$data['is_inside_chars'])
            ? $data['text']
            : substr($data['text'], 1, -1);
    }


    // Gets data array for the current segment.

    public function getCurrentData(): array
    {

        return $this->segments[$this->iterator_num];
    }


    // Tells if current segment contains enclosing chars.

    public function hasEnclosingChars(): bool
    {

        $data = $this->getCurrentData();

        return $data['is_inside_chars'];
    }


    // Gets opening char for the current segment.

    public function getOpeningChar(): ?string
    {

        $data = $this->getCurrentData();

        return ($data['is_inside_chars'])
            ? $data['text'][0]
            : null;
    }


    // Gets closing char for the current segment.

    public function getClosingChar(): ?string
    {

        $data = $this->getCurrentData();

        return ($data['is_inside_chars'])
            ? substr($data['text'], -1)
            : null;
    }


    // Gets start position for the current segment.

    public function getStartPosition(): int
    {

        $data = $this->getCurrentData();

        return $data['start_pos'];
    }


    // Gets end position for the current segment.

    public function getEndPosition(): int
    {

        $data = $this->getCurrentData();

        return $data['end_pos'];
    }


    // Return the key of the current segment.

    public function key(): int
    {

        return $this->iterator_num;
    }


    // Move forward and fetch new segments, if required.

    public function next(): void
    {

        $this->iterator_num++;

        if (!isset($this->segments[$this->iterator_num]) && $this->position < $this->max_position) {
            $this->fetchSegments();
        }
    }


    // Rewind the Iterator and prep the initial segments.

    public function rewind(): void
    {

        $this->position = 0;
        $this->fetchSegments();
    }


    // Checks if current position is valid.

    public function valid(): bool
    {

        return (isset($this->segments[$this->iterator_num]) || $this->position < $this->max_position);
    }


    // Gets the next closest opening char.

    public function getNextClosestChar(int $offset = 0): ?array
    {

        $positions = [];

        foreach ($this->chars as $opening_char => $char_defs) {

            // Escaping is on.
            if (!empty($char_defs[1])) {

                $pos = call_user_func_array([Str::class, 'posNotPrecededBy'], [
                    $this->str,
                    $opening_char,
                    self::DEFAULT_ESCAPE_CHAR,
                    ($this->position + $offset),
                ]);

                // Escaping is off.
            } else {

                $pos = call_user_func_array('strpos', [
                    $this->str,
                    $opening_char,
                    ($this->position + $offset),
                ]);
            }

            if ($pos !== false) {
                $positions[$opening_char] = $pos;
            }
        }

        if (empty($positions)) {
            return null;
        }

        $closest_pos = min($positions);

        return [
            'pos' => $closest_pos,
            'char' => array_search($closest_pos, $positions),
        ];
    }


    // Complement further segments.

    public function fetchSegments(): void
    {

        $new_segments = 0;
        $offset = 0;

        do {

            if ($open_pos = $this->getNextClosestChar($offset)) {

                $offset = 0;

                // Escaping is on.
                if (!empty($this->chars[$open_pos['char']][1])) {

                    $close_pos = call_user_func_array([Str::class, 'posNotPrecededBy'], [
                        $this->str,
                        $this->chars[$open_pos['char']][0],
                        self::DEFAULT_ESCAPE_CHAR,
                        ($open_pos['pos'] + 1),
                    ]);

                    // Escaping is off.
                } else {

                    $close_pos = call_user_func_array('strpos', [
                        $this->str,
                        $this->chars[$open_pos['char']][0],
                        ($open_pos['pos'] + 1),
                    ]);
                }

                // Found closing character.
                if ($close_pos) {

                    $unquoted = substr($this->str, $this->position, ($open_pos['pos'] - $this->position));

                    if (!empty($unquoted)) {

                        // Unquoted.
                        $this->segments[] = [
                            'text' => $unquoted,
                            'is_inside_chars' => false,
                            'start_pos' => $this->position,
                            'end_pos' => ($open_pos['pos'] - 1),
                        ];
                    }

                    // Quoted.
                    $this->segments[] = [
                        'text' => substr($this->str, $open_pos['pos'], ($close_pos - $open_pos['pos'] + 1)),
                        'is_inside_chars' => true,
                        'start_pos' => $open_pos['pos'],
                        'end_pos' => $close_pos,
                    ];

                    $new_segments++;

                    $this->position = ($close_pos + 1);

                    // Closing character not found.
                } else {

                    $offset = ($open_pos['pos'] - $this->position + 1);
                }

            } else {

                $this->segments[] = [
                    'text' => substr($this->str, $this->position),
                    'is_inside_chars' => false,
                    'start_pos' => $this->position,
                    'end_pos' => $this->max_position,
                ];

                $new_segments++;

                $this->position = $this->max_position;
            }

        } while (!$new_segments);
    }
}
