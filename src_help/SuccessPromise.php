<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\PromiseInterface;

class SuccessPromise implements PromiseInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getException(): \Throwable
    {
        throw new \LogicException("SuccessPromise never fails!");
    }

    public function getState(): string
    {
        return PromiseInterface::STATE_FULFILLED;
    }


}
