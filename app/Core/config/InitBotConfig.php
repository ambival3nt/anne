<?php

namespace App\Core\config;

class InitBotConfig
{

    //This is where you set the bot's command tag
    public function commandTag()
    {
        $tag = '-=';
        $length = strlen($tag);

        return [
            'tag' => $tag,
            'tagLength' => $length,
        ];

    }
}
