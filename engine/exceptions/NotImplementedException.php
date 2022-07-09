<?php

class NotImplementedException extends BadMethodCallException {

    public function __construct(string $message = '') {
        parent::__construct($message, 501);
    }

}