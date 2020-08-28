<?php

namespace App;

class WebM extends \FFMpeg\Format\Video\WebM
{
    public function getExtraParams()
    {
        return array('-f', 'webm', '-max_muxing_queue_size', '5000', '-preset', 'fast');
    }
}
