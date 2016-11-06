<?php

namespace Tests\AppBundle\Utils;
use AppBundle\Utils\Scraper;

class ScraperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests the Read method, the public method of our scraper, the only method
     * that should be called from outside. (see the to-do from Scraper.php)
     */
    public function testRead() {
        Scraper::$publishers['elpais']['rss'] = dirname(__FILE__).'/example.xml';
        $scraper = new Scraper();
        $feed = $scraper->read('elpais');

        $this->assertEquals('El ‘efecto Trump’: una inusual volatilidad electoral llega a Wall Street', $feed->getTitle());
    }

    /**
     * Tries to get the SimpleXML object from the readRss method.
     * This mocks a new publisher with local rss to avoid make an external
     * call on a unit test.
     */
    public function testReadRssMock() {
        Scraper::$publishers['mockNews'] = [
            'code' => 'mockNews',
            'url' => 'http://mockNews.com/',
            'rss' => dirname(__FILE__).'/example.xml'
        ];
        $scraper = new Scraper();
        $content = $scraper->readRss('mockNews');

        $this->checkTitle($content, 'Portada de EL PAÍS');
    }

    /**
     * @expectedException \Exception
     */
    public function testReadRssException()
    {
        $scraper = new Scraper();
        $scraper->readRss('ElMundoToday');
    }

    /**
     * Tests if ReadXML method can actually read an xml
     */
    public function testReadXML()
    {
        // example xml
        $xml = file_get_contents('example.xml', FILE_USE_INCLUDE_PATH);

        // Scrap it
        $scraper = new Scraper();
        $result = $scraper->readXML($xml);

        $this->checkTitle($result, 'Portada de EL PAÍS');
    }

    /**
     * Helper for check the title on channel->title
     *
     * @param $content - SimpleXML Object
     * @param $expectedTitle - The expected title string
     */
    private function checkTitle($content, $expectedTitle) {
        // Method more cool but code less readable and test error not enough verbose
        // $title = trim($content->xpath('channel/title')[0]);
        // $this->assertEquals($expectedTitle, $title);

        // Check if title available
        $this->assertObjectHasAttribute('channel', $content);
        $this->assertObjectHasAttribute('title', $content->channel);
        $title = trim($content->channel->title);

        // Check if xml read correctly
        $this->assertEquals($expectedTitle, $title);
    }

}
