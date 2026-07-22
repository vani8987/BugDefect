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

    protected function positiveId(string $message, mixed $id, int $status = 422): int|null {
        if (!$this->validate(
            filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0,
            $message,
            $status
        )) {
            return null;
        }

        return (int) $id;
    }

    protected function validateStringLength(
        mixed $dataString,
        string $message,
        int $status = 422,
        int $countSymbol = 255,
        bool $required = true
    ): bool {
        if (!is_string($dataString)) {
            return $this->validate(false, $message, $status);
        }

        $dataString = trim($dataString);

        if ($required && $dataString === '') {
            return $this->validate(false, $message, $status);
        }

        return $this->validate(
            mb_strlen($dataString) <= $countSymbol,
            $message,
            $status
        );
    }
}
