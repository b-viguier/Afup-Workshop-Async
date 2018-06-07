<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\PromiseInterface;

class PendingPromise implements PromiseInterface
{
    private $deferred;


    public function __construct()
    {
        $this->deferred = new \Amp\Deferred();
    }

    public function getAmpPromise(): \Amp\Promise
    {
        return $this->deferred->promise();
    }

    public function resolve($value)
    {
        $this->deferred->resolve($value);
    }

    public function reject(\Throwable $throwable)
    {
        $this->deferred->fail($throwable);
    }
}
