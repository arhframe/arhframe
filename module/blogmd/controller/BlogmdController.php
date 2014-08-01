<?php
namespace blogmd\controller;
use blogmd\model\DaoMdView;
use blogmd\model\MdView;

import('module.blogmd.model.*');

class BlogmdController extends \Controller
{
    private $output = array();
    private $slugs = array();
    private $tags = array();
    private $daoMdView;

    public function __construct()
    {
        parent::__construct();
        $this->daoMdView = new DaoMdView();

        $this->output = array('articles' => $this->daoMdView->listMdView());
        $this->output['tags'] = $this->daoMdView->getTags();

    }


    function indexAction()
    {
        return $this->render('articles.twig', $this->output);
    }

    function articleAction()
    {
        return $this->render('article.twig', $this->output);
    }


    public function getFromSlug($request = null)
    {
        if (empty($request)) {
            $request = $this->getRequest();
        }
        $slug = $request->getInfoRequest('slug');
        $slugs = $this->daoMdView->getSlugs();
        if (empty($slugs[$slug])) {
            return;
        }
        $mdFile = new \File($slugs[$slug]);
        $this->output['article'] = $this->daoMdView->getMdView($mdFile);
        unset($this->output['tags']);
    }

    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param array $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }
}

?>
