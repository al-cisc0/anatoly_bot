<?php

namespace App\CrawlerExtracts;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class PickupMasterExtract
{
    public static function getExtract()
    {
        try {
            $url = 'https://intrigue.dating/interesnoe/tupye-podkaty-k-devushke-frazy-kotorye-toje-mogut-srabotat/';
            $client = New Client();
            $response = $client->request('GET',$url)->getBody()->getContents();
            $crawler = new Crawler();
            $crawler->addHtmlContent($response);
            $count = $crawler->filter('div.entry-content ul li')->count();
            $text = $crawler->filter('div.entry-content ul li')->getNode(rand(0,$count))->textContent;
            $text = str_replace('«','',$text);
            $text = str_replace('»','',$text);
            $text = str_replace(';','',$text);
            return $text;
        } catch (\Exception $e) {
            return 'Ой-ой что-то где-то пошло как-то не так...';
        }
    }
}
