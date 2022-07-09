<?php

require_once __DIR__.'/../init.php';

$r = (new Router())
    //    route                               handler             input
    //    -----                               -------             -----
    ->add('/',                                'index')
    ->add('contacts/',                        'contacts')
    ->add('projects.html',                    'projects')
    ->add('blog/(\d+)/',                      'post_id            id=$(1)')
    ->add('([a-z0-9-]+)/',                    'auto               name=$(1)')

    ->add('feed.rss',                         'RSS')

    ->add('admin/',                           'admin/index')
    ->add('admin/{login,logout,log}/',        'admin/${1}')

    ->add('([a-z0-9-]+)/{delete,edit}/',      'admin/auto_${1}    short_name=$(1)')
    ->add('([a-z0-9-]+)/create/',             'admin/page_add     short_name=$(1)')
    ->add('write/',                           'admin/post_add')
    ->add('admin/markdown-preview.ajax',      'admin/markdown_preview')

    ->add('uploads/',                         'admin/uploads')
    ->add('uploads/{edit_note,delete}/(\d+)/','admin/upload_${1}  id=$(1)')
;

(new RequestDispatcher($r))->dispatch();