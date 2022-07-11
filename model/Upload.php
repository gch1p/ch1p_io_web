<?php

class Upload extends Model {

    const DB_TABLE = 'uploads';

    public static array $ImageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    public static array $VideoExtensions = ['mp4', 'ogg'];

    public int $id;
    public string $randomId;
    public int $ts;
    public string $name;
    public int $size;
    public int $downloads;
    public int $image; // TODO: remove
    public int $imageW;
    public int $imageH;
    public string $note;

    public function getDirectory(): string {
        global $config;
        return $config['uploads_dir'].'/'.$this->randomId;
    }

    public function getDirectUrl(): string {
        global $config;
        return 'https://'.$config['uploads_host'].'/'.$this->randomId.'/'.$this->name;
    }

    public function getDirectPreviewUrl(int $w, int $h, bool $retina = false): string {
        global $config;
        if ($w == $this->imageW && $this->imageH == $h)
            return $this->getDirectUrl();

        if ($retina) {
            $w *= 2;
            $h *= 2;
        }

        $prefix = $this->imageMayHaveAlphaChannel() ? 'a' : 'p';
        return 'https://'.$config['uploads_host'].'/'.$this->randomId.'/'.$prefix.$w.'x'.$h.'.jpg';
    }

    // TODO remove?
    public function incrementDownloads() {
        $db = getDb();
        $db->query("UPDATE uploads SET downloads=downloads+1 WHERE id=?", $this->id);
        $this->downloads++;
    }

    public function getSize(): string {
        return sizeString($this->size);
    }

    public function getMarkdown(): string {
        if ($this->isImage()) {
            $md = '{image:'.$this->randomId.',w='.$this->imageW.',h='.$this->imageH.'}{/image}';
        } else if ($this->isVideo()) {
            $md = '{video:'.$this->randomId.'}{/video}';
        } else {
            $md = '{fileAttach:'.$this->randomId.'}{/fileAttach}';
        }
        $md .= ' <!-- '.$this->name.' -->';
        return $md;
    }

    public function setNote(string $note) {
        $db = getDb();
        $db->query("UPDATE uploads SET note=? WHERE id=?", $note, $this->id);
    }

    public function isImage(): bool {
        return in_array(extension($this->name), self::$ImageExtensions);
    }

    // assume all png images have alpha channel
    // i know this is wrong, but anyway
    public function imageMayHaveAlphaChannel(): bool {
        return strtolower(extension($this->name)) == 'png';
    }

    public function isVideo(): bool {
        return in_array(extension($this->name), self::$VideoExtensions);
    }

    public function getImageRatio(): float {
        return $this->imageW / $this->imageH;
    }

    public function getImagePreviewSize(?int $w = null, ?int $h = null): array {
        if (is_null($w) && is_null($h))
            throw new Exception(__METHOD__.': both width and height can\'t be null');

        if (is_null($h))
            $h = round($w / $this->getImageRatio());

        if (is_null($w))
            $w = round($h * $this->getImageRatio());

        return [$w, $h];
    }

    public function createImagePreview(?int $w = null,
                                       ?int $h = null,
                                       bool $force_update = false,
                                       bool $may_have_alpha = false): bool {
        global $config;

        $orig = $config['uploads_dir'].'/'.$this->randomId.'/'.$this->name;
        $updated = false;

        foreach (themes::getThemes() as $theme) {
            if (!$may_have_alpha && $theme == 'dark')
                continue;

            for ($mult = 1; $mult <= 2; $mult++) {
                $dw = $w * $mult;
                $dh = $h * $mult;

                $prefix = $may_have_alpha ? 'a' : 'p';
                $dst = $config['uploads_dir'].'/'.$this->randomId.'/'.$prefix.$dw.'x'.$dh.($theme == 'dark' ? '_dark' : '').'.jpg';

                if (file_exists($dst)) {
                    if (!$force_update)
                        continue;
                    unlink($dst);
                }

                $img = imageopen($orig);
                imageresize($img, $dw, $dh, themes::getThemeAlphaColorAsRGB($theme));
                imagejpeg($img, $dst, $mult == 1 ? 93 : 67);
                imagedestroy($img);

                setperm($dst);
                $updated = true;
            }
        }

        return $updated;
    }

    /**
     * @return int  Number of deleted files
     */
    public function deleteAllImagePreviews(): int {
        global $config;
        $dir = $config['uploads_dir'].'/'.$this->randomId;
        $files = scandir($dir);
        $deleted = 0;
        foreach ($files as $f) {
            if (preg_match('/^[ap](\d+)x(\d+)(?:_dark)?\.jpg$/', $f)) {
                if (is_file($dir.'/'.$f))
                    unlink($dir.'/'.$f);
                else
                    logError(__METHOD__.': '.$dir.'/'.$f.' is not a file!');
                $deleted++;
            }
        }
        return $deleted;
    }

}
