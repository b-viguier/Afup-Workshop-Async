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

    public function async(\Generator $generator): PromiseInterface
    {
        try {
            while ($generator->valid()) {
                $blockingPromise = $generator->current();

                // Resolves blocking promise and forwards result to the generator
                $blockingPromiseValue = null;
                $blockingPromiseException = null;
                try {
                    $blockingPromiseValue = $this->wait($blockingPromise);
                } catch (\Throwable $exception) {
                    $blockingPromiseException = $exception;
                }
                if ($blockingPromiseException) {
                    $generator->throw($blockingPromiseException);
                } else {
                    $generator->send($blockingPromiseValue);
                }
            }

            return new SuccessPromise($generator->getReturn());
        } catch (\Throwable $exception) {
            return new FailurePromise($exception);
        }
    }

    public function all(PromiseInterface ...$promises): PromiseInterface
    {
        try {
            return new SuccessPromise(array_map([$this, 'wait'], $promises));
        } catch (\Throwable $exception) {
            return new FailurePromise($exception);
        }
    }

}
