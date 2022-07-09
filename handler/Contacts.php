<?php

namespace handler;

use Response;

class Contacts extends \RequestHandler {

    public function get(): Response {
        global $config;
        $this->skin->title = $this->lang['contacts'];
        return $this->skin->renderPage('main/contacts',
            email: $config['admin_email']);
    }

}