<?php
namespace blogmd\model;


class MdView
{
    private $title;
    private $date;
    private $logo;
    private $content;
    private $shortContent;
    private $slug;
    private $tags = array();
    private $originalContent;
    private $timestamp;

    function __construct($content, $date, $logo, $title, $originalContent = null)
    {
        $this->originalContent = $originalContent;
        $this->content = $content;
        $this->date = $date;
        $this->logo = $logo;
        $this->title = $title;
        $this->shortContent = html_entity_decode(strip_tags($this->content), ENT_QUOTES, 'utf-8');
        $this->shortContent = couper_texte($this->shortContent, 50, '...');
        $this->setSlug($this->title);
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getShortContent()
    {
        return $this->shortContent;
    }

    /**
     * @param string $shortContent
     */
    public function setShortContent($shortContent)
    {
        $this->shortContent = $shortContent;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        $this->slug = removeaccents($this->slug);
        $this->slug = preg_replace('#[^A-Za-z0-9]+#', '-', $this->slug);
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function setTagsFromString($string)
    {
        $tags = explode(',', $string);
        foreach ($tags as $tag) {
            $this->tags[] = trim($tag);
        }
    }

    public function getTagsToString()
    {
        return implode(',', $this->tags);
    }

    public function getFilenameFromTitle()
    {
        return $this->getSlug() . '.md';
    }

    /**
     * @return mixed
     */
    public function getOriginalContent()
    {
        return $this->originalContent;
    }

    /**
     * @param mixed $originalContent
     */
    public function setOriginalContent($originalContent)
    {
        $this->originalContent = $originalContent;
    }

    public function toContentFile()
    {
        $text = "%logo=" . $this->logo . "%\n";
        $text .= "%title=" . $this->title . "%\n";
        $text .= "%tags=" . implode(',', $this->tags) . "%\n\n";
        $text .= $this->content;
        return $text;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }
} 