<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\EventLoopInterface;
use Workshop\Async\Definitions\PromiseInterface;

class EventLoop implements EventLoopInterface
{
    public function wait(PromiseInterface $promise)
    {
        switch ($promise->getState()) {
            case PromiseInterface::STATE_FULFILLED:
                return $promise->getValue();
            case PromiseInterface::STATE_REJECTED:
                throw $promise->getException();
        }

        throw new \Exception("Unhandled Promise state [{$promise->getState()}].");
    }
}
