<?php

class RedirectResponse extends Response {

    public function __construct(string $url) {
        parent::__construct(301);
        $this->addHeader('HTTP/1.1 301 Moved Permanently');
        $this->addHeader('Location: '.$url);
    }

}