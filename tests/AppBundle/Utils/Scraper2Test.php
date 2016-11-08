<?php

namespace Tests\AppBundle\Utils;

// Mock em and repository
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;

// Test
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Scraper2Test extends KernelTestCase
{
    private $container;
    private $em;

    /**
     * Load the container and the EntityManager
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->container = static::$kernel->getContainer();
        $this->em = $this->container
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Tests .isPublisher: Determine if some string is a publisher.
     */
    public function testIsPublisher()
    {
        $scraper = $this->container->get('app.scraper2');
        $this->assertTrue(
            method_exists($scraper, 'isPublisher'),
            'Class does not have method isPublisher'
        );

        // Valid publishers
        $publishers = ['elpais', 'elmundo', 'elconfidencial', 'larazon', 'elperiodico'];
        foreach($publishers as $publisher) {
            $this->assertTrue(
                $scraper->isPublisher($publisher),
                "Method isPublisher considers '$publisher' isn't a publisher."
            );
        }

        // Invalid publisher
        $this->assertFalse($scraper->isPublisher('elperiódico'));
    }

    /**
     * Checks .checkIfPublisher: Throws if invalid publisher.
     *
     * @expectedException \Exception
     */
    public function testReadRssException()
    {
        $scraper = $this->container->get('app.scraper2');
        $scraper->checkIsPublisher('nope');
    }

    /**
     * Checks .loadHtml: Gets the html from a publisher.
     */
    public function testLoadHtml()
    {
        $scraper = $this->container->get('app.scraper2');
        $scraper->publishers['elpais']['url'] = dirname(__FILE__).'/fixtures/elpais.html';
        $html = $scraper->loadHtml('elpais');
        $this->assertEquals(2, strpos($html, 'DOCTYPE'));
    }

    /**
     * Checks .parseToFeed: html -> Feed entity
     */
    public function testHtmlToFeed()
    {
        $scraper = $this->container->get('app.scraper2');

        $publishers = ['elpais', 'elmundo', 'elconfidencial', 'larazon', 'elperiodico'];
        foreach($publishers as $publisher) {
            $scraper->publishers[$publisher]['url'] = dirname(__FILE__)."/fixtures/$publisher.html";
            $html = $scraper->loadHtml($publisher);
            $feed = $scraper->htmlToFeed($html, $publisher);
            $this->assertTrue($feed->getTitle() != '');
        }
    }

    /**
     * Checks .persistFeed: Persist a Feed to the DB. Makes an upsert.
     */
    public function testPersistFeed()
    {
        $scraper = $this->container->get('app.scraper2');
        $scraper->publishers['elpais']['url'] = dirname(__FILE__).'/fixtures/elpais.html';
        $html = $scraper->loadHtml('elpais');
        $feed = $scraper->htmlToFeed($html, 'elpais');
        $this->assertTrue($feed->getId() === null);
        $feed = $scraper->persistFeed($feed);
        $this->assertTrue($feed->getId() !== null);
    }

    /**
     * Checks .read: Reads a publisher or all publisher and persists
     */
    public function testRead()
    {
        $scraper = $this->container->get('app.scraper2');

        // Mock
        $publishers = ['elpais', 'elmundo', 'elconfidencial', 'larazon', 'elperiodico'];
        foreach($publishers as $publisher) {
            $scraper->publishers[$publisher]['url'] = dirname(__FILE__)."/fixtures/$publisher.html";
        }

        // Read all
        $feeds = $scraper->read(false);

        // Assert
        $this->assertEquals(5, count($feeds));
        foreach($feeds as $feed) {
            $this->assertTrue($feed->getId() !== null);
        }
    }

}
