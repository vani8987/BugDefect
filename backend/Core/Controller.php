<?php

namespace Core;

class Controller
{
    protected Request $request;
    protected Logger $logger;
    protected Response $response;

    public function __construct(Logger $logger, Response $response, Request $request)
    {   
        $this->request = $request;
        $this->logger = $logger;
        $this->response = $response;
    }

    protected function validate(bool $condition, string $message, int $status): bool
    {
        if ($condition) {
            return true;
        }

        $this->logger->error($message);
        $this->response->json(['message' => $message], $status);

        return false;
    }
}
