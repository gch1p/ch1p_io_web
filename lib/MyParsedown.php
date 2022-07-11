<?php

use sixlive\ParsedownHighlight;

class MyParsedown extends ParsedownHighlight {

    public function __construct(
        protected bool $useImagePreviews = false
    ) {
        parent::__construct();
        $this->InlineTypes['{'][] = 'FileAttach';
        $this->InlineTypes['{'][] = 'Image';
        $this->InlineTypes['{'][] = 'Video';
        $this->inlineMarkerList .= '{';
    }

    protected function inlineFileAttach($excerpt) {
        if (preg_match('/^{fileAttach:([\w]{8})}{\/fileAttach}/', $excerpt['text'], $matches)) {
            $random_id = $matches[1];
            $upload = uploads::getByRandomId($random_id);
            $result = [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'span',
                    'text' => '',
                ],
                'type' => ''
            ];

            if (!$upload) {
                return $result;
            }

            unset($result['element']['text']);

            $ctx = self::getSkinContext();
            $result['element']['rawHtml'] = $ctx->fileupload($upload->name, $upload->getDirectUrl(), $upload->note, $upload->getSize());

            return $result;
        }
    }

    protected function inlineImage($excerpt) {
        global $config;

        if (preg_match('/^{image:([\w]{8}),(.*?)}{\/image}/', $excerpt['text'], $matches)) {
            $random_id = $matches[1];

            $opts = [
                'w' => 'auto',
                'h' => 'auto',
                'align' => 'left',
                'nolabel' => false,
            ];
            $inputopts = explode(',', $matches[2]);

            foreach ($inputopts as $opt) {
                if ($opt == 'nolabel')
                    $opts[$opt] = true;
                else {
                    list($k, $v) = explode('=', $opt);
                    if (!isset($opts[$k]))
                        continue;
                    $opts[$k] = $v;
                }
            }

            $image = uploads::getByRandomId($random_id);
            $result = [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'span',
                    'text' => '',
                ],
                'type' => ''
            ];

            if (!$image) {
                return $result;
            }

            list($w, $h) = $image->getImagePreviewSize(
                $opts['w'] == 'auto' ? null : $opts['w'],
                $opts['h'] == 'auto' ? null : $opts['h']
            );
            $opts['w'] = $w;
            // $opts['h'] = $h;

            if (!$this->useImagePreviews)
                $image_url = $image->getDirectUrl();
            else
                $image_url = $image->getDirectPreviewUrl($w, $h);

            unset($result['element']['text']);

            $ctx = self::getSkinContext();
            $result['element']['rawHtml'] = $ctx->image(
                w: $opts['w'],
                nolabel: $opts['nolabel'],
                align: $opts['align'],
                padding_top: round($h / $w * 100, 4),
                may_have_alpha: $image->imageMayHaveAlphaChannel(),

                url: $image_url,
                direct_url: $image->getDirectUrl(),
                note: $image->note
            );

            return $result;
        }
    }

    protected function inlineVideo($excerpt) {
        if (preg_match('/^{video:([\w]{8})(?:,(.*?))?}{\/video}/', $excerpt['text'], $matches)) {
            $random_id = $matches[1];

            $opts = [
                'w' => 'auto',
                'h' => 'auto',
                'align' => 'left',
                'nolabel' => false,
            ];
            $inputopts = !empty($matches[2]) ? explode(',', $matches[2]) : [];

            foreach ($inputopts as $opt) {
                if ($opt == 'nolabel')
                    $opts[$opt] = true;
                else {
                    list($k, $v) = explode('=', $opt);
                    if (!isset($opts[$k]))
                        continue;
                    $opts[$k] = $v;
                }
            }

            $video = uploads::getByRandomId($random_id);
            $result = [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'span',
                    'text' => '',
                ],
                'type' => ''
            ];

            if (!$video) {
                return $result;
            }

            $video_url = $video->getDirectUrl();

            unset($result['element']['text']);

            $ctx = self::getSkinContext();
            $result['element']['rawHtml'] = $ctx->video(
                url: $video_url,
                w: $opts['w'],
                h: $opts['h']
            );

            return $result;
        }
    }

    protected function paragraph($line) {
        if (preg_match('/^{fileAttach:([\w]{8})}{\/fileAttach}$/', $line['text'])) {
            return $this->inlineFileAttach($line);
        }
        if (preg_match('/^{image:([\w]{8}),(?:.*?)}{\/image}/', $line['text'])) {
            return $this->inlineImage($line);
        }
        if (preg_match('/^{video:([\w]{8})(?:,(?:.*?))?}{\/video}/', $line['text'])) {
            return $this->inlineVideo($line);
        }
        return parent::paragraph($line);
    }

    protected function blockFencedCodeComplete($block) {
        if (!isset($block['element']['element']['attributes'])) {
            return $block;
        }

        $code = $block['element']['element']['text'];
        $languageClass = $block['element']['element']['attributes']['class'];
        $language = explode('-', $languageClass);

        if ($language[1] == 'term') {
            $lines = explode("\n", $code);
            for ($i = 0; $i < count($lines); $i++) {
                $line = $lines[$i];
                if (str_starts_with($line, '$ ') || str_starts_with($line, '# ')) {
                    $lines[$i] = '<span class="term-prompt">'.substr($line, 0, 2).'</span>'.htmlspecialchars(substr($line, 2), ENT_NOQUOTES, 'UTF-8');
                } else {
                    $lines[$i] = htmlspecialchars($line, ENT_NOQUOTES, 'UTF-8');
                }
            }
            $block['element']['element']['rawHtml'] = implode("\n", $lines);
            unset($block['element']['element']['text']);

            return $block;
        }

        return parent::blockFencedCodeComplete($block);
    }

    protected static function getSkinContext(): SkinContext {
        return new SkinContext('\\skin\\markdown');
    }

}
