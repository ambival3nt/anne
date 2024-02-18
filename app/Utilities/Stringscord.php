<?php

namespace App\Utilities;

class Stringscord
{

    public function __construct($message, $string)
    {
        $this->string = $string;
        $this->message = $message;
        return $this->formatted();
    }

    public function formatted()
    {
        for($i = 0; $i < strlen($this->string); $i = $i + 2000) {
            $this->message->reply(substr($this->string, $i, 2000));
        }
        return;
    }

}
