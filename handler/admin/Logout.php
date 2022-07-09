<?php

namespace handler\admin;

use admin;
use csrf;
use Response;

class Logout extends AdminRequestHandler {

    public function get(): Response {
        csrf::check('logout');
        admin::unsetCookie();
        return new \RedirectResponse('/admin/login/');
    }

}