<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Cli;

class ConsoleFrame
{
    private readonly Console $cli;

    /** @var array{x:int,y:int} */
    private array $pos;
    private int $height;
    private int $width;

    /** @var array<array{string, int}> */
    private array $buffer = [];

    private int $scrollPos = 0;

    public function __construct(Console $cli)
    {
        $this->cli = $cli;

        $this->height = $cli->getHeight();
        $this->width = $cli->getWidth();
    }

    public function setPosition(int $x, int $y): static
    {
        $this->pos = [
            'x' => $x,
            'y' => $y,
        ];

        return $this;
    }

    public function setScrollPos(int $pos): static
    {
        if ($pos < 0) {
            $this->scrollPos = count($this->buffer) - $pos;
        }

        $this->scrollPos = $pos;
        return $this;
    }

    public function getScrollPos(): int
    {
        return $this->scrollPos;
    }

    public function setDimension(int $width, int $height): static
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    // if window overflows the window with limit blanking width
    // to stay within borders
    private function getRenderWidth(): int
    {
        if ($this->cli->getWidth() - $this->pos['x'] <= $this->width) {
            return $this->cli->getWidth() - $this->pos['x'];
        }

        return $this->width;
    }

    private function getRenderHeight(): int
    {
        if ($this->cli->getHeight() < $this->pos['y'] + $this->height) {
            return $this->cli->getHeight() - $this->pos['y'];
        }

        return $this->height;
    }

    // wipes rectangle with spaces
    private function blank(): void
    {
        $width = $this->getRenderWidth();
        $height = $this->getRenderHeight();

        for ($h = 0; $h <= $this->height && $h < $height; ++$h) {
            $this->cli->jump($this->pos['x'], $this->pos['y'] + $h);
            $this->cli->write(str_repeat(' ', $width));
        }
    }

    public function addToBuffer(string $line, int $style = 0): static
    {
        $this->buffer[] = [$line, $style];
        return $this;
    }

    public function clearBuffer(): static
    {
        $this->buffer = [];
        return $this;
    }

    /**
     * @return array<array{string,int}>
     */
    public function getBuffer(): array
    {
        return $this->buffer;
    }

    /**
     * @param array<array{string,int}> $buffer
     */
    public function setBuffer(array $buffer): static
    {
        $this->buffer = $buffer;
        return $this;
    }

    public function render(): void
    {
        $this->blank();

        $offset = 0;
        $width = $this->getRenderWidth();
        $height = $this->getRenderHeight();

        foreach (array_slice($this->buffer, $this->scrollPos, $height) as [ $line, $style ]) {
            $this->cli->jump($this->pos['x'], $this->pos['y'] + $offset);
            //                $this->cli->write( mb_substr( $line, 0,  $this->getRenderWidth() ), $style );

            $line = str_replace(["\n", "\r", "\t", "\0"], ['', '', '', ''], $line);

            $this->cli->write(
                //                    str_pad( $this->getRenderHeight() . ":". $offset  ,6 ).
                //                    str_pad(
                substr($line, 0, $width)
//                        $this->getRenderWidth(),
//                        $this->instance
//                    ),
                ,
                $style
            );

            ++$offset;
        }
    }
}
