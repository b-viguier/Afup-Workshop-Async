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
        $exception = new class extends \Exception
        {
        };
        $promise = new \Workshop\Async\FailurePromise($exception);
        $eventLoop = new \Workshop\Async\EventLoop();

        $this->expectException(get_class($exception));
        $eventLoop->wait($promise);
    }

    public function test Async returns value of dummy generator()
    {
        $expectedValue = 51;
        $eventLoop = new \Workshop\Async\EventLoop();
        $generator = function ($value): \Generator {
            return $value;
            yield; // Needed if we want to produce a generator!
        };

        $this->assertEquals(
            $expectedValue,
            $eventLoop->wait($eventLoop->async($generator($expectedValue)))
        );
    }

    public function test Async throws exception of dummy generator()
    {
        $exception = new class extends \Exception
        {
        };
        $eventLoop = new \Workshop\Async\EventLoop();
        $generator = function (\Exception $exception): \Generator {
            throw $exception;
            yield; // Needed if we want to produce a generator!
        };

        $promise = $eventLoop->async($generator($exception));
        $this->expectException(get_class($exception));
        $eventLoop->wait($promise);
    }

    public function test Generators can yield resolved promise()
    {
        $expectedValue = 1664;
        $eventLoop = new \Workshop\Async\EventLoop();
        $generator = function ($value): \Generator {
            $result = yield new \Workshop\Async\SuccessPromise($value);

            return $result;
        };

        $this->assertEquals(
            $expectedValue,
            $eventLoop->wait(
                $eventLoop->async(
                    $generator($expectedValue)
                )
            )
        );
    }

    public function test Generators can yield rejected promise()
    {
        $exception = new class extends \Exception
        {
        };
        $eventLoop = new \Workshop\Async\EventLoop();
        $generator = function (\Exception $exception): \Generator {
            try {
                yield new \Workshop\Async\FailurePromise($exception);

                return 'OK';
            } catch (\Throwable $throwable) {
                return $throwable;
            }
        };

        $this->assertSame(
            $exception,
            $eventLoop->wait(
                $eventLoop->async(
                    $generator($exception)
                )
            )
        );
    }

    public function test Generators can yield several promises()
    {
        $eventLoop = new \Workshop\Async\EventLoop();
        $generator = function (): \Generator {
            $this->assertEquals(
                123,
                yield new \Workshop\Async\SuccessPromise(123)
            );
            $this->assertEquals(
                'hello',
                yield new \Workshop\Async\SuccessPromise('hello')
            );

            try {
                yield new \Workshop\Async\FailurePromise(new \Exception('success'));

                return 'failure';
            } catch (\Throwable $throwable) {
                return $throwable->getMessage();
            }
        };

        $this->assertEquals(
            'success',
            $eventLoop->wait($eventLoop->async($generator()))
        );
    }
}
