<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Util\Inflector;

/**
 * Feed
 *
 * @ORM\Table(name="feed")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FeedRepository")
 */
class Feed
{
    public function __construct() {
        $this->setCreated(new \DateTime());
    }

    /**
     * Ugly implementation of doctrine hydration, as I am unable to make
     * native hydration to work for Feed entity.
     *
     * @param $feed2 - Feed to hydration from
     * @return $this
     */
    public function hydrate($feed2) {
        if (isset($feed2->title)) { $this->title = (string) $feed2->title; }
        if (isset($feed2->body)) { $this->body = (string) $feed2->body; }
        if (isset($feed2->image)) { $this->image = (string) $feed2->image; }
        if (isset($feed2->source)) { $this->source = (string) $feed2->source; }
        return $this;
    }

    public function toArray() {
        $result = [
            'title' => '',
            'body' => '',
            'image' => '',
            'source' => '',
            'publisher' => $this->publisher
        ];

        if (isset($this->title)) { $result['title'] = (string) $this->title; }
        if (isset($this->body)) { $result['body'] = (string) $this->body; }
        if (isset($this->image)) { $result['image'] = (string) $this->image; }
        if (isset($this->source)) { $result['source'] = (string) $this->source; }

        return $result;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="string", length=255)
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255, nullable=true)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="publisher", type="string", length=255, nullable=true)
     */
    private $publisher;

    /**
     * @var date
     *
     * @ORM\Column(name="created", type="date")
     */
    private $created;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Feed
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return Feed
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Feed
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Feed
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set publisher
     *
     * @param string $publisher
     *
     * @return Feed
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set created
     *
     * @param date $created
     *
     * @return Feed
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return date
     */
    public function getCreated()
    {
        return $this->created;
    }
}

