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
    private array $rawArguments = [];

    /**
     * @var string[]
     */
    private array $short = [];

    /**
     * @var string[]
     */
    private array $long = [];

    private int $argumentCount = 0;

    /**
     * @var Argument[]
     */
    private array $arguments = [];

    /**
     * @var Option[]
     */
    private array $options = [];

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
    }

    public function addOptions(Option ...$options): self
    {
        foreach ($options as $option) {
            $this->options[] = $option;
        }

        return $this;
    }

    public function addArguments(Argument ...$argument): self
    {
        $seenNames = [];

        foreach ($argument as $arg) {
            if (in_array($arg->name, $seenNames, true)) {
                throw new ArgumentDuplicated("argument {$arg->name} already present");
            }

            $this->arguments[] = $arg;
            $seenNames[] = $arg->name;
        }

        return $this;
    }

    public function parse(): self
    {
        $args = $this->argv;

        while ($input = $this->consume()) {
            if (!str_starts_with($input, '-')) {
                $this->parseArgument($input);
                continue;
            }

            if (str_starts_with($input, '--')) {
                $this->long[] = $input;
                continue;
            }

            if (str_starts_with($input, '-')) {
                $this->short[] = $input;
            }
        }

        // check for missing args
        foreach ($this->arguments as $arg) {
            if ($arg->optional === false && $arg->isInitialized() === false) {
                throw new ArgumentMissingException("missing Argument {$arg->name}");
            }
        }

        return $this;
    }

    private function parseArgument(string $input): void
    {
        $argument = $this->arguments[$this->argumentCount++] ?? null;
        $this->rawArguments[] = $input;

        // anonymous argument
        if ($argument === null) {
            return;
        }

        $argument->setValue($input);
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
    public function getRawArguments(): array
    {
        return $this->rawArguments;
    }

    public function getOption(string $name): Option
    {
        $option = null;

        foreach ($this->options as $opt) {
            if ($opt->name === $name) {
                $option = $opt;
                break;
            }
        }

        if ($option === null) {
            throw new \RuntimeException("unknown option {$name}");
        }

        return $option;
    }

    public function hasOption(string $name): bool
    {
        try {
            $this->getOption($name);
            return true;
        } catch (\RuntimeException $ex) {
            return false;
        }
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
