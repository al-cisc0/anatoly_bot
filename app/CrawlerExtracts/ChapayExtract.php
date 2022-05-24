<?php

namespace App\CrawlerExtracts;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ChapayExtract
{
    public static function getExtract()
    {
        try {
            $url = 'https://www.anekdot.ru/tags/%D1%87%D0%B0%D0%BF%D0%B0%D0%B5%D0%B2/?sort=sum';
            $client = New Client();
            $response = $client->request('GET',$url)->getBody()->getContents();
            $crawler = new Crawler();
            $crawler->addHtmlContent($response);
            $count = $crawler->filter('div.topicbox div.text')->count();
            $text = $crawler->filter('div.topicbox div.text')->getNode(rand(0,$count-1))->textContent;
            return $text;
        } catch (\Exception $e) {
            return 'Ой-ой что-то где-то пошло как-то не так...';
        }
    }
}
