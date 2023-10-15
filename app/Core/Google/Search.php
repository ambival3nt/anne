<?php

namespace App\Core\Google;

use DOMDocument;

class Search
{

    //i'm broken don't fucking use me

    function dom2array($node) {
        $res = array();
        print $node->nodeType.'<br/>';
        if($node->nodeType == XML_TEXT_NODE){
            $res = $node->nodeValue;
        }
        else{
            if($node->hasAttributes()){
                $attributes = $node->attributes;
                if(!is_null($attributes)){
                    $res['@attributes'] = array();
                    foreach ($attributes as $index=>$attr) {
                        $res['@attributes'][$attr->name] = $attr->value;
                    }
                }
            }
            if($node->hasChildNodes()){
                $children = $node->childNodes;
                for($i=0;$i<$children->length;$i++){
                    $child = $children->item($i);
                    $res[$child->nodeName] = $this->dom2array($child);
                }
            }
        }
        return $res;
    }

    public function getSearchResults($query) {
        $url = "https://www.google.com/search?q=" . urlencode($query);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($result === false) {
            return false;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Suppress errors related to invalid HTML

        $dom->loadHTML($result);


        $searchResults = [];

        // Find and loop through search result elements
        $elements = $dom->getElementsByTagName('h3');
        $linkElements = $dom->getElementsByTagName('a');


        foreach ($elements as $element) {

            dd($this->dom2array($element->parentNode->parentNode->parentNode->parentNode->parentNode->parentNode->parentNode->parentNode));
            $result = [
                'test' => $element->getAttribute('href'),
                'title' => $element->textContent,
                'link' => $element->parentNode->getAttribute('href'),
                'real_link' => $this->getRealLink($element->parentNode->getAttribute('href')),
            ];


            $searchResults[] = $result;
        }

        dd($searchResults);
        curl_close($ch);

        return $searchResults;
    }

    // Function to extract the real link from Google's redirect link
    function getRealLink($redirectLink) {
        $urlParts = parse_url($redirectLink);
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $query);
            if (isset($query['q'])) {
                return urldecode($query['q']);
            }
        }
        return $redirectLink;
    }

}
