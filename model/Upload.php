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

        return 'https://'.$config['uploads_host'].'/'.$this->randomId.'/p'.$w.'x'.$h.'.jpg';
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

    /**
     * @param ?int $w
     * @param ?int $h
     * @param bool $update Whether to proceed if preview already exists
     * @return bool
     */
    public function createImagePreview(?int $w = null, ?int $h = null, bool $update = false): bool {
        global $config;

        $orig = $config['uploads_dir'].'/'.$this->randomId.'/'.$this->name;
        $updated = false;

        for ($mult = 1; $mult <= 2; $mult++) {
            $dw = $w * $mult;
            $dh = $h * $mult;
            $dst = $config['uploads_dir'].'/'.$this->randomId.'/p'.$dw.'x'.$dh.'.jpg';

            if (file_exists($dst)) {
                if (!$update)
                    continue;
                unlink($dst);
            }

            $img = imageopen($orig);
            imageresize($img, $dw, $dh, [255, 255, 255]);
            imagejpeg($img, $dst, $mult == 1 ? 93 : 67);
            imagedestroy($img);

            setperm($dst);
            $updated = true;
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
            if (preg_match('/^p(\d+)x(\d+)\.jpg$/', $f)) {
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
