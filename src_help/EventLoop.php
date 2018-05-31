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
        while ($promise->getState() === PromiseInterface::STATE_PENDING && $this->tasks) {
            $allTasks = $this->tasks;
            $this->tasks = [];
            foreach ($allTasks as $task) {
                try {
                    if (!$task->generator->valid()) {
                        $task->promise->resolve($task->generator->getReturn());
                        continue;
                    }

                    $blockingPromise = $task->generator->current();
                    switch ($blockingPromise->getState()) {
                        case PromiseInterface::STATE_FULFILLED:
                            $task->generator->send($blockingPromise->getValue());
                            break;
                        case PromiseInterface::STATE_REJECTED:
                            $task->generator->throw($blockingPromise->getException());
                            break;
                        case PromiseInterface::STATE_PENDING:
                            break;
                        default:
                            throw new \Exception("Unhandled Promise state [{$blockingPromise->getState()}].");
                    }
                    $this->tasks[] = $task;

                } catch (\Throwable $exception) {
                    $task->promise->reject($exception);
                }
            }
        }

        switch ($promise->getState()) {
            case PromiseInterface::STATE_FULFILLED:
                return $promise->getValue();
            case PromiseInterface::STATE_REJECTED:
                throw $promise->getException();
            case PromiseInterface::STATE_PENDING:
                throw new \Exception('No more async tasks available to resolve promise.');
        }

        throw new \Exception("Unhandled Promise state [{$promise->getState()}].");
    }

    public function async(\Generator $generator): PromiseInterface
    {
        $task = new EventLoopTask($generator);
        $this->tasks[] = $task;

        return $task->promise;
    }

    public function all(PromiseInterface ...$promises): PromiseInterface
    {
        $groupPromise = new PendingPromise();
        $unresolvedPromiseCount = count($promises);
        $allResults = array_fill(0, count($promises), null);

        $waitOnePromise = function (int $index, PromiseInterface $promise) use ($groupPromise, &$unresolvedPromiseCount, &$allResults): \Generator {
            try {
                $allResults[$index] = yield $promise;
            } catch (\Throwable $throwable) {
                if ($unresolvedPromiseCount > 0) {
                    $groupPromise->reject($throwable);
                    $unresolvedPromiseCount = -1;   // Prevent to resolve/reject the groupPromise twice
                }
            }
            if (0 == --$unresolvedPromiseCount) {
                $groupPromise->resolve($allResults);
            }
        };

        foreach ($promises as $index => $promise) {
            $this->async($waitOnePromise($index, $promise));
        }

        return $groupPromise;
    }

    public function idle(): PromiseInterface
    {
        return $this->async((function (): \Generator {
            return;
            yield;
        })());
    }
}

class EventLoopTask
{
    public $generator;
    public $promise;

    public function __construct(\Generator $generator)
    {
        $this->generator = $generator;
        $this->promise = new PendingPromise();
    }
}
