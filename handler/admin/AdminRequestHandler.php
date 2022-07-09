<?php

namespace handler\admin;

use admin;
use Response;

class AdminRequestHandler extends \RequestHandler {

    public function beforeDispatch(): ?Response {
        $this->skin->static[] = '/css/admin.css';
        $this->skin->static[] = '/js/admin.js';

        if (!($this instanceof Login) && !admin::isAdmin())
            throw new \ForbiddenException('looks like you are not admin');

        return null;
    }

}