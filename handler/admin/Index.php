<?php

namespace handler\admin;

use Response;

class Index extends AdminRequestHandler {

    public function get(): Response {
        return $this->skin->renderPage('admin/index');
    }

}