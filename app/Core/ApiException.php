<?php
// Throw this anywhere to stop and send a JSON error with the right HTTP status.
class ApiException extends Exception
{
    public function __construct(string $message, public int $status = 400, public array $errors = [])
    {
        parent::__construct($message);
    }
}