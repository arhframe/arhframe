<?php
/**
 * Created by IntelliJ IDEA.
 * User: arthurhalet
 * Date: 08/07/14
 * Time: 21:10
 */

namespace blogmd\model;
package('module.blogmd.model');


class DaoMdView
{
    private $slugs = array();
    private $tags = array();
    private $loaded = false;
    private $articles = array();


    public function listMdView($lazyLoad = false)
    {
        if ($this->loaded) {
            return;
        }
        if (!$lazyLoad) {
            $this->articles = array();
            $this->slugs = array();
            $this->tags = array();
        }
        $cache = cache('DaoMdView');
        if ($lazyLoad) {
            $slugs = $cache->get('slugsMd');
            if (!empty($slugs)) {
                $this->slugs = $slugs;
                return;
            }
        }
        $folder = new \Folder(ROOT . '/module/blogmd/resources/markdown');
        $mdFiles = $folder->getFiles('#.*\.md$#i');

        foreach ($mdFiles as $mdFile) {
            $mdView = $this->getMdView($mdFile);
            $this->slugs[$mdView->getSlug()] = $mdFile->absolute();
            $this->tags = array_merge($this->tags, $mdView->getTags());
            $this->articles[] = $mdView;
        }
        if (!$lazyLoad) {
            $this->loaded = true;
        }
        usort($this->articles, function ($a, $b) {
            if ($a->getTimestamp() == $b->getTimestamp()) {
                return 0;
            }
            return ($a->getTimestamp() > $b->getTimestamp()) ? -1 : 1;
        });
        $cache->set('slugsMd', $this->slugs);
        return $this->articles;
    }

    /**
     * @return array
     */
    public function getSlugs()
    {
        $this->listMdView(true);
        return $this->slugs;
    }

    public function getMdView($mdFile)
    {
        $data = $this->extractData($mdFile->getContent());
        $mdView = new MdView($data['content'], date('d/m/Y H:i:s', $mdFile->getTime()), $data['logo'], $data['title'], $data['originalContent']);
        $mdView->setTimestamp($mdFile->getTime());
        if (!empty($data['slug'])) {
            $mdView->setSlug($data['slug']);
        }
        if (!empty($data['tags'])) {
            $mdView->setTagsFromString($data['tags']);
        }

        return $mdView;
    }

    private function extractData($mdFileContent)
    {
        preg_match_all('#%(.*=.*)%$#im', $mdFileContent, $return);
        $return = implode("\n", $return[1]);
        $data = parse_ini_string(trim($return));
        $parseDown = new \ParsedownExtra();
        $mdFileContent = preg_replace('#%(.*=.*)%$#im', '', $mdFileContent);
        $data['content'] = html_entity_decode($parseDown->text($mdFileContent), ENT_QUOTES, 'utf-8');
        $data['originalContent'] = $mdFileContent;
        return $data;

    }


    /**
     * @return array
     */
    public function getTags()
    {
        $this->listMdView();
        return array_unique($this->tags);
    }


}
