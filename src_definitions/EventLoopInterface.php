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
}
