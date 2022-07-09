<?php

class AjaxOkResponse extends AjaxResponse {

    public function __construct($data) {
        parent::__construct(code: 200, body: json_encode(['response' => $data], JSON_UNESCAPED_UNICODE));
    }

}