<?php

namespace App\Services;

use App\Core\commands\HandleCommandProcess;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Interaction;
use Google\Service\Genomics\Action;
use phpDocumentor\Reflection\Types\Compound;

class ButtonService
{

    public static function buildPaginator($pageCount, $currentPage, $discord, $playlistData)
    {

        //don't pay too much mind to these two buttons and how gnarly they look, they're just the previous and next buttons
        //setListener is the troublemaker though in all cases, it has a callback, which basically immediately fires that interaction.
        //it works, but, after you click 1 again, the paginator duplicates


        //untouched copy of playlistData
        $cleanPlaylistData = $playlistData;

        //create Previous button '<'
        $prevButton = Button::new(Button::STYLE_SUCCESS)
            ->setLabel('<')
            ->setDisabled($currentPage === 1)
            ->setListener(function (Interaction $interaction) use ($playlistData, $currentPage, $pageCount, $discord) {
                $interaction
                    ->updateMessage(self::buildPaginator($pageCount, ($currentPage - 1), $discord, $playlistData));
            }, $discord);

        //create Previous button '<'
        $firstButton = Button::new(Button::STYLE_SUCCESS)
            ->setLabel('first')
            ->setDisabled($currentPage === 1)
            ->setListener(function (Interaction $interaction) use ($playlistData, $currentPage, $pageCount, $discord) {
                $interaction
                    ->updateMessage(self::buildPaginator($pageCount, 1, $discord, $playlistData));
            }, $discord);

        $lastButton = Button::new(Button::STYLE_SUCCESS)
            ->setLabel('last')
            ->setDisabled($currentPage === $pageCount)
            ->setListener(function (Interaction $interaction) use ($playlistData, $currentPage, $pageCount, $discord) {
                $interaction
                    ->updateMessage(self::buildPaginator($pageCount, $pageCount, $discord, $playlistData));
            }, $discord);


//        $buttonRow->setListener(function (Interaction $interaction) use ($currentPage, $cleanPlaylistData, $i, $pageCount, $discord) {
//            $interaction->updateMessage(self::buildPaginator($pageCount, $i, $discord, $cleanPlaylistData));
//
//        }, $discord);

        //create Next button '>'
        $nextButton = Button::new(Button::STYLE_SUCCESS)
            ->setLabel('>')
            ->setDisabled((string)$currentPage == $pageCount)
            ->setListener(function (Interaction $interaction) use ($playlistData, $currentPage, $pageCount, $discord) {
                $interaction
                    ->updateMessage(self::buildPaginator($pageCount, ($currentPage + 1), $discord, $playlistData));
            }, $discord);

        //middle counter button that does nothing
        $countButton = Button::new(Button::STYLE_SECONDARY)
            ->setLabel($currentPage . ' / ' . $pageCount)
            ->setDisabled((true));



        //create row array
        $row = [];
        $rowNum = 1;

        //Buttons *HAVE* to be in an 'actionrow' component
        $row[1] = ActionRow::new()
            ->addComponent($firstButton)
            ->addComponent($prevButton)
            ->addComponent($countButton)
            ->addComponent($nextButton)
            ->addComponent($lastButton);

//        useless but saved to method because so much work you guys like so much work
//        $row = self::buildNumberPaginator($pageCount, $rowNum, $row, $currentPage, $cleanPlaylistData, $discord, $emptyButtons);

        //if you use the number paginator above, you have to comment out this block


        //also for the useless pile of work that was the number paginator
//        $row[1]->addComponent($nextButton);


        foreach ($row as $_row) {
            if (data_get(json_decode(json_encode($cleanPlaylistData[$currentPage])), 'components', null) == null) {
                $out = $cleanPlaylistData[$currentPage]->addComponent($_row);
            }else{
                $out = $cleanPlaylistData[$currentPage];
            }
        }

        return $out;

    }

    /**
     * @param $pageCount
     * @param int $rowNum
     * @param array $row
     * @param $currentPage
     * @param mixed $cleanPlaylistData
     * @param $discord
     * @param int $emptyButtons
     * @return array
     */
    protected static function buildNumberPaginator($pageCount, int $rowNum, array $row, $currentPage, mixed $cleanPlaylistData, $discord, int $emptyButtons): array
    {
//this loops through each page, creating a button and a listener for each number
        for ($i = 1; $i <= $pageCount; $i++) {

            //make another row of buttons if need be (top row has < and > buttons and 3 numbers, any additional rows would have five number buttons
            if ($i === 4 || $i === 9) {
                $rowNum++;
                $row[$rowNum] = ActionRow::new();
            }

            //create number button for page
            $buttonRow = Button::new(Button::STYLE_PRIMARY)
                ->setLabel($i);

            //disable current page button (as if 'pressed')
            if ($i == $currentPage) {
                $buttonRow->setDisabled(true);
            } else {

                //recursive call on itself for when a paginator button is clicked, this SHOULD set up a listener for an 'interaction', which is what happens when a button is clicked
                //in this case we want it to run this builder method again, passing in "playlistData" which is an array of already chunked song pages in message builder format
                //we also pass in the current page number, and the total number of pages, which should mean we can just render $playlistData[$currentPage] and stick a fresh paginator on it
                $buttonRow->setListener(function (Interaction $interaction) use ($currentPage, $cleanPlaylistData, $i, $pageCount, $discord) {
                    $interaction->updateMessage(self::buildPaginator($pageCount, $i, $discord, $cleanPlaylistData));

                }, $discord);
            }


            //add real buttons
            $row[$rowNum]->addComponent($buttonRow);

        }


        //filler buttons that do nothing (purely aesthetic)
        if ($emptyButtons > 0) {

            for ($i = 0; $i < $emptyButtons; $i++) {
                $emptyButton = Button::new(Button::STYLE_PRIMARY,)->setLabel('-')->setDisabled(true);
                $row[$rowNum]->addComponent($emptyButton);
            }
        }
        return $row;
    }
}
