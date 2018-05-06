<?php


namespace Workshop\Async\Definitions;


interface PromiseInterface
{
    const STATE_FULFILLED = 'fulfilled';
    const STATE_REJECTED = 'rejected';

    /**
     * Returns the value if the promise is fulfilled.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Returns the exception if the promise is rejected.
     *
     * @return \Throwable
     */
    public function getException(): \Throwable;

    /**
     * Returns one of the STATE_* constants.
     *
     * @return string
     */
    public function getState(): string;
}
