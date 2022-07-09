<?php

class UnauthorizedException extends BadMethodCallException {

    public function __construct(string $message = '') {
        parent::__construct($message, 401);
    }

}