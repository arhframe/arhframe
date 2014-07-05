<?php

/**
*
*/
class HtmlProxy
{
    private $headBeginning = array();
    private $headEnding = array();
    private $bodyBeginning = array();
    private $bodyEnding = array();
    private $html;
    private $htmlManipulator;
    private $noRewrite = false;
    public function __construct()
    {
        # code...
    }
    public function setHtml($html)
    {
        if (stristr($html, '<html')===false) {
            $this->html = $html;
            $this->noRewrite = true;

            return;
        }
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);

        $htmlDoc = $dom->getElementsByTagName('html')->item(0);
        $body = $dom->getElementsByTagName('body');
        if ($body->length==0) {
            $node = $dom->createElement('body');
            if ($htmlDoc->hasChildNodes()) {
                $htmlDoc->insertBefore($node,$htmlDoc->firstChild);
            } else {
                $htmlDoc->appendChild($base);
            }

        }
        $body = $dom->getElementsByTagName('body')->item(0);
        $head = $dom->getElementsByTagName('head');
        if ($head->length==0) {

            $node = $dom->createElement('head');
            if ($htmlDoc->hasChildNodes()) {
                $htmlDoc->insertBefore($node,$htmlDoc->firstChild);
            } else {
                $htmlDoc->appendChild($base);
            }
        }
        $this->html = $dom->saveHTML();
    }
    public function appendHead($head)
    {
        $this->headEnding[] = $head;
    }
    public function prependHead($head)
    {
        $this->headBeginning[] = $head;
    }
    public function appendBody($head)
    {
        $this->bodyEnding[] = $head;
    }
    public function prependBody($head)
    {
        $this->bodyBeginning[] = $head;
    }
    public function isNoRewrite()
    {
        return $this->noRewrite;
    }
    public function getHtml()
    {
        if ($this->noRewrite) {
            return $this->html;
        }
        $this->htmlManipulator = new \Wa72\HtmlPageDom\HtmlPage($this->html);
        foreach ($this->headEnding as $value) {
            $this->htmlManipulator->filter('head')->append($value);
        }
        foreach ($this->headBeginning as $value) {
            $this->htmlManipulator->filter('head')->prepend($value);
        }
        foreach ($this->bodyEnding as $value) {
            $this->htmlManipulator->filter('body')->append($value);
        }
        foreach ($this->bodyBeginning as $value) {
            $this->htmlManipulator->filter('body')->prepend($value);
        }
        $this->bodyBeginning = array();
        $this->bodyEnding = array();
        $this->headBeginning = array();
        $this->headEnding = array();
        $this->html = $this->htmlManipulator->save();

        return $this->html;
    }

}
