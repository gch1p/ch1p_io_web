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

    public function getHtml(bool $retina): string {
        $html = $this->html;
        if ($retina)
            $html = markup::htmlRetinaFix($html);
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
