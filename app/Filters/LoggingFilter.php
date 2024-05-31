<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class LoggingFilter implements FilterInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct()
    {
        $this->logger = service('logger');
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // Log the incoming request
        $this->logger->info('Incoming request', [
            'startTime' => microtime(true),
            'method' => $request->getMethod(),
            'uri' => (string) $request->getURI(),
            'headers' => $request->getHeaderLine(true),
            'endTime' => microtime(true)
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Log the outgoing response
        $this->logger->info('Outgoing response', [
            'status_code' => $response->getStatusCode(),
            'headers' => $request->getHeaderLine(true),
            'body' => $response->getBody()
        ]);
    }
}
