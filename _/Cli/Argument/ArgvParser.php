<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli\Argument;

use function array_is_list;
use function is_array;

use RuntimeException;

use function str_split;
use function str_starts_with;
use function substr;

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

    private int $argumentCounter = 0;

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
     * @var string[]
     */
    protected array $argv = [];

    /**
     * @param string[]|null $argv $_SERVER['argv']
     */
    public function __construct(array $argv = null)
    {
        $argv ??= $_SERVER['argv'] ?? [];

        if (!is_array($argv) || !array_is_list($argv) || count($argv) === 0) {
            throw new RuntimeException('argv expected to be an list with at least 1 element');
        }

        /* @phpstan-ignore-next-line */
        $this->argv = $argv;
    }

    public function addOptions(Option ...$options): self
    {
        foreach ($options as $option) {
            if (in_array($option->name, array_map(fn (Option $o): string => $o->name, $this->options), true)) {
                throw new OptionDuplicatedException("option «{$option->name}» already present");
            }

            $this->options[] = $option;
        }

        return $this;
    }

    public function addArguments(Argument ...$argument): self
    {
        foreach ($argument as $arg) {
            if (in_array($arg->name, array_map(fn (Argument $a): string => $a->name, $this->arguments), true)) {
                throw new ArgumentDuplicatedException("argument «{$arg->name}» already present");
            }

            $this->arguments[] = $arg;
        }

        return $this;
    }

    public function parse(): self
    {
        $this->argumentCounter = 0;
        $this->pos = 1;

        while ($input = $this->consume()) {
            match (true) {
                str_starts_with($input, '-') => $this->parseOption($input),
                default => $this->parseArgument($input),
            };
        }

        // check for missing args
        foreach ($this->arguments as $arg) {
            if ($arg->optional === false && $arg->isInitialized() === false) {
                throw new ArgumentMissingException("missing Argument {$arg->name}");
            }
        }

        return $this;
    }

    private function parseOption(string $input): ?Option
    {
        $longFormat = str_starts_with('--', $input);

        if ($longFormat) {
            $this->long[] = $input;
        } else {
            $combinedOpts = str_split(substr($input, 1));

            if (count($combinedOpts) > 1) {
                for ($i = 0; $i < count($combinedOpts); ++$i) {
                    $option = $this->parseOption("-{$combinedOpts[$i]}");
                    if ($option?->isValueRequired() === true && $i < count($combinedOpts) - 1) {
                        throw new \RuntimeException('only last combined option can require a value');
                    }
                }

                return $option ?? null;
            }

            $this->short[] = $input;
        }

        foreach ($this->options as $option) {
            if ($longFormat && $option->name === substr($input, 2) || !$longFormat && $option->short === substr($input, 1)) {
                $option->markPresent();

                if ($option->isValueRequired()) {
                    $option->setValue($this->consume() ?? throw new OptionMissingValueException("missing value for option {$option->name}"));
                }

                return $option;
            }
        }

        return null;
    }

    private function parseArgument(string $input): void
    {
        $argument = $this->arguments[$this->argumentCounter++] ?? null;
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

    public function getArgument(string $name): Argument
    {
        $argument = null;

        foreach ($this->arguments as $arg) {
            if ($arg->name === $name) {
                $argument = $arg;
                break;
            }
        }

        if ($argument === null) {
            throw new \RuntimeException("unknown argument {$name}");
        }

        return $argument;
    }

    public function hasArgument(string $name): bool
    {
        try {
            $this->getArgument($name);
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
