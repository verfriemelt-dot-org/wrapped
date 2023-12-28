<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command\CommandArguments;

use RuntimeException;

class ArgvParser
{
    private int $argumentCounter;

    /** @var Argument[] */
    private array $arguments = [];

    /** @var array<string,Argument> */
    private array $argumentsNamed = [];

    /** @var array<string,Option> */
    private array $options = [];

    /** @var array<string,Option> */
    private array $shortOptions = [];

    private int $pos;

    /** @var string[] */
    protected array $argv;

    public function addOptions(Option ...$options): self
    {
        foreach ($options as $option) {
            if (\array_key_exists($option->name, $this->options)) {
                throw new OptionDuplicatedException("option «{$option->name}» already present");
            }

            $this->options[$option->name] = $option;

            if ($option->short !== null) {
                $this->shortOptions[$option->short] = $option;
            }
        }

        return $this;
    }

    public function addArguments(Argument ...$arguments): self
    {
        foreach ($arguments as $arg) {
            if (isset($this->arguments[$arg->name])) {
                throw new ArgumentDuplicatedException("argument «{$arg->name}» already present");
            }

            $optinalArguments = array_filter($this->arguments, static fn (Argument $a): bool => !$a->required());
            if (count($optinalArguments) > 0 && $arg->required()) {
                throw new ArgumentUnexpectedException('required arguments cannot follow after optional arguments');
            }

            $this->arguments[] = $arg;
            $this->argumentsNamed[$arg->name] = $arg;
        }

        return $this;
    }

    /**
     * @param string[] $argv
     */
    public function parse(array $argv): void
    {
        $this->argv = $argv;
        $this->argumentCounter = 0;
        $this->pos = 0;

        while ($input = $this->consume()) {
            match (true) {
                \str_starts_with($input, '--') => $this->parseLongOption(\substr($input, 2)),
                \str_starts_with($input, '-') => $this->parseShortOptions(\substr($input, 1)),
                default => $this->parseArgument($input),
            };
        }

        // check for missing args/opts
        foreach ($this->arguments as $arg) {
            if ($arg->required() === true && $arg->present() === false) {
                throw new ArgumentMissingException("missing Argument {$arg->name}");
            }
        }

        foreach ($this->options as $opt) {
            if ($opt->required() === true && $opt->present() === false) {
                throw new OptionMissingException("missing option {$opt->name}");
            }
        }
    }

    private function parseLongOption(string $name): void
    {
        $this->handleOption($this->options[$name] ?? throw new OptionUnexpectedException("unknown option {$name}"));
    }

    private function parseShortOptions(string $input): void
    {
        $shorts = \str_split($input);
        $i = 0;

        foreach ($shorts as $short) {
            $this->handleOption(
                $this->shortOptions[$short] ?? throw new OptionUnexpectedException("unknown option {$short}"),
                ++$i === count($shorts)
            );
        }
    }

    private function handleOption(Option $option, bool $isLastProvided = true): void
    {
        $option->markPresent();

        if (!$isLastProvided && $option->isValueRequired()) {
            throw new OptionMissingValueException("shorthand {$option->name} cannot required value while not been last");
        }

        if ($option->isValueRequired()) {
            $option->set($this->consume() ?? throw new OptionMissingValueException("missing value for {$option->name}"));
        }
    }

    private function parseArgument(string $input): void
    {
        $argument = $this->arguments[$this->argumentCounter++] ?? throw new ArgumentUnexpectedException();
        $argument->set($input);
    }

    private function consume(): ?string
    {
        return $this->argv[$this->pos++] ?? null;
    }

    public function getOption(string $name): Option
    {
        return $this->options[$name] ?? throw new RuntimeException("unknown option {$name}");
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function getArgument(string $name): Argument
    {
        return $this->argumentsNamed[$name] ?? throw new RuntimeException("unknown argument {$name}");
    }

    public function hasArgument(string $name): bool
    {
        return isset($this->argumentsNamed[$name]);
    }
}
