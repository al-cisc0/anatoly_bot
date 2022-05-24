<?php

namespace App\CrawlerExtracts;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class BearExtract
{
    public static function getExtract()
    {
        try {
            $url = 'https://memepedia.ru/medved-v-kustax/';
            $client = New Client();
            $response = $client->request('GET',$url)->getBody()->getContents();
            $crawler = new Crawler();
            $crawler->addHtmlContent($response);
            $count = $crawler->filter('div.js-mediator-article.s-post-content.s-post-small-el.bb-mb-el p img')->count();
            $node = $crawler->filter('div.js-mediator-article.s-post-content.s-post-small-el.bb-mb-el p img')->eq(rand(0,$count-1));
            $text = $node->attr('src');
            return $text;
        } catch (\Exception $e) {
            return 'Ой-ой что-то где-то пошло как-то не так...';
        }
    }
}
