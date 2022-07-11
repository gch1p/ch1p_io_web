<?php

namespace skin\admin;

use Stringable;

// login page
// ----------

function login($ctx) {
$html = <<<HTML
<form action="/admin/login/" method="post" class="form-layout-h">
    <input type="hidden" name="token" value="{$ctx->csrf('adminlogin')}" />
    <div class="form-field-wrap clearfix">
        <div class="form-field-label">{$ctx->lang('as_form_password')}:</div>
        <div class="form-field">
            <input id="as_password" class="form-field-input" type="password" name="password" size="50" />
        </div>
    </div>
    <div class="form-field-wrap clearfix">
        <div class="form-field-label"></div>
        <div class="form-field">
            <button type="submit">{$ctx->lang('submit')}</button>
        </div>
    </div>
</form>
HTML;

$js = <<<JS
ge('as_password').focus();
JS;

return [$html, $js];
}


// index page
// ----------

function index($ctx) {
    return <<<HTML
<div class="admin-page">
<!--    <a href="/admin/log/">Log</a><br/>-->
    <a href="/admin/logout/?token={$ctx->csrf('logout')}">Sign out</a>
</div>
HTML;
}


// uploads page
// ------------

function uploads($ctx, $uploads, $error) {
return <<<HTML
{$ctx->if_true($error, $ctx->formError, $error)}

<div class="blog-upload-form">
    <form action="/uploads/" method="post" enctype="multipart/form-data" class="form-layout-h">
        <input type="hidden" name="token" value="{$ctx->csrf('addupl')}" />
        
        <div class="form-field-wrap clearfix">
            <div class="form-field-label">{$ctx->lang('blog_upload_form_file')}:</div>
            <div class="form-field">
                <input type="file" name="files[]" multiple>
            </div>
        </div>

        <div class="form-field-wrap clearfix">
            <div class="form-field-label">{$ctx->lang('blog_upload_form_custom_name')}:</div>
            <div class="form-field">
                <input type="text" name="name">
            </div>
        </div>

        <div class="form-field-wrap clearfix">
            <div class="form-field-label">{$ctx->lang('blog_upload_form_note')}:</div>
            <div class="form-field">
                <input type="text" name="note" size="55">
            </div>
        </div>

        <div class="form-field-wrap clearfix">
            <div class="form-field-label"></div>
            <div class="form-field">
                <input type="submit" value="Upload">
            </div>
        </div>
    </form>
</div>

<div class="blog-upload-list">
    {$ctx->for_each($uploads, fn($u) => $ctx->uploadsItem(
        id: $u->id,
        name: $u->name,
        direct_url: $u->getDirectUrl(),
        note: $u->note,
        addslashes_note: $u->note,
        markdown: $u->getMarkdown(),
        size: $u->getSize(), 
    ))}
</div>
HTML;
}

function uploadsItem($ctx, $id, $direct_url, $note, $addslashes_note, $markdown, $name, $size) {
return <<<HTML
<div class="blog-upload-item">
    <div class="blog-upload-item-actions">
        <a href="javascript:void(0)" onclick="var mdel = ge('upload{$id}_md'); mdel.style.display = (mdel.style.display === 'none' ? 'block' : 'none')">{$ctx->lang('blog_upload_show_md')}</a>
        | <a href="javascript:void(0)" onclick="BlogUploadList.submitNoteEdit('/uploads/edit_note/{$id}/?token={$ctx->csrf('editupl'.$id)}', prompt('Note:', '{$addslashes_note}'))">Edit note</a>
        | <a href="/uploads/delete/{$id}/?token={$ctx->csrf('delupl'.$id)}" onclick="return confirm('{$ctx->lang('blog_upload_delete_confirmation')}')">{$ctx->lang('blog_upload_delete')}</a>
    </div>
    <div class="blog-upload-item-name"><a href="{$direct_url}">{$name}</a></div>
    {$ctx->if_true($note, '<div class="blog-upload-item-note">'.$note.'</div>')}
    <div class="blog-upload-item-info">{$size}</div>
    <div class="blog-upload-item-md" id="upload{$id}_md" style="display: none">
        <input type="text" value="{$markdown}" onclick="this.select()" readonly size="30">
    </div>
</div>
HTML;
}

function postForm($ctx,
                  string|Stringable $title,
                  string|Stringable $text,
                  string|Stringable $short_name,
                  string|Stringable $tags = '',
                  bool $is_edit = false,
                  $error_code = null,
                  ?bool $saved = null,
                  ?bool $visible = null,
                  string|Stringable|null $post_url = null,
                  ?int $post_id = null): array {
$form_url = !$is_edit ? '/write/' : $post_url.'edit/';

$html = <<<HTML
{$ctx->if_true($error_code, '<div class="form-error">'.$ctx->lang('err_blog_'.$error_code).'</div>')}
{$ctx->if_true($saved, '<div class="form-success">'.$ctx->lang('info_saved').'</div>')}
<table cellpadding="0" cellspacing="0" class="blog-write-table">
<tr>
    <td id="form_first_cell">
        <form class="blog-write-form form-layout-v" name="postForm" action="{$form_url}" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{$ctx->if_then_else($is_edit, $ctx->csrf('editpost'.$post_id), $ctx->csrf('addpost'))}" />
            
            <div class="form-field-wrap clearfix">
                <div class="form-field-label">{$ctx->lang('blog_write_form_title')}</div>
                <div class="form-field">
                    <input class="form-field-input" type="text" name="title" value="{$title}" />
                </div>
            </div>

            <div class="form-field-wrap clearfix">
                <div class="form-field-label">{$ctx->lang('blog_write_form_text')}</div>
                <div class="form-field">
                    <textarea class="form-field-input" name="text" wrap="soft">{$text}</textarea><br/>
                    <a class="blog-write-form-toggle-link" id="toggle_wrap" href="">{$ctx->lang('blog_write_form_toggle_wrap')}</a>
                </div>
            </div>

            <div class="form-field-wrap clearfix">
                <table class="blog-write-options-table">
                    <tr>
                        <td>
                            <div class="clearfix">
                                <div class="form-field-label">{$ctx->lang('blog_write_form_tags')}</div>
                                <div class="form-field">
                                    <input class="form-field-input" type="text" name="tags" value="{$tags}" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="clearfix">
                                <div class="form-field-label">{$ctx->lang('blog_write_form_options')}</div>
                                <div class="form-field">
                                    <label for="visible_cb"><input type="checkbox" id="visible_cb" name="visible"{$ctx->if_true($visible, ' checked="checked"')}> {$ctx->lang('blog_write_form_visible')}</label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="clearfix">
                                <div class="form-field-label">{$ctx->lang('blog_write_form_short_name')}</div>
                                <div class="form-field">
                                    <input class="form-field-input" type="text" name="{$ctx->if_then_else($is_edit, 'new_short_name', 'short_name')}" value="{$short_name}" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="clearfix">
                                <div class="form-field-label">&nbsp;</div>
                                <div class="form-field">
                                    <button type="submit" name="submit_btn"><b>{$ctx->lang('blog_write_form_submit_btn')}</b></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <div id="form_placeholder"></div>
    </td>
    <td>
        <div class="blog-write-form-preview post_text" id="preview_html"></div>
    </td>
</tr>
</table>
HTML;

$js_params = json_encode($is_edit
    ? ['edit' => true, 'id' => $post_id]
    : (object)[]);
$js = "AdminWriteForm.init({$js_params});";

return [$html, $js];
}


function pageForm($ctx,
                  string|Stringable $title,
                  string|Stringable $text,
                  string|Stringable $short_name,
                  bool $is_edit = false,
                  $error_code = null,
                  ?bool $saved = null,
                  bool $visible = false): array {
$form_url = '/'.$short_name.'/'.($is_edit ? 'edit' : 'create').'/';
$html = <<<HTML
{$ctx->if_true($error_code, '<div class="form-error">'.$ctx->lang('err_pages_'.$error_code).'</div>')}
{$ctx->if_true($saved, '<div class="form-success">'.$ctx->lang('info_saved').'</div>')}
<table cellpadding="0" cellspacing="0" class="blog-write-table">
<tr>
    <td id="form_first_cell">
        <form class="blog-write-form form-layout-v" name="pageForm" action="{$form_url}" method="post">
            <input type="hidden" name="token" value="{$ctx->if_then_else($is_edit, $ctx->csrf('editpage'.$short_name), $ctx->csrf('addpage'))}" />
        
            <div class="form-field-wrap clearfix">
                <div class="form-field-label">{$ctx->lang('pages_write_form_title')}</div>
                <div class="form-field">
                    <input class="form-field-input" type="text" name="title" value="{$title}" />
                </div>
            </div>

            <div class="form-field-wrap clearfix">
                <div class="form-field-label">{$ctx->lang('pages_write_form_text')}</div>
                <div class="form-field">
                    <textarea class="form-field-input" name="text" wrap="soft">{$text}</textarea><br/>
                    <a class="blog-write-form-toggle-link" id="toggle_wrap" href="">{$ctx->lang('pages_write_form_toggle_wrap')}</a>
                </div>
            </div>
            
            {$ctx->if_then_else($is_edit,
                fn() => $ctx->pageFormEditOptions($short_name, $visible),
                fn() => $ctx->pageFormAddOptions($short_name))}
            
        </form>
        <div id="form_placeholder"></div>
    </td>
    <td>
        <div class="blog-write-form-preview post_text" id="preview_html"></div>
    </td>
</tr>
</table>
HTML;

$js_params = json_encode(['pages' => true, 'edit' => $is_edit]);
$js = <<<JS
AdminWriteForm.init({$js_params});
JS;

return [$html, $js];
}

function pageFormEditOptions($ctx, $short_name, $visible) {
return <<<HTML
<div class="form-field-wrap clearfix">
    <table class="blog-write-options-table">
    <tr>
        <td>
            <div class="clearfix">
                <div class="form-field-label">{$ctx->lang('pages_write_form_short_name')}</div>
                <div class="form-field">
                    <input class="form-field-input" type="text" name="new_short_name" value="{$short_name}" />
                </div>
            </div>
        </td>
        <td>
            <div class="clearfix">
                <div class="form-field-label">{$ctx->lang('pages_write_form_options')}</div>
                <div class="form-field">
                    <label for="visible_cb"><input type="checkbox" id="visible_cb" name="visible"{$ctx->if_true($visible, ' checked="checked"')}> {$ctx->lang('pages_write_form_visible')}</label>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td rowspan="2">
            <button type="submit" name="submit_btn"><b>{$ctx->lang('pages_write_form_submit_btn')}</b></button>
        </td>
    </tr>
    </table>
</div>
HTML;
}

function pageFormAddOptions($ctx, $short_name) {
return <<<HTML
<div class="form-field-wrap clearfix">
    <div class="form-field-label"></div>
    <div class="form-field">
        <button type="submit" name="submit_btn"><b>{$ctx->lang('pages_write_form_submit_btn')}</b></button>
    </div>
</div>
<input name="short_name" value="{$short_name}" type="hidden" />
HTML;
}

function pageNew($ctx, $short_name) {
return <<<HTML
<div class="page">
    <div class="empty">
        <a href="/{$short_name}/create/">{$ctx->lang('pages_create')}</a>
    </div>
</div>
HTML;

}

// misc
function formError($ctx, $error) {
return <<<HTML
<div class="form-error">{$ctx->lang('error')}: {$error}</div>
HTML;
}

function markdownPreview($ctx, $unsafe_html, $title) {
return <<<HTML
<div class="blog-post">
    {$ctx->if_true($title, '<div class="blog-post-title"><h1>'.$title.'</h1></div>')}
    <div class="blog-post-text">{$unsafe_html}</div>
</div>
HTML;

}