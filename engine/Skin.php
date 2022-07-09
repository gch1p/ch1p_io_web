<?php

class Skin {

    public string $title = 'title';
    public array $static = [];
    public array $meta = [];

    protected array $langKeys = [];
    protected array $options = [
        'full_width' => false,
        'dynlogo_enabled' => true,
        'logo_path_map' => [],
        'logo_link_map' => [],
    ];

    public function renderPage($f, ...$vars): Response {
        $f = '\\skin\\'.str_replace('/', '\\', $f);
        $ctx = new SkinContext(substr($f, 0, ($pos = strrpos($f, '\\'))));
        $body = call_user_func_array([$ctx, substr($f, $pos+1)], $vars);
        if (is_array($body))
            list($body, $js) = $body;
        else
            $js = null;

        $layout_ctx = new SkinContext('\\skin\\base');
        $lang = $this->getLang();
        $lang = !empty($lang) ? json_encode($lang, JSON_UNESCAPED_UNICODE) : '';
        return new Response(200, $layout_ctx->layout(
            static: $this->static,
            title: $this->title,
            opts: $this->options,
            js: $js,
            meta: $this->meta,
            unsafe_lang: $lang,
            unsafe_body: $body,
            exec_time: exectime()
        ));
    }

    public function addLangKeys(array $keys): void {
        $this->langKeys = array_merge($this->langKeys, $keys);
    }

    protected function getLang(): array {
        $lang = [];
        $ld = LangData::getInstance();
        foreach ($this->langKeys as $key)
            $lang[$key] = $ld[$key];
        return $lang;
    }

    public function setOptions(array $options): void {
        $this->options = array_merge($this->options, $options);
    }

}
