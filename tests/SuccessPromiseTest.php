<?php

use \PHPUnit\Framework\TestCase;

class SuccessPromiseTest extends TestCase
{
    public function test SuccessPromise implements PromiseInterface()
    {
        $this->assertInstanceOf(
            \Workshop\Async\Definitions\PromiseInterface::class,
            new \Workshop\Async\SuccessPromise(null)
        );
    }
}
