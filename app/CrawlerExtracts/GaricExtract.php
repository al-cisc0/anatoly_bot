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
            $text = $crawler->filter('div.fb2-stanza')->getNode(rand(0,$count))->textContent;
            return $text.PHP_EOL.PHP_EOL.' И. Губерман';
        } catch (\Exception $e) {
            return 'Ой-ой что-то где-то пошло как-то не так...';
        }
    }
}
