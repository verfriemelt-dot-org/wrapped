<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli\Argument;

use function array_is_list;

use RuntimeException;

use function str_starts_with;

class ArgvParser
{
    /**
     * @var string[]
     */
    private array $args = [];

    /**
     * @var string[]
     */
    private array $short = [];

    /**
     * @var string[]
     */
    private array $long = [];

    private int $pos = 1;

    /**
     * @param string[] $argv $_SERVER['argv']
     */
    public function __construct(
        readonly public array $argv
    ) {
        if (count($this->argv) === 0 || !array_is_list($argv)) {
            throw new RuntimeException('argv expected to be an list with at least 1 element');
        }

        $this->parse();
    }

    private function parse(): void
    {
        $args = $this->argv;

        while ($input = $this->consume()) {
            if (!str_starts_with($input, '-')) {
                $this->args[] = $input;
                continue;
            }

            if (str_starts_with($input, '--')) {
                $this->long[] = $input;
            }

            if (str_starts_with($input, '-')) {
                $this->short[] = $input;
            }
        }
    }

    private function consume(): ?string
    {
        return $this->argv[$this->pos++] ?? null;
    }

    public function getScript(): string
    {
        return $this->argv[0];
    }

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        return $this->args;
    }

    /**
     * @return string[]
     */
    public function getShortOptions(): array
    {
        return $this->short;
    }

    /**
     * @return string[]
     */
    public function getLongOptions(): array
    {
        return $this->long;
    }
}
