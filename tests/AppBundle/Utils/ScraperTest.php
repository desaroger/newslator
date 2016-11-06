<?php

namespace Tests\AppBundle\Utils;
use AppBundle\Utils\Scraper;

class ScraperTest extends \PHPUnit_Framework_TestCase
{

    public function testReadXML()
    {
        // example xml
        $xml = file_get_contents('example.xml', FILE_USE_INCLUDE_PATH);

        // Scrap it
        $scraper = new Scraper();
        $result = $scraper->readXML($xml);

        // Check if title available
        $this->assertObjectHasAttribute('channel', $result);
        $this->assertObjectHasAttribute('title', $result->channel);
        $title = trim($result->channel->title);

        // Check if xml read correctly
        $this->assertEquals('Portada de EL PAÍS', $title);
    }

}
