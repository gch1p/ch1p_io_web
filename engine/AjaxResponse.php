<?php

class AjaxResponse extends Response {

    public function __construct(...$args) {
        parent::__construct(...$args);
        $this->addHeader('Content-Type: application/json; charset=utf-8');
        $this->addHeader('Cache-Control: no-cache, must-revalidate');
        $this->addHeader('Pragma: no-cache');
        $this->addHeader('Content-Type: application/json; charset=utf-8');
    }

}