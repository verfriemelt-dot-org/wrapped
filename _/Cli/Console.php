<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli;

use Closure;
use RuntimeException;
use verfriemelt\wrapped\_\ParameterBag;

class Console implements InputInterface, OutputInterface
{
    final public const STYLE_NONE = 0;
    final public const STYLE_BLACK = 30;
    final public const STYLE_RED = 31;
    final public const STYLE_GREEN = 32;
    final public const STYLE_YELLOW = 33;
    final public const STYLE_BLUE = 34;
    final public const STYLE_PURPLE = 35;
    final public const STYLE_CYAN = 36;
    final public const STYLE_WHITE = 37;

    final public const STYLE_REGULAR = 0;
    final public const STYLE_BOLD = 1;
    final public const STYLE_UNDERLINE = 4;

    protected int $currentFgColor = self::STYLE_NONE;
    protected int $currentBgColor = self::STYLE_NONE;
    protected int $currentFontStyle = self::STYLE_REGULAR;

    /** @var resource */
    protected $selectedStream;

    /** @var resource */
    protected $stdout = STDOUT;

    /** @var resource */
    protected $stderr = STDERR;
    protected Closure $linePrefixFunc;
    protected bool $hadLineOutput = false;
    protected bool $inTerminal = false;
    protected bool $forceColor = false;

    /** @var array{int, int} */
    protected array $dimensions;

    protected ParameterBag $argv;

    public static function getInstance(): self
    {
        return new self();
    }

    public function __construct()
    {
        $this->selectedStream = &$this->stdout;

        if (!is_array($_SERVER['argv'])) {
            throw new RuntimeException('cannot read argv');
        }

        $this->argv = new ParameterBag($_SERVER['argv']);

        $this->inTerminal = isset($_SERVER['TERM']);
    }

    public static function isCli(): bool
    {
        return php_sapi_name() === 'cli';
    }

    public function getArgv(): ParameterBag
    {
        return $this->argv;
    }

    public function getArgvAsString(): string
    {
        // omit first element
        return implode(' ', $this->argv->except([0]));
    }

    public function setPrefixCallback(Closure $func): static
    {
        $this->linePrefixFunc = $func;
        return $this;
    }

    public function toSTDOUT(): static
    {
        $this->selectedStream = &$this->stdout;
        return $this;
    }

    public function toSTDERR(): static
    {
        $this->selectedStream = &$this->stderr;
        return $this;
    }

    public function write(string $text, ?int $color = null): static
    {
        if ($color !== null) {
            $this->setForegroundColor($color);
        }

        if ($this->currentFontStyle !== 0 || $this->currentBgColor !== 0 || $this->currentFgColor !== 0) {
            // set current color
            fwrite($this->selectedStream, "\033[{$this->currentFgColor}m");
        }

        if (isset($this->linePrefixFunc) && $this->hadLineOutput !== true) {
            fwrite($this->selectedStream, (string) ($this->linePrefixFunc)());
            $this->hadLineOutput = true;
        }

        fwrite($this->selectedStream, $text);

        // clear color output again
        if ($this->currentFontStyle !== 0 || $this->currentBgColor !== 0 || $this->currentFgColor !== 0) {
            fwrite($this->selectedStream, "\033[0m");
        }

        if ($color !== null) {
            $this->setForegroundColor(static::STYLE_NONE);
        }

        return $this;
    }

    public function writeLn(string $text, ?int $color = null): static
    {
        return $this->write($text, $color)->eol();
    }

    public function cr(): static
    {
        fwrite($this->selectedStream, "\r");
        return $this;
    }

    public function eol(): static
    {
        $this->write(PHP_EOL);
        $this->hadLineOutput = false;
        return $this;
    }

    public function writePadded($text, $padding = 4, $paddingChar = ' ', $color = null): static
    {
        $this->write(str_repeat((string) $paddingChar, $padding));
        $this->write($text, $color);

        return $this;
    }

    // this is blocking
    public function read()
    {
        return fgets(STDIN);
    }

    public function setFontFeature(int $style): static
    {
        $this->currentFontStyle = $style;
        return $this;
    }

    public function setBackgroundColor(int $color): static
    {
        $this->currentBgColor = $color + 10;
        return $this;
    }

    public function setForegroundColor(int $color): static
    {
        $this->currentFgColor = $color;
        return $this;
    }

    public function cls(): static
    {
        fwrite($this->selectedStream, "\033[2J");
        return $this;
    }

    public function up(int $amount = 1): static
    {
        fwrite($this->selectedStream, "\033[{$amount}A");
        return $this;
    }

    public function down(int $amount = 1): static
    {
        fwrite($this->selectedStream, "\033[{$amount}B");
        return $this;
    }

    public function right(int $amount = 1): static
    {
        fwrite($this->selectedStream, "\033[{$amount}C");
        return $this;
    }

    public function left(int $amount = 1): static
    {
        fwrite($this->selectedStream, "\033[{$amount}D");
        return $this;
    }

    /**
     * Hides the cursor
     */
    public function hide(): static
    {
        fwrite($this->selectedStream, "\033[?25l");
        return $this;
    }

    /**
     * Enable/Disable Auto-Wrap
     */
    public function wrap(bool $wrap = true): static
    {
        if ($wrap) {
            fwrite($this->selectedStream, "\033[?7h");
        } else {
            fwrite($this->selectedStream, "\033[?7l");
        }

        return $this;
    }

    /**
     * Shows the cursor
     */
    public function show(): static
    {
        fwrite($this->selectedStream, "\033[?25h\033[?0c");
        return $this;
    }

    /**
     * stores cursor position
     */
    public function push(): static
    {
        fwrite($this->selectedStream, "\033[s");
        return $this;
    }

    /**
     * restores cursor position
     */
    public function pop(): static
    {
        fwrite($this->selectedStream, "\033[u");
        return $this;
    }

    public function jump(int $x = 0, int $y = 0): static
    {
        fwrite($this->selectedStream, "\033[{$y};{$x}H");
        return $this;
    }

    /**
     * reset all style features
     */
    public function __destruct()
    {
        if ($this->currentFgColor !== self::STYLE_NONE || $this->currentBgColor !== self::STYLE_NONE) {
            fwrite($this->selectedStream, "\033[0m");
        }
    }

    public function getWidth(): int
    {
        if (!isset($this->dimensions)) {
            $this->updateDimensions();
        }

        return $this->dimensions[0] ?? throw new RuntimeException('cant detect terminal width');
    }

    public function getHeight(): int
    {
        if (isset($this->dimensions)) {
            $this->updateDimensions();
        }

        return $this->dimensions[1] ?? throw new RuntimeException('cant detect terminal height');
    }

    public function updateDimensions(): static
    {
        $this->dimensions[0] = (int) shell_exec('tput cols');
        $this->dimensions[1] = (int) shell_exec('tput lines');

        return $this;
    }

    public function forceColorOutput(bool $bool = true): static
    {
        $this->forceColor = $bool;
        return $this;
    }

    public function supportsColor(): bool
    {
        return ((int) shell_exec('tput colors')) > 1;
    }
}
