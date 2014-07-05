<?php
use Everzet\Jade\Jade;
use Everzet\Jade\Parser;
use Everzet\Jade\Lexer\Lexer;
use Everzet\Jade\Dumper\PHPDumper;
use Everzet\Jade\Visitor\AutotagsVisitor;

use Everzet\Jade\Filter\JavaScriptFilter;
use Everzet\Jade\Filter\CDATAFilter;
use Everzet\Jade\Filter\PHPFilter;
use Everzet\Jade\Filter\CSSFilter;
use Everzet\Jade\Filter\ImportFilter;

require_once __DIR__.'/../jade/vendor/symfony/src/Symfony/Framework/UniversalClassLoader.php';
use Symfony\Framework\UniversalClassLoader;
import('arhframe.renderer.AbstractRenderer');
import('arhframe.eden.eden');
$loader = new UniversalClassLoader();
$loader->registerNamespaces(array('Everzet' => __DIR__.'/src'));
$loader->register();
/**
*
*/
class JadeRenderer extends AbstractRenderer
{
    private $jade;
    private $html;
    private $loader;
    private $pageNameFinal;
    public function __construct()
    {
        parent::__construct();
        import('arhframe.renderer.filterToLoad.**');
        import('arhframe.renderer.functionToLoad.**');
        $this->setName('jade');

    }
    public function createRenderer($page, $array=null)
    {
        $this->pageName = $page;
        $forced = DependanceManager::parseForce($page);
        $pageNameFinal = DependanceManager::parseForceFileName($page);
        $this->pageNameFinal = $forced .'/'. $pageNameFinal;
        parent::createRenderer($page, $array);
        $pageId = couper_texte_sec($page, 30);
        $this->html = cache($this)->get($pageId);
        if (!empty($this->html)) {
            return;
        }
        $this->loader = new UniversalClassLoader();
        $this->loader->registerNamespaces(array('Everzet' => __DIR__.'/../jade/src'));
        $this->loader->register();
        $dumper = new PHPDumper();
        $dumper->registerVisitor('tag', new AutotagsVisitor());
        $dumper->registerFilter('javascript', new JavaScriptFilter());
        $dumper->registerFilter('cdata', new CDATAFilter());
        $dumper->registerFilter('php', new PHPFilter());
        $dumper->registerFilter('style', new CSSFilter());
        $dumper->registerFilter('import', new ImportFilter());

        // Initialize parser & Jade
        $parser = new Parser(new Lexer());
        $this->jade   = new Jade($parser, $dumper, cache($this)->getCacheFolder());
        $this->html = $this->jade->render($this->page);
        cache($this)->set($pageId, $this->html);
    }
    public function getHtml()
    {
        if (!$this->isFile()) {
            return $this->html;
        }
       
        if (!empty($this->array)) {
            $array = $this->array;
            extract($array);
        }
        $file = __DIR__.'/../tmp/' . $this->pageNameFinal .'.php';
        $pathinfo = pathinfo($file);
        $folder = new Folder($pathinfo['dirname']);
        $folder->create();
        file_put_contents($file, $this->html);
        ob_start();
        require $file;
        $content = ob_get_clean();
        return $content;
    }
    public function getJade()
    {
        return $this->jade;
    }
    public function __destruct()
    {
        if (empty($this->loader)) {
            return;
        }
        spl_autoload_unregister(array($this->loader, 'loadClass'));
    }
}
