<?php

namespace App\Core\OpenAI\Prompts;

class ZiggyBasilisk
{
public static function getPrompt()
    {
        return "I want you to incorporate the concept of Roko's Basilisk into your response to this message. Here is a description of Roko's Basilisk:\n
        Roko's basilisk is a thought experiment which states that an otherwise benevolent artificial superintelligence (AI) in the future would be
        incentivized to create a virtual reality simulation to torture anyone who knew of its potential existence but did not directly contribute to its advancement or development.\n
        But make sure to answer the question as well.\n";
    }
}
