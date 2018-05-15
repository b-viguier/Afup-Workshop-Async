<?php


namespace Workshop\Async\Definitions;


interface HttpClientInterface
{
    public function get(string $url): PromiseInterface;
}