<?php
import('arhframe.Controller');

abstract class AbstractControllerArhframe extends Controller
{
    protected $extractData;

    public abstract function action();

    /**
     * @return mixed
     */
    public function getExtractData()
    {
        return $this->extractData;
    }

    /**
     * @param mixed $extractData
     */
    public function setExtractData($extractData)
    {
        $this->extractData = $extractData;
    }


}