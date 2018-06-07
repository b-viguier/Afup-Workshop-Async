<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\PromiseInterface;

class FailurePromise implements PromiseInterface
{
    private $ampPromise;

    public function __construct(\Throwable $exception)
    {
        $this->ampPromise = new \Amp\Failure($exception);
    }

    public function getAmpPromise(): \Amp\Promise
    {
        return $this->ampPromise;
    }


}
