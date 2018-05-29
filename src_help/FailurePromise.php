<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\PromiseInterface;

class FailurePromise implements PromiseInterface
{
    /**
     * @var \Throwable
     */
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function getValue()
    {
        throw new \LogicException("FailurePromise never succeeds!");
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getState(): string
    {
        return PromiseInterface::STATE_REJECTED;
    }


}
