<?php

namespace App\CrawlerExtracts;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class BooksExtract
{
    public static function getExtract()
    {
        try {
            $options = "176,1325,438,856,1311,783,94,1171,292,867,470,1029,914,947,1326,1907,346,1216,541,523,549,875,1222,1309,411,270,857,1284,87,665,257,652,776,813,816,36,272,918,1954,697,1291,1179,52,1353,185,621,1129,811,1315,904,2117,181,1218,901,660,792,681,959,897,539,941,1035,502,902,2128,53,377,287,464,374,518,1997,756,1223,244,322,1286,823,155,205,1335,184,1199,1201,40,939,699,428";
            $url = 'https://vsenauka.ru/knigi/vsenauchnyie-knigi/book-details.html?id=';
            $idArr = explode(',',$options);
            $id = $idArr[array_rand($idArr)];
            return 'Братишка, я тебе почитать принес вот '.PHP_EOL.PHP_EOL.$url.$id;
        } catch (\Exception $e) {
            return 'Ой-ой что-то где-то пошло как-то не так...';
        }
    }
}
