<?php

class RedirectResponse extends Response {

    public function __construct(string $url, int $code = 302) {
        parent::__construct($code);
        $this->addHeader('Location: '.$url);
    }

}