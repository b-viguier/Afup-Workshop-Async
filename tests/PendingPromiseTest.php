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

    public function test PendingPromise can be resolved()
    {
        $expectedValue = 'This is the expected value';
        $promise = new \Workshop\Async\PendingPromise();

        $this->assertEquals(
            \Workshop\Async\Definitions\PromiseInterface::STATE_PENDING,
            $promise->getState()
        );
        $promise->resolve($expectedValue);

        $this->assertEquals(
            \Workshop\Async\Definitions\PromiseInterface::STATE_FULFILLED,
            $promise->getState()
        );
        $this->assertEquals($expectedValue, $promise->getValue());

        $this->expectException(\Exception::class);
        $promise->resolve($expectedValue);
    }

    public function test PendingPromise can be rejected()
    {
        $exception = new \Exception();
        $promise = new \Workshop\Async\PendingPromise();

        $this->assertEquals(
            \Workshop\Async\Definitions\PromiseInterface::STATE_PENDING,
            $promise->getState()
        );
        $promise->reject($exception);

        $this->assertEquals(
            \Workshop\Async\Definitions\PromiseInterface::STATE_REJECTED,
            $promise->getState()
        );
        $this->assertSame($exception, $promise->getException());

        //Cannot reject twice
        $this->expectException(\Exception::class);
        $promise->reject($exception);
    }

    public function test resolved PendingPromise cannot be rejected()
    {
        $promise = new \Workshop\Async\PendingPromise();
        $promise->resolve('A value');

        $this->expectException(\Exception::class);
        $promise->reject(new \Exception());
    }

    public function test rejected PendingPromise cannot be resolved()
    {
        $promise = new \Workshop\Async\PendingPromise();
        $promise->reject(new \Exception());

        $this->expectException(\Exception::class);
        $promise->resolve('A value');
    }
}
