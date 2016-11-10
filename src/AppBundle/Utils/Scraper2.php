<?php
/**
 * Class for scraping several online newspaper covers.
 *
 * User: desaroger
 * Date: 8/11/16
 * Time: 21:05
 */

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use AppBundle\Entity\Feed;

use Symfony\Component\DomCrawler\Crawler;

class Scraper2
{
    /**
     * It stores the valid publishers. Perhaps in the future this might be
     * stored on DB, but for now we need to make custom scraping for
     * different publishers.
     *
     * @var array
     */
    public static $staticPublishers = [
        'elpais' => [
            'code' => 'elpais',
            'printable' => 'El País',
            'url' => 'http://elpais.com/'
        ],
        'elmundo' => [
            'code' => 'elmundo',
            'printable' => 'El Mundo',
            'url' => 'http://www.elmundo.es/'
        ],
        'elconfidencial' => [
            'code' => 'elconfidencial',
            'printable' => 'El Confidencial',
            'url' => 'http://www.elconfidencial.com/'
        ],
        'larazon' => [
            'code' => 'larazon',
            'printable' => 'La Razón',
            'url' => 'http://www.larazon.es/'
        ],
        'elperiodico' => [
            'code' => 'elperiodico',
            'printable' => 'El Periódico',
            'url' => 'http://www.elperiodico.com/es/'
        ]
    ];

    /**
     * Scraper2 constructor.
     * @param EntityManager $em
     * @param Container $container
     */
    public function __construct(EntityManager $em, Container $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->repository = $container->get('doctrine')->getRepository('AppBundle:Feed');
        $this->publishers = self::$staticPublishers;
    }

    /**
     * Checks if a given publisher exists.
     *
     * @param $publisher
     * @return bool
     */
    public function isPublisher($publisher)
    {
        return isset($this->publishers[$publisher]);
    }

    /**
     * As isPublisher, but throws if isn't.
     *
     * @param $publisher
     * @throws \Exception
     */
    public function checkIsPublisher($publisher)
    {
        if (!$this->isPublisher($publisher)) {
            throw new \Exception("'$publisher' isn't a valid publisher.");
        }
    }

    /**
     * Loads the html from a publisher.
     *
     * @param $publisher
     * @return Crawler
     */
    public function loadHtml($publisher)
    {
        $this->checkIsPublisher($publisher);

        $url = $this->publishers[$publisher]['url'];

        /**
         * Save the curl method if at some point file_get_contents fails.
         */
//        $ch = \curl_init($url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
//        curl_setopt($ch, CURLOPT_USERAGENT, 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)');
////        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
////        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
//        $html = curl_exec($ch);
//        curl_close($ch);

        $html = file_get_contents($url);

        return $html;
    }

    /**
     * Parses the html and returns the Feed entity.
     *
     * @param $html - The html to be parsed
     * @param $publisher - The publisher of the html
     * @return Feed - The Feed with the information of html parsed.
     */
    public function htmlToFeed($html, $publisher)
    {
        $this->checkIsPublisher($publisher);

        // Initialize everything
        $rawFeed = ['title' => '', 'body' => '', 'image' => '', 'source' => ''];
        $crawler = new Crawler($html);

        // Helper to short things.
        $craw = function ($a, $b, $c = false, $d = false) {
            return $this->safeTextCrawler($a, $b, $c, $d);
        };

        // Scraper itself
        if ($publisher == 'elpais') {
            $article            = $crawler->filter('body article.articulo--primero')->first();
            $rawFeed['title']   = $craw($article->filter('.articulo__interior > [itemprop="headline"] > a'),  'text');
            $rawFeed['body']    = $craw($article->filter('.articulo__interior > [itemprop="description"]'),   'text');
            $rawFeed['image']   = $craw($article->filter('.articulo__interior > [itemprop="image"] a img'),   'attr', 'data-src');
            $rawFeed['source']  = $craw($article->filter('.articulo__interior > [itemprop="headline"] a'),    'attr', 'href', 'http://elpais.com');
        } else if ($publisher == 'elmundo') {
            $article            = $crawler->filter('body .flex-a .content-item:first-child')->first();
            $rawFeed['title']   = $craw($article->filter('article > header a'),     'text');
            $rawFeed['body']    = $craw($article->filter('article > p.entradilla'), 'text');
            $rawFeed['image']   = $craw($article->filter('article > figure[itemprop="image"] img'), 'attr', 'src', true);
            $rawFeed['source']  = $craw($article->filter('article > header a'),     'attr', 'href');
        } else if ($publisher == 'elconfidencial') {
            $area               = $crawler->filter('body .content-areas > .area')->first();
            $section            = $area->filter('.opening-container > div > section')->first();
            $article            = $section->filter('.group')->first();
            $titleLink          = $article->filter('article .art-tit a');
            $rawFeed['title']   = $craw($titleLink, 'text');
            $rawFeed['source']  = $craw($titleLink, 'attr', 'href');
            $rawFeed['body']    = $craw($article->filter('article > .leadin'), 'text');
            $rawFeed['image']   = $craw($article->filter('article > figure.art-fig img'), 'attr', 'src');

        } else if ($publisher == 'larazon') {
            $biggerFirstTitle   = $crawler->filter('body .headline.xlarge')->first();
            $article            = $biggerFirstTitle->parents()->first()->parents()->first();
            $titleLink          = $article->filter('.teaserPrincipal > .headline a');
            $rawFeed['title']   = $craw($titleLink, 'text');
            $rawFeed['source']  = $craw($titleLink, 'attr', 'href', 'http://www.larazon.es');
            $rawFeed['image']   = $craw($article->filter('.teaserPrincipal > .media img'), 'attr', 'src', 'http://www.larazon.es');
            $rawFeed['body']    = $craw($article->filter('.teaserPrincipal > .teaser p:first-child'), 'text');

        } else if ($publisher == 'elperiodico') {
            $article = $crawler->filter('body .ep-noticia.tam-1')->first();
            if (count($article) == 0) {
                $article = $crawler->filter('body .ep-noticia.tam-2')->first();
            }
            $titleLink          = $article->filter('h2 a');
            $rawFeed['title']   = $craw($titleLink, 'text');
            $rawFeed['source']  = $craw($titleLink, 'attr', 'href', 'http://www.elperiodico.com/');
            $rawFeed['body']    = $craw($article->filter('.subtitulo'), 'text');
            $rawFeed['image']   = $craw($article->filter('.thumb img'), 'attr', 'src');
        }

        /* Preparations */

        // Trim everything
        $rawFeed = array_map(function($item) {
            return trim((string) $item);
        }, $rawFeed);

        // Html
        foreach(['title', 'body'] as $prop) {
            $value = $rawFeed[$prop];
            $value = htmlspecialchars_decode($value); // Decode html
            $value = strip_tags($value);              // Delete it
            $value = htmlspecialchars_decode($value); // Redecode (sometimes there is br encoded)
            $feedArray[$prop] = $value;
        }

        // Url
        foreach(['source', 'image'] as $prop) {
            if ($rawFeed[$prop] == '')
                continue;
            $base = $this->publishers[$publisher]['url'];
            $rawFeed[$prop] = $this->absolutizeUrl($rawFeed[$prop], $base);
        }

        /* Extra */

        // For transparentPixel, 1x1 pixel
        if (strpos($rawFeed['image'], 'transparent') > 0) {
            $rawFeed['image'] = '';
        }

        /* Create Feed */

        $feed = new Feed();
        $feed->setTitle($rawFeed['title']);
        $feed->setBody($rawFeed['body']);
        $feed->setImage($rawFeed['image']);
        $feed->setSource($rawFeed['source']);
        $feed->setPublisher($publisher);


        return $feed;
    }

    /**
     * Reads the cover of a publisher (or all publishers)
     * and persist the data.
     *
     * @param $targetPublisher (optional) - The publisher to be readed. If null, all
     * will be read.
     * @return array - An array of created/updated feeds.
     */
    public function read($targetPublisher)
    {
        $logger = $this->container->get('logger');

        // Determine if scrap one or all the publishers
        $publishers = [];
        if ($targetPublisher) {
            $publishers[$targetPublisher] = [];
        } else {
            $publishers = $this->publishers;
        }

        $feeds = [];
        foreach ($publishers as $publisher => $dump) {
            $html = $this->loadHtml($publisher);
            $feed = $this->htmlToFeed($html, $publisher);
            $feed = $this->persistFeed($feed);
            $logger->info("Scraped '$publisher'", $feed->toArray());
            $feeds[] = $feed;
        }

        return $feeds;
    }

    /**
     * Allows to persist a Feed.
     * Adds bool property '_createdNow' determining if the feed
     * was created now or was updated.
     *
     * @param $feed - The feed to be persisted.
     * @return Feed - The persisted feed (now with id, etc)
     */
    public function persistFeed($feed)
    {
        $isCreation = true;

        // Prepare persistence
        $doctrine = $this->container->get('doctrine');
        $repository = $doctrine->getRepository('AppBundle:Feed');
        $em = $this->em;

        // Find existing Feed
        $previousFeed = $repository->findOneBy([
            'created' => new \DateTime(),
            'publisher' => $feed->getPublisher()
        ]);
        if (!is_null($previousFeed)) {
            $isCreation = false;
            $previousFeed->hydrate($feed);
            $feed = $previousFeed;
        }

        // Persist
        $em->persist($feed);
        $em->flush($feed);

        // For show debug info on console command
        $feed->_createdNow = $isCreation;

        return $feed;
    }

    /**
     * Little method to help us shorting the crawler method. I always get the "Empty node list" before, so ended
     * up with so many Ifs and try/catchs. This functions aims to replace them.
     *
     * @param $crawler - The Symfony Dom crawler object
     * @param $method - The method to be executed if there is some node
     * @param $argument - The argument to pass to that function
     * @param $baseAbsolutizeUrl - If passed, the base with which the result will be absolutized
     * @return string - The result string. Empty string if there wasn't a node.
     */
    private function safeTextCrawler($crawler, $method, $argument = false, $baseAbsolutizeUrl = false)
    {
        if (count($crawler) == 0) {
            return '';
        }
        $elem = $crawler->first();

        // Run the method
        $result = $argument ? $elem->$method($argument) : $elem->$method();

        // Absolutize if needed
        if ($baseAbsolutizeUrl) {
            $result = $this->absolutizeUrl($result, $baseAbsolutizeUrl);
        }

        return $result;
    }

    /**
     * Absolutize a url if needed. Also fix some incomplete urls.
     *
     * @param $url - The url to absolutize.
     * @param $base - The domain to be added.
     * @return string - The absolute url.
     */
    private function absolutizeUrl($url, $base)
    {
        // Absolute yet
        if (strpos($url, "http://") === 0) {
            return $url;
        }
        if (strpos($url, "//") === 0) {
            return 'http:' . $url;
        }

        // Remove first '/'
        if (strpos($url, "/") === 0) {
            $url = substr($url, 1);
        }

        // Add '/' to base
        if (substr($base, -1) != '/') {
            $base = $base . '/';
        }

        return $base . $url;
    }

}
