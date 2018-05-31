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

    public function test all promises resolved()
    {
        $expectedValues = [1, 'ok', new \StdClass(), ['array']];

        $eventLoop = new \Workshop\Async\EventLoop();
        $promises = [];
        foreach ($expectedValues as $value) {
            $promises[] = new \Workshop\Async\SuccessPromise($value);
        }
        $promise = $eventLoop->all(...$promises);

        $this->assertEquals(
            $expectedValues,
            $eventLoop->wait($promise)
        );
    }

    public function test all promises rejected()
    {
        $expectedException = new class() extends \Exception
        {
        };

        $eventLoop = new \Workshop\Async\EventLoop();
        $promise = $eventLoop->all(
            new \Workshop\Async\SuccessPromise(1),
            new \Workshop\Async\FailurePromise($expectedException),
            new \Workshop\Async\SuccessPromise(2),
            new \Workshop\Async\FailurePromise(new \Exception())
        );

        $this->expectException(get_class($expectedException));
        $eventLoop->wait($promise);
    }

    public function test generators can yield pending promise that will resolve()
    {
        $eventLoop = new \Workshop\Async\EventLoop();
        $pendingPromise1 = new \Workshop\Async\PendingPromise();
        $pendingPromise2 = new \Workshop\Async\PendingPromise();

        $generator1 = function () use ($pendingPromise1, $pendingPromise2): \Generator {
            $result1 = yield $pendingPromise1;
            $pendingPromise2->resolve('generator1');

            return $result1;
        };

        $generator2 = function () use ($pendingPromise1, $pendingPromise2): \Generator {
            $pendingPromise1->resolve('generator2');
            $result2 = yield $pendingPromise2;

            return $result2;
        };

        $this->assertEquals(
            [
                'generator2',
                'generator1',
            ],
            $eventLoop->wait(
                $eventLoop->all(
                    $eventLoop->async($generator1()),
                    $eventLoop->async($generator2())
                )
            )
        );
    }

    public function test EventLoop idle()
    {
        $eventLoop = new \Workshop\Async\EventLoop();

        $idleCount = 0;
        $idleGenerator = function () use ($eventLoop, &$idleCount): \Generator {
            while (true) {
                ++$idleCount;
                yield $eventLoop->idle();
            }
        };

        $otherGenerator = function (): \Generator {
            yield new \Workshop\Async\SuccessPromise(1);
            yield new \Workshop\Async\SuccessPromise(2);
            yield new \Workshop\Async\SuccessPromise(3);
        };

        $eventLoop->async($idleGenerator());
        $eventLoop->wait($eventLoop->async($otherGenerator()));

        $this->assertGreaterThanOrEqual(3, $idleCount);
    }
}
