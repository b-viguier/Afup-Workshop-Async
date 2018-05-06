<?php

use \PHPUnit\Framework\TestCase;

class EventLoopTest extends TestCase
{
    public function test EventLoop implements EventLoopInterface()
    {
        $this->assertInstanceOf(
            \Workshop\Async\Definitions\EventLoopInterface::class,
            new \Workshop\Async\EventLoop()
        );
    }

    public function test EventLoop can resolve SuccessPromise()
    {
        $expectedValue = 42;
        $promise = new \Workshop\Async\SuccessPromise($expectedValue);
        $eventLoop = new \Workshop\Async\EventLoop();

        $this->assertEquals(
            $expectedValue,
            $eventLoop->wait($promise)
        );
    }

    public function test EventLoop throws in case of FailurePromise()
    {
        $exception = new class extends \Exception {};
        $promise = new \Workshop\Async\FailurePromise($exception);
        $eventLoop = new \Workshop\Async\EventLoop();

        $this->expectException(get_class($exception));
        $eventLoop->wait($promise);
    }
}
