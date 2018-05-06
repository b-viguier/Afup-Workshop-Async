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

    public function test SuccessPromise returned value()
    {
        $this->assertEquals(
            'A',
            (new \Workshop\Async\SuccessPromise('A'))->getValue()
        );
    }

    public function test SuccessPromise never fails()
    {
        $promise = new \Workshop\Async\SuccessPromise('A');
        $this->assertEquals(
            \Workshop\Async\Definitions\PromiseInterface::STATE_FULFILLED,
            $promise->getState()
        );

        $this->expectException(\Exception::class);
        $promise->getException();
    }
}
