<?php

namespace handler\admin;

use csrf;
use RedirectResponse;
use Response;

class UploadDelete extends AdminRequestHandler {

    public function get(): Response {
        list($id) = $this->input('i:id');

        $upload = \uploads::get($id);
        if (!$upload)
            return new RedirectResponse('/uploads/?error='.urlencode('upload not found'));

        csrf::check('delupl'.$id);

        \uploads::delete($id);

        return new RedirectResponse('/uploads/');
    }

}