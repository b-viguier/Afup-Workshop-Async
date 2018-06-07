<?php


namespace Workshop\Async\Definitions;

interface PromiseInterface
{
    public function getAmpPromise(): \Amp\Promise;
}
