<?php

use \PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    public function test HttpClient succeeds to retrieve Example page()
    {
        $eventLoop = new \Workshop\Async\EventLoop();
        $client = new \Workshop\Async\HttpClient($eventLoop);

        $content = $eventLoop->wait($client->get('http://www.example.com/'));

        $this->assertContains('Example Domain', $content);;
    }

    public function test HttpClient throws exceptions in case of error()
    {
        $eventLoop = new \Workshop\Async\EventLoop();
        $client = new \Workshop\Async\HttpClient($eventLoop);
        $promise = $client->get('http://www.example.com/404');

        $this->expectException(\Exception::class);
        $eventLoop->wait($promise);
    }
}
