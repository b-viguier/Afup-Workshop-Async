<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\PromiseInterface;

class SuccessPromise implements PromiseInterface
{
    private $ampPromise;

    public function __construct($value)
    {
        $this->ampPromise = new \Amp\Success($value);
    }

    public function getAmpPromise(): \Amp\Promise
    {
        return $this->ampPromise;
    }
}
