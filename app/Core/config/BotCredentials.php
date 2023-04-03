<?php

namespace App\Core\config;

class BotCredentials
{
    private $token;

    //TODO: MAKE AN ENV FILE goddammit lets just start over with laravel

//    public function setToken(){
////        $this->token = $_ENV('DISCORD_TOKEN');
//        $this->token = "MTAzNjI1NjI1MTQxMzI2NjUyMg.G4InvY.SLX82VfTB_bXTj4hpAVlUjA9zfN00lIdgSiK0g";
//    }
//
//    public function getToken($auth = true){
//        if($auth) {
////            return $this->token;
//            return "MTAzNjI1NjI1MTQxMzI2NjUyMg.G4InvY.SLX82VfTB_bXTj4hpAVlUjA9zfN00lIdgSiK0g";
//        }
//        return "Unauthorized.";
//    }
    public function getToken()
    {
        $this->token=getenv('DISCORD_BOT_TOKEN');
        return $this->token;
    }


}
