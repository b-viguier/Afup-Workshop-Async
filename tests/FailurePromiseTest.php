<?php

use \PHPUnit\Framework\TestCase;

class FailurePromiseTest extends TestCase
{
    public function test FailurePromise implements PromiseInterface()
    {
        $this->assertInstanceOf(
            \Workshop\Async\Definitions\PromiseInterface::class,
            new \Workshop\Async\FailurePromise(new \Exception())
        );
    }
}
