<?php

namespace Tests\AppBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScraperTest extends KernelTestCase
{
    private $container;
    private $em;


    protected function setUp()
    {
        self::bootKernel();

        $this->container = static::$kernel->getContainer();
        $this->em = $this->container
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Tests the Read method, the public method of our scraper, the only method
     * that should be called from outside. (see the to-do from Scraper.php)
     */
    public function testRead() {
        $scraper = $this->container->get('app.scraper');
        $scraper->publishers['elpais']['rss'] = dirname(__FILE__).'/example.xml';
        $feed = $scraper->read('elpais');

        $this->assertEquals('El ‘efecto Trump’: una inusual volatilidad electoral llega a Wall Street', $feed->getTitle());
    }

    /**
     * Tries to get the SimpleXML object from the readRss method.
     * This mocks a new publisher with local rss to avoid make an external
     * call on a unit test.
     */
    public function testReadRssMock() {
        $scraper = $this->container->get('app.scraper');
        $scraper->publishers['elpais']['rss'] = dirname(__FILE__).'/example.xml';
        $content = $scraper->readRss('elpais');

        $this->checkTitle($content, 'Portada de EL PAÍS');
    }

    /**
     * @expectedException \Exception
     */
    public function testReadRssException()
    {
        $scraper = $this->container->get('app.scraper');
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
        $scraper = $this->container->get('app.scraper');
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
