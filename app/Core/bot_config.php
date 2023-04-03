<?php

namespace App\Core;

use Discord\Discord;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;



include __DIR__.'/vendor/autoload.php';
class configData
{
    public static function config() {
        return
            [
            'token' => 'OTkxNDQ1NzY4MjgyMDU0NzA2.Gu9rcH.kZydr8XGyispPVF5iBqh4WTrBxR5cQ6JK1k41A',
            'intents' => Intents::getDefaultIntents(), // default intents as well as guild members
            'loadAllMembers' => false,
            'storeMessages' => false,
            'retrieveBans' => false,
            'pmChannels' => false,
//            'disabledEvents' => [Event::MESSAGE_CREATE, Event::MESSAGE_DELETE,  ...],
            'loop' => \React\EventLoop\Factory::create(),
            'logger' => new \Monolog\Logger('New logger'),
            'dnsConfig' => '1.1.1.1',
            'shardId' => 0,
            'shardCount' => 5,
                ];
        }
}
