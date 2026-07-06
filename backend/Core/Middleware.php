<?php

namespace Core;

class Middleware
{
    protected Logger $logger;
    protected Request $request;

    public function __construct()
    {
        $this->logger = new Logger('Middleware.log');
        $this->request = new Request();
    }
}
