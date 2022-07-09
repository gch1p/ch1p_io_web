<?php

namespace handler\main;

class ProjectsHtml extends \RequestHandler {

    public function get(): \Response {
        return new \RedirectResponse('/projects/', 301);
    }

}