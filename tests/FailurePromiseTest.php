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

    public function test FailurePromise never returns value()
    {
        $promise = new \Workshop\Async\FailurePromise(new \Exception());
        $this->assertEquals(
            \Workshop\Async\Definitions\PromiseInterface::STATE_REJECTED,
            $promise->getState()
        );

        $this->expectException(\Exception::class);
        $promise->getValue();
    }

    public function test FailurePromise returned exception()
    {
        $exception = new \Exception();
        $promise = new \Workshop\Async\FailurePromise($exception);

        $this->assertSame($exception, $promise->getException());
    }
}
