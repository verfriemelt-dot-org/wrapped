<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Response;

class Cookie
{
    private $name;
    private $value;
    private $path;
    private $domain;
    private int|float $expiresTime;

    public function __construct($name, $value, $expires = 3600 * 24)
    {
        $this->name = $name;
        $this->value = $value;
        $this->expiresTime = time() + $expires;
    }

    public static function create($name, $value, $expires = 3600 * 24)
    {
        return new self($name, $value, $expires);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    public function getExpiresTime()
    {
        return $this->expiresTime;
    }

    public function setExpiresTime($expiresTime)
    {
        $this->expiresTime = $expiresTime;
        return $this;
    }
}
