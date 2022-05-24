<?php

namespace App\CrawlerExtracts;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class GaricExtract
{
    public static function getExtract()
    {
        try {
            $url = 'https://www.litmir.me/br/?b=11236&p='.rand(1,20);
            $client = New Client();
            $response = $client->request('GET',$url)->getBody()->getContents();
            $crawler = new Crawler();
            $crawler->addHtmlContent($response);
            $count = $crawler->filter('div.fb2-stanza')->count();
            $childNodes = $crawler->filter('div.fb2-stanza')->getNode(rand(0,$count-1))->childNodes;
            $text = '';
            foreach ($childNodes as $node) {
                $text .= $node->textContent.PHP_EOL;
            }
            return $text.PHP_EOL.PHP_EOL.' И. Губерман';
        } catch (\Exception $e) {
            return 'Ой-ой что-то где-то пошло как-то не так...';
        }
    }
}
