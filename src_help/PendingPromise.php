<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\PromiseInterface;

class PendingPromise implements PromiseInterface
{
    private $value;

    private $throwable;

    private $state;

    public function __construct()
    {
        $this->state = PromiseInterface::STATE_PENDING;
    }

    public function resolve($value)
    {
        if ($this->state !== PromiseInterface::STATE_PENDING) {
            throw new \Exception('Only pending promises can be resolved.');
        }
        $this->value = $value;
        $this->state = PromiseInterface::STATE_FULFILLED;
    }

    public function getValue()
    {
        if ($this->state !== PromiseInterface::STATE_FULFILLED) {
            throw new \Exception('Only fulfilled promises have a value.');
        }

        return $this->value;
    }

    public function reject(\Throwable $throwable)
    {
        if ($this->state !== PromiseInterface::STATE_PENDING) {
            throw new \Exception('Only pending promises can be rejected.');
        }
        $this->throwable = $throwable;
        $this->state = PromiseInterface::STATE_REJECTED;
    }

    public function getException(): \Throwable
    {
        if ($this->state !== PromiseInterface::STATE_REJECTED) {
            throw new \Exception('Only rejected promises have an exception.');
        }

        return $this->throwable;
    }

    public function getState(): string
    {
        return $this->state;
    }


}
