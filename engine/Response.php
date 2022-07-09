<?php

class Response {

    protected array $headers = [];

    public function __construct(
        public int $code = 200,
        public ?string $body = null
    ) {}

    public function send(): void {
        $this->setHeaders();
        if ($this->code == 200 || $this->code >= 400)
            echo $this->body;
    }

    public function addHeader(string $header): void {
        $this->headers[] = $header;
    }

    public function setHeaders(): void {
        http_response_code($this->code);
        foreach ($this->headers as $header)
            header($header);
    }

}