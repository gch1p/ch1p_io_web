<?php

namespace handler\admin;

use csrf;
use RedirectResponse;
use Response;

// So it's 2022 outside, and it's PHP 8.1 already, which is actually so cool comparing to 5.x and even 7.4, but...
// ...class names are still case-insensitive?!! And I can't import \uploads because it's the same as Uploads?!!
//
// PHP, what the fuck is wrong with you?!

class Uploads extends AdminRequestHandler {

    public function get(): Response {
        list($error) = $this->input('error');
        $uploads = \uploads::getAll();

        $this->skin->title = ($this->lang)('blog_upload');
        return $this->skin->renderPage('admin/uploads',
            error: $error,
            uploads: $uploads);
    }

    public function post(): Response {
        csrf::check('addupl');

        list($custom_name, $note) = $this->input('name, note');

        if (!isset($_FILES['files']))
            return new RedirectResponse('/uploads/?error='.urlencode('no file'));

        $files = [];
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            $files[] = [
                'name' => $_FILES['files']['name'][$i],
                'type' => $_FILES['files']['type'][$i],
                'tmp_name' => $_FILES['files']['tmp_name'][$i],
                'error' => $_FILES['files']['error'][$i],
                'size' => $_FILES['files']['size'][$i],
            ];
        }

        if (count($files) > 1) {
            $note = '';
            $custom_name = '';
        }

        foreach ($files as $f) {
            if ($f['error'])
                return new RedirectResponse('/uploads/?error='.urlencode('error code '.$f['error']));

            if (!$f['size'])
                return new RedirectResponse('/uploads/?error='.urlencode('received empty file'));

            $ext = extension($f['name']);
            if (!\uploads::isExtensionAllowed($ext))
                return new RedirectResponse('/uploads/?error='.urlencode('extension not allowed'));

            $upload_id = \uploads::add(
                $f['tmp_name'],
                $custom_name ?: $f['name'],
                $note);

            if (!$upload_id)
                return new RedirectResponse('/uploads/?error='.urlencode('failed to create upload'));
        }

        return new RedirectResponse('/uploads/');
    }

}