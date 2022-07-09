<?php

namespace handler\admin;

use csrf;
use Response;

class UploadEditNote extends AdminRequestHandler {

    public function post(): Response {
        list($id) = $this->input('i:id');

        $upload = \uploads::get($id);
        if (!$upload)
            return new \RedirectResponse('/uploads/?error='.urlencode('upload not found'));

        csrf::check('editupl'.$id);

        $note = $_POST['note'] ?? '';
        $upload->setNote($note);

        return new \RedirectResponse('/uploads/');
    }

}