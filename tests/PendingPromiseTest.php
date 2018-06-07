<?php

use \PHPUnit\Framework\TestCase;

class PendingPromiseTest extends TestCase
{
    public function test PendingPromise implements PromiseInterface()
    {
        $this->assertInstanceOf(
            \Workshop\Async\Definitions\PromiseInterface::class,
            new \Workshop\Async\PendingPromise()
        );
    }
}
