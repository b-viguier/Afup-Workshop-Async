<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\EventLoopInterface;
use Workshop\Async\Definitions\PromiseInterface;

class EventLoop implements EventLoopInterface
{
    /**
     * @var EventLoopTask[]
     */
    private $tasks = [];

    public function wait(PromiseInterface $promise)
    {
        return \Amp\Promise\wait($promise->getAmpPromise());
    }

    public function async(\Generator $generator): PromiseInterface
    {
        $wrapper = function (\Generator $generator): \Generator {
            while ($generator->valid()) {
                $blockingPromise = $generator->current()->getAmpPromise();

                // Forwards promise value/exception to underlying generator
                $blockingPromiseValue = null;
                $blockingPromiseException = null;
                try {
                    $blockingPromiseValue = yield $blockingPromise;
                } catch (\Throwable $throwable) {
                    $blockingPromiseException = $throwable;
                }
                if ($blockingPromiseException) {
                    $generator->throw($blockingPromiseException);
                } else {
                    $generator->send($blockingPromiseValue);
                }
            }

            return $generator->getReturn();
        };

        $coroutinePromise = new class implements PromiseInterface
        {
            public $ampPromise;

            public function getAmpPromise(): \Amp\Promise
            {
                return $this->ampPromise;
            }

        };
        $coroutinePromise->ampPromise = new \Amp\Coroutine($wrapper($generator));

        return $coroutinePromise;
    }

    public function all(PromiseInterface ...$promises): PromiseInterface
    {
        $ampPromises = [];
        foreach ($promises as $promise) {
            $ampPromises[] = $promise->getAmpPromise();
        }

        $allPromise = new class implements PromiseInterface
        {
            public $ampPromise;

            public function getAmpPromise(): \Amp\Promise
            {
                return $this->ampPromise;
            }

        };
        $allPromise->ampPromise = \Amp\Promise\all($ampPromises);

        return $allPromise;
    }

    public function idle(): PromiseInterface
    {
        $promise = new PendingPromise();

        \Amp\Loop::defer(function () use ($promise) {
            $promise->resolve(null);
        });


        return $promise;
    }
}
