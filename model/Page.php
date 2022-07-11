<?php

class Page extends Model {

    const DB_TABLE = 'pages';
    const DB_KEY = 'short_name';

    public string $title;
    public string $md;
    public string $html;
    public int $ts;
    public int $updateTs;
    public bool $visible;
    public string $shortName;

    public function edit(array $data) {
        $data['update_ts'] = time();
        if ($data['md'] != $this->md)
            $data['html'] = markup::markdownToHtml($data['md']);
        parent::edit($data);
    }

    public function isUpdated(): bool {
        return $this->updateTs && $this->updateTs != $this->ts;
    }

    public function getHtml(bool $is_retina, string $user_theme): string {
        $html = $this->html;
        $html = markup::htmlImagesFix($html, $is_retina, $user_theme);
        return $html;
    }

    public function getUrl(): string {
        return "/{$this->shortName}/";
    }

    public function updateHtml() {
        $html = markup::markdownToHtml($this->md);
        $this->html = $html;
        getDb()->query("UPDATE pages SET html=? WHERE short_name=?", $html, $this->shortName);
    }

}
