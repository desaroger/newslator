<?php
/**
 * Created by PhpStorm.
 * User: desaroger
 * Date: 6/11/16
 * Time: 17:39
 */

namespace AppBundle\Utils;

class Scraper
{
    public static $publishers = [
        'elpais' => [
            'code' => 'elpais',
            'url' => 'http://elpais.com/',
            'rss' => 'http://ep00.epimg.net/rss/elpais/portada.xml'
        ]
    ];


    public function readXML($xmlstr) {
        return new \SimpleXMLElement($xmlstr);
    }





}






