<?php

namespace blogmd\controller;

use blogmd\model\DaoMdView;
use blogmd\model\MdView;

import('module.blogmd.model.*');

class BlogmdAdminController extends \Controller
{
    private $daoMdView;

    public function __construct()
    {
        parent::__construct();
        $this->daoMdView = new DaoMdView();


    }

    public function viewCreateArticleAction()
    {
        return $this->render('admin/addArticle.twig');
    }

    public function createArticleAction()
    {
        $form = $this->getForm('addArticle');
        if (!$form->validate()) {
            return $this->render('admin/addArticle.twig');
        }
        $this->getPost()->set('content', html_entity_decode($this->getPost()->get('content'), ENT_QUOTES, 'utf-8'));
        $mdView = new MdView($this->getPost()->get('content'), null, $this->getPost()->get('logo'), $this->getPost()->get('title'));
        $mdView->setTagsFromString($this->getPost()->get('tags'));
        $mdFile = new \File(ROOT . '/module/blogmd/resources/markdown/' . $mdView->getFilenameFromTitle());
        if ($mdFile->isFile()) {
            return $this->render('admin/addArticle.twig', array('error' => 'An article with this title already exist.'));
        }
        $mdFile->setContent($mdView->toContentFile());
        $title = $this->getPost()->get('title');
        $form->clearSendValue();

        return $this->render('admin/addArticle.twig', array('success' => 'Your article "' . $title . '" has been created.'));

    }

    public function viewUpdateArticleAction()
    {
        $article = $this->getFromSlug();
        if (empty($article)) {
            return $this->listArticleAction();
        }
        $post = $this->getRequest()->getPost();

        define("TITLE_ARTICLE", $article->getTitle());
        define("LOGO_ARTICLE", $article->getLogo());
        define("TAGS_ARTICLE", $article->getTagsToString());
        define("CONTENT_ARTICLE", $article->getOriginalContent());
        return $this->render('admin/addArticle.twig', array('article' => $article));
    }

    public function updateArticleAction()
    {

        $slug = $this->getRequest()->getInfoRequest('slug');
        $slugs = $this->daoMdView->getSlugs();

        $mdFile = new \File($slugs[$slug]);
        if (!$mdFile->isFile()) {
            return $this->listArticleAction();
        }
        $article = $this->daoMdView->getMdView($mdFile);
        $form = $this->getForm('updateArticle');
        if (!$form->validate()) {
            return $this->render('admin/addArticle.twig', array('article' => $article));
        }
        $this->getPost()->set('content', html_entity_decode($this->getPost()->get('content'), ENT_QUOTES, 'utf-8'));
        $mdView = new MdView($this->getPost()->get('content'), null, $this->getPost()->get('logo'), $this->getPost()->get('title'));
        $mdView->setTagsFromString($this->getPost()->get('tags'));
        $mdFile->setContent($mdView->toContentFile());
        return $this->render('admin/listArticle.twig', array('articles' => $this->daoMdView->listMdView(), 'success' => 'Article successfully updated.'));
    }

    public function deleteArticleAction()
    {
        $slug = $this->getRequest()->getInfoRequest('slug');
        $slugs = $this->daoMdView->getSlugs();
        try {
            $mdFile = new \File($slugs[$slug]);
            $mdFile->remove();
        } catch (\Exception $e) {

        }

        return $this->render('admin/listArticle.twig', array('articles' => $this->daoMdView->listMdView()));
    }

    public function listArticleAction()
    {
        
        return $this->render('admin/listArticle.twig', array('articles' => $this->daoMdView->listMdView()));
    }

    public function viewCreateImageAction()
    {
        return $this->render('admin/addImage.twig', array('images' => $this->listImage()));
    }
    public function deleteImageAction()
    {
        try {
            $mdFile = new \File(ROOT . '/module/blogmd/resources/image/md/'. basename($this->getGet()->get('image')));
            $mdFile->remove();
        } catch (\Exception $e) {

        }
        return $this->render('admin/addImage.twig', array('images' => $this->listImage()));
    }
    public function createImageAction()
    {
        $form = $this->getForm('addImage');
        if (!$form->validate()) {
            return $this->render('admin/addImage.twig');
        }
        
        $form->clearSendValue();
        $upladFilesName = $form->getFilesUploadInfo();
        
        return $this->render('admin/addImage.twig', array(
            'success' => 'Image uploaded',
            'images' => $this->listImage()
            )
        );

    }
    public function listImage(){
        $folder = new \Folder(ROOT . '/module/blogmd/resources/image/md');
        $images = $folder->getFiles('#.*#i');
        $imageResource = array();
        foreach ($images as $image) {
            $imageResource[] = 'md/'. $image->getName();
        }
        return $imageResource;
    }
    public function getFromSlug($request = null)
    {
        if (empty($request)) {
            $request = $this->getRequest();
        }
        $slug = $request->getInfoRequest('slug');
        $slugs = $this->daoMdView->getSlugs();
        if (empty($slugs[$slug])) {
            return null;
        }
        $mdFile = new \File($slugs[$slug]);
        return $this->daoMdView->getMdView($mdFile);
    }
}