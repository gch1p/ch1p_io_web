<?php

class AjaxErrorResponse extends AjaxResponse {

    public function __construct(string $error, int $code = 200) {
        parent::__construct(code: $code, body: json_encode(['error' => $error], JSON_UNESCAPED_UNICODE));
    }

}