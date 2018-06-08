<?php

namespace Workshop\Async\Definitions;

interface EventLoopInterface
{
    /**
     * Waits *synchronously* until the promise is resolved and returns its value.
     * If promise is rejected, corresponding exception is thrown.
     *
     * @return mixed
     */
    public function wait(PromiseInterface $promise);

    /**
     * Creates a promise that will be resolved with the result of the generator.
     * If the generator throws an exception, the promise will be rejected.
     * The generator must yield PromiseInterface ONLY,
     * they will be resolved/rejected *asynchronously*.
     */
    public function async(\Generator $generator): PromiseInterface;
}