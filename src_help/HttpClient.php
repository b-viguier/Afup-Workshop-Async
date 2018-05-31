<?php

namespace Workshop\Async;

use Workshop\Async\Definitions\EventLoopInterface;
use Workshop\Async\Definitions\HttpClientInterface;
use Workshop\Async\Definitions\PromiseInterface;

class HttpClient implements HttpClientInterface
{
    private $eventLoop;
    private $curlMultiHandle;
    private $handleToPromise = [];

    public function __construct(EventLoopInterface $eventLoop)
    {
        $this->eventLoop = $eventLoop;

        if (!$this->curlMultiHandle = curl_multi_init()) {
            throw new \Exception('Failed to create mutli handle');
        }
    }

    public function getConnectionsCount(): int
    {
        return count($this->handleToPromise);
    }

    public function get(string $url): PromiseInterface
    {
        if (!$curlHandle = curl_init($url)) {
            throw new \Exception('Failed to create simple handle');
        }
        if (!curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1)) {
            throw new \Exception('Failed to set option');
        }
        if (curl_multi_add_handle($this->curlMultiHandle, $curlHandle)) {
            throw new \Exception('Failed to add handle');
        }

        $promise = new PendingPromise();
        $this->handleToPromise[(int) $curlHandle] = $promise;

        // Is it the first request? Then start to check curl status.
        if (count($this->handleToPromise) == 1) {
            $this->eventLoop->async(
                $this->curlEventLoop()
            );
        }

        return $promise;
    }

    private function curlEventLoop(): \Generator
    {
        $active = true;
        do {
            yield $this->eventLoop->idle();
            $status = curl_multi_exec($this->curlMultiHandle, $active);

            while ($curlMultiInfos = curl_multi_info_read($this->curlMultiHandle)) {
                $curlHandle = $curlMultiInfos['handle'];
                $promise = $this->handleToPromise[(int) $curlHandle];
                unset($this->handleToPromise[(int) $curlHandle]);

                $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
                if ($httpCode != 200) {
                    $url = curl_getinfo($curlHandle)['url'];
                    $promise->reject(new \Exception("Error [$httpCode][$url] ".curl_error($curlHandle)));
                } else {
                    $promise->resolve(curl_multi_getcontent($curlHandle));
                }

                curl_multi_remove_handle($this->curlMultiHandle, $curlHandle);
            }
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);
    }
}
