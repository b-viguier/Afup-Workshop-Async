<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\HttpClientInterface;
use Workshop\Async\Definitions\PromiseInterface;

class HttpClient implements HttpClientInterface
{
    public function get(string $url): PromiseInterface
    {
        $content = @file_get_contents($url);

        return $content === false ?
            new FailurePromise(new \Exception("Failed to retrieve Url content '$url'"))
            : new SuccessPromise($content);
    }
}
