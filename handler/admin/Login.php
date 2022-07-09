<?php

namespace handler\admin;

use admin;
use csrf;
use RedirectResponse;
use Response;
use UnauthorizedException;

class Login extends AdminRequestHandler {

    public function get(): Response {
        if (admin::isAdmin())
            return new RedirectResponse('/admin/');
        return $this->skin->renderPage('admin/login');
    }

    public function post(): Response {
        csrf::check('adminlogin');
        $password = $_POST['password'] ?? '';
        $valid = admin::checkPassword($password);
        if ($valid) {
            admin::logAuth();
            admin::setCookie();
            return new RedirectResponse('/admin/');
        }
        throw new UnauthorizedException('nice try');
    }

}