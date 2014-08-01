<?php
import('arhframe.form.Zebra_Form');
import('arhframe.LanguageManager');
import('arhframe.renderer.*');
import('arhframe.NeedleManager');
import('arhframe.Router');
import('arhframe.secure.Secure');
import('arhframe.cache.CacheManager');
import('arhframe.BeanLoader');
import('arhframe.yamlarh.Yamlarh');

class FormManager
{
    private $alajaxFile = "alajax.js";
    private $zebraformFile = "zebra_form.js";
    private $valueReceive;
    private $type = null;
    private $success = null;
    private $beforeSend = null;
    private $error;
    private $ajax = false;
    private $zebraForm;
    private $template = null;
    private $folderTemplate = null;
    private $formFolder;
    private $formName;
    private $name;
    private $cacheManager;
    private $formHtml;
    private $languageManager;
    private $proxyHtml;
    private $addCssToHeadPage = true;
    private $debugBarManager;
    private $formStyle;
    private $canCache = true;

    public function __construct($form, $route = null, $method = 'POST')
    {
        $this->name = $form;
        $this->proxyHtml = BeanLoader::getInstance()->getBean('arhframe.htmlProxy');
        $this->debugBarManager = BeanLoader::getInstance()->getBean('arhframe.debugBarManager');
        if (empty($route)) {
            $route = Router::getCurrentRoute();
        } else {
            $router = Router::getInstance();
            $route = $router->writeRoute($router->getRouteByName($route));
        }
        $router = Router::getInstance();
        $forced = DependanceManager::parseForce($form);
        $pageName = DependanceManager::parseForceFileName($form);
        if (!empty($forced)) {
            $module = $forced;
        } else {
            $module = $router->getModule();
        }
        $this->formFolder = '/module/' . $module . '/resources/form';

        if ($method == 'GET') {
            $this->valueReceive = $_GET;
        } else {
            $this->valueReceive = $_POST;
            $method = 'POST';
        }
        $formInfo = pathinfo($form);
        if ($formInfo['dirname'] == '.') {
            $formInfo['dirname'] = '';
        } else {
            $formInfo['dirname'] .= '/';
        }
        $formName = $formInfo['filename'];
        $file = $this->formFolder . "/" . $formInfo['dirname'] . $formName . '.yml';
        if (!is_file(__DIR__ . '/../' . $file)) {
            throw new ArhframeException("The form " . $formName . " does not exist.");

        }
        $form .= 'Form';
        $this->formName = $form;
        $this->languageManager = LanguageManager();
        $this->formHtml = cache($this, true, true)->get($this->languageManager->getLocalization() . '/' . $this->formName);
        $yamlarh = new Yamlarh($file);
        $yamlarh->loadDependance(DependanceManager::getInstance());
        $this->formStyle = $yamlarh->parse();
        $this->zebraForm = new Zebra_Form($form, $method, $route);
        $this->zebraForm->csrf(false);
        $this->zebraForm->language($this->languageManager->getLocalization());
        foreach ($this->formStyle as $id => $value) {
            $this->createForm($id, $value);
        }
        $factoryRenderer = BeanLoader::getInstance()->getBean('arhframe.FactoryRenderer');
        foreach ($factoryRenderer->getRenderersAvailable() as $rendererName) {
            $fileTemplate = $this->formFolder . "/" . $formInfo['dirname'] . $formName . '.' . $rendererName;
            if (is_file($fileTemplate)) {
                $this->template = $formName . '.' . $rendererName;
                $this->folderTemplate = $this->formFolder . "/" . $formInfo['dirname'];
                break;
            }
        }
    }

    private function createForm($id, $info)
    {
        $type = $info['type'];
        $control = null;
        switch ($type) {
            case 'button':
                $control = $this->addButton($id, $info);
                break;
            case 'captcha':
                $control = $this->addCaptcha($id, $info);
                break;
            case 'checkboxes':
                $control = $this->addCheckbox($id, $info);
                break;
            case 'date':
                $control = $this->addDate($id, $info);
                break;
            case 'file':
                $control = $this->addFile($id, $info);
                $info['rules'] = array();
                break;
            case 'hidden':
                $control = $this->addHidden($id, $info);
                break;
            case 'image':
                $control = $this->addImage($id, $info);
                break;
            case 'password':
                $control = $this->addPassword($id, $info);
                break;
            case 'radios':
                $control = $this->addRadios($id, $info);
                break;
            case 'reset':
                $control = $this->addReset($id, $info);
                break;
            case 'select':
                $control = $this->addSelect($id, $info);
                break;
            case 'text':
                $control = $this->addText($id, $info);
                break;
            case 'textarea':
                $control = $this->addTextarea($id, $info);
                break;
            case 'time':
                $control = $this->addTime($id, $info);
                break;
            case 'submit':
                $control = $this->addSubmit($id, $info);
                break;
            default:
                break;
        }
        $this->addAttributes($control, $info['attributes']);
        $this->addRule($control, $info['rules']);
    }

    private function addAttributes($control, $attributes)
    {
        if (empty($attributes) || empty($control)) {
            return;
        }
        if (!empty($attributes['data-prefix'])) {
            $img = explode(':', $attributes['data-prefix']);
            if ($img[0] = 'img') {
                unset($img[0]);
                $img = implode(':', $img);
                $resourcesManager = new ResourcesManager($img);
                $imageFile = $resourcesManager->getHttpFile();
                $attributes['data-prefix'] = 'img:' . $imageFile;
            }
        }
        $control->set_attributes($attributes);
    }

    private function addButton($id, $info)
    {
        return $this->zebraForm->add($info['type'], $id, $this->languageManager->get($info['displayName']), $info['attributes']);
    }

    private function addCaptcha($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $this->zebraForm->add('text', $id);

        return $this->zebraForm->add($info['type'], $id, $info['attach'], $info['storage']);
    }

    private function addDate($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $dateZebra = $this->zebraForm->add($info['type'], $id, date($info['format']));
        $dateZebra->format($info['format']);

        return $dateZebra;
    }

    private function addCheckbox($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $name = $id;
        if (!empty($info['multiple'])) {
            $name .= '[]';
        }

        return $this->zebraForm->add($info['type'], $name, $info['values'], $info['selected']);

    }

    private function addRule($control, $rules)
    {
        if (empty($control) || empty($rules)) {
            return;
        }
        if (empty($rules) || !is_array($rules)) {
            $control->set_rule(array());
        }
        $rulesTab = null;
        foreach ($rules as $key => $rule) {
            foreach ($rule as $value) {
                $rulesTab[$key][] = $this->languageManager->get($value);
            }
        }
        $control->set_rule($rulesTab);
    }

    private function addFile($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $name = $id;
        if (!empty($info['multiple'])) {
            $name .= '[]';
        }
        $control = $this->zebraForm->add($info['type'], $name);
        if (!empty($info['rules']['upload']['uploadpath']) && $info['rules']['upload']['uploadpath'][0] == '/') {
            $info['rules']['upload']['uploadpath'] = ROOT . $info['rules']['upload']['uploadpath'];
        } else if (!empty($info['rules']['upload']['uploadpath'])) {
            $info['rules']['upload']['uploadpath'] = ROOT . $this->formFolder . '/' . $info['rules']['upload']['uploadpath'];
        }
        if ($info['rules']['upload']['filename'] == "random") {
            $info['rules']['upload']['filename'] = ZEBRA_FORM_UPLOAD_RANDOM_NAMES;
        }

        if (!empty($_REQUEST['name_' . $this->formName])) {
            $this->addRule($control, $info['rules']);
        }


        return $control;
    }

    private function addHidden($id, $info)
    {
        return $this->zebraForm->add($info['type'], $id, $info['value']);
    }

    private function addImage($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $resourcesManager = new ResourcesManager($info['image']);
        $imageFile = $resourcesManager->getHttpFile();

        return $this->zebraForm->add($info['type'], $id, $imageFile);
    }

    private function addPassword($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $name = $id;
        if (!empty($info['multiple'])) {
            $name .= '[]';
        }

        return $this->zebraForm->add($info['type'], $name, $info['default']);
    }

    private function addRadios($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $name = $id;
        if (!empty($info['multiple'])) {
            $name .= '[]';
        }

        return $this->zebraForm->add($info['type'], $name, $info['values'], $info['selected']);
    }

    private function addReset($id, $info)
    {
        return $this->zebraForm->add($info['type'], $id, $this->languageManager->get($info['displayName']));
    }

    private function addSelect($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $name = $id;
        if (!empty($info['multiple'])) {
            $name .= '[]';
        }
        $select = $this->zebraForm->add($info['type'], $name, $info['selected']);
        $select->add_options($info['values']);

        return $select;
    }

    private function addText($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $name = $id;
        if (!empty($info['multiple'])) {
            $name .= '[]';
        }

        return $this->zebraForm->add($info['type'], $name, $info['default']);
    }

    private function addTextarea($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $name = $id;
        if (!empty($info['multiple'])) {
            $name .= '[]';
        }

        return $this->zebraForm->add($info['type'], $name, $info['default']);
    }

    private function addTime($id, $info)
    {
        $this->attachLabel($id, $info['displayName']);
        $this->attachNote($id, $info['note']);
        $name = $id;
        if (!empty($info['multiple'])) {
            $name .= '[]';
        }
        $date = null;
        if (!empty($info['default'])) {
            $date = date($info['default']);
        }

        return $this->zebraForm->add($info['type'], $name, $date);
    }

    private function addSubmit($id, $info)
    {
        return $this->zebraForm->add($info['type'], $id, $this->languageManager->get($info['displayName']));
    }

    private function attachNote($id, $note)
    {
        if (empty($note)) {
            return;
        }
        $note = $this->languageManager->get($note);

        $this->zebraForm->add('note', 'note' . ucfirst($id), $id, $note);
    }

    private function attachLabel($id, $text)
    {
        if (empty($text)) {
            return;
        }
        $text = $this->languageManager->get($text);
        if ($inside != true) {
            $inside = false;
        }
        $this->zebraForm->add('label', 'label' . ucfirst($id), $id, $text);
    }

    public function ajax()
    {
        $this->ajax = true;

        return $this;
    }

    public function ajaxType($type)
    {
        $this->ajax = true;
        $this->type = $type;

        return $this;
    }

    public function ajaxSuccess($success)
    {
        $this->ajax = true;
        $this->success = $success;

        return $this;
    }

    public function ajaxBeforeSend($beforeSend)
    {
        $this->ajax = true;
        $this->beforeSend = $beforeSend;

        return $this;
    }

    public function ajaxError($error)
    {
        $this->ajax = true;
        $this->error = $error;

        return $this;
    }

    public function getHtml()
    {
        $start = microtime(true);
        if ($this->addCssToHeadPage) {
            $resourcesManager = new NeedleManager('zebra_form.css');
            $this->proxyHtml->prependHead($resourcesManager->getHtml());
            $this->addCssToHeadPage = false;
        }
        if (empty($this->formHtml) || !$this->canCache) {
            $header = NeedleManager::getJqueryHtml();
            $header .= NeedleManager::loadPluginJquery($this->zebraformFile, 'Zebra_Form');
            $header .= NeedleManager::loadPluginJquery($this->zebraformFile, 'Zebra_DatePicker');
            $this->zebraForm->validate();
            if ($this->template != null) {
                $pathinfo = pathinfo($this->template);
                $renderer = BeanLoader::getInstance()->getBean('arhframe.FactoryRenderer')->getRenderer(strtolower($pathinfo['extension']));
                $renderer->setFolder($this->folderTemplate);
                $render = $this->zebraForm->render("twig");
                $header .= $render["Form_Header"];
                if ($renderer instanceof TwigRenderer) {
                    $renderer->setAutoescape(false);
                }
                $footer = $render["Form_Footer"];
                $renderer->createRenderer($this->template, $render);
                $text = $renderer->getHtml();
            } else {
                $renderTab = $this->zebraForm->render();
                $header .= $renderTab['header'];
                $text = $renderTab['content'];
            }
            $this->formHtml = $text . $footer . $this->getAjax();
            cache($this, true, true)->set($this->languageManager->getLocalization() . '/header/' . $this->formName, $header);
            cache($this, true, true)->set($this->languageManager->getLocalization() . '/' . $this->formName, $this->formHtml);

        } else {
            $header = cache($this, true, true)->get($this->languageManager->getLocalization() . '/header/' . $this->formName);
        }

        $secure = BeanLoader::getInstance()->getBean('arhframe.secure');
        $this->debugBarManager->addFormCollector($this->name, $end - $start);
        $header .= '<input type="hidden" name="' . $secure->getTokenKey() . '" value="' . $secure->getToken() . '"/>';
        $end = microtime(true);
        return $header . $this->formHtml;
    }

    public function getAjax()
    {
        if (!$this->ajax) {
            return null;
        }
        $text = NeedleManager::loadPluginJquery($this->alajaxFile, 'alajax', '$("#' . $this->formName . '").alajax(' . $this->getAlajaxOption() . ');');

        return $text;
    }

    private function getAlajaxOption()
    {
        if (empty($this->type) && empty($this->error) && empty($this->success) && empty($this->beforeSend)) {
            return null;
        }
        $option = null;
        if (!empty($this->type)) {
            $option[] = "type: '" . $this->type . "'";
        }
        if (!empty($this->error)) {
            $option[] = "error: " . $this->error;
        }
        if (!empty($this->success)) {
            $option[] = "success: " . $this->success;
        }

        if (!empty($this->beforeSend)) {
            $option[] = "beforeSend: " . $this->beforeSend;
        }

        return "{" . implode(", ", $option) . "}";
    }

    public function setAddCssToHeadPage($addCssToHeadPage)
    {
        $this->addCssToHeadPage = $addCssToHeadPage;

        return $this;
    }

    public function getFormName()
    {
        return $this->formName;
    }

    public function setLocalization($localization = null)
    {
        $languageManager = LanguageManager($localization);
        $this->zebraForm->language($languageManager->getLocalization());
    }

    public function validate()
    {
        return $this->zebraForm->validate();
    }

    public function clearSendValue()
    {
        foreach ($this->formStyle as $id => $value) {
            unset($_REQUEST[$id]);
            unset($_POST[$id]);
            unset($_GET[$id]);
        }
        unset($_REQUEST['name_' . $this->formName]);
        unset($_REQUEST['zebra_honeypot_' . $this->formName]);
        unset($_GET['name_' . $this->formName]);
        unset($_GET['zebra_honeypot_' . $this->formName]);
        unset($_POST['name_' . $this->formName]);
        unset($_POST['zebra_honeypot_' . $this->formName]);
    }

    public function getForm()
    {
        return $this->zebraForm;
    }

    public function getFilesUploadInfo()
    {
        return $this->zebraForm->file_upload;
    }
    public function uncache(){
        $this->canCache =false;
        return $this;
    }
    public function __toString()
    {
        try {
            return $this->getHtml();
        } catch (Exception $e) {

            return $e->getTraceAsString();
        }

    }
}
