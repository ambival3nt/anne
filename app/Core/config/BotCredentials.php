<?php

namespace App\Core\config;

class BotCredentials
{
    private $token;
    public function getToken()
    {
        $this->token=getenv('DISCORD_BOT_TOKEN');
        return $this->token;
    }


}
