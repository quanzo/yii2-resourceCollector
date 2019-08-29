<?php

namespace x51\yii2\modules\resourceCollector;
use \x51\yii2\modules\resourceCollector\Assets;
use \x51\classes\frontend\Bender;

use \Yii;

class Module extends \yii\base\Module
{
    public $cacheDir = 'cache';
    
	public $optimizeTTL = -1;
    public $optimizeCss = false;
    public $optimizeJs = false;
    public $makePreload = false;
    public $preload = [];

    protected $_arScssImportPath = [];
    protected $_scssVariables = '';
    protected $_scssFunctions = '';

    public function init()
    {
        parent::init();
        $view = Yii::$app->view;
        //$view->on($view::EVENT_END_PAGE, [$this, 'onAttachResources']);
        if ($this->optimizeCss || $this->optimizeJs) {
            $view->on($view::EVENT_END_PAGE, [$this, 'onOptimizeBender']);
        }
        $view->on($view::EVENT_END_BODY, [$this, 'onAttachResources']);
        $view->on($view::EVENT_AFTER_RENDER, [$this, 'onAfterRender']);
        Assets::$module = $this;
        Assets::$cacheDir = $this->cacheDir;
    }

    /**
     * Undocumented function
     *
     * @param [type] $event
     * @return void
     */
    public function onOptimizeBender($event) {
        if ($this->optimizeCss || $this->optimizeJs) {
            Yii::beginProfile('resourse-collector-optimize');
			$view = Yii::$app->view;            
            // выберем ресурсы для оптимизации
            $arCss = [];
            $arJs = [];
            $bender = new Bender($this->_arScssImportPath, intval($this->optimizeTTL));
            if ($this->_scssFunctions) {
                $bender->functionsConfig = $this->_scssFunctions;
            }
            if ($this->_scssVariables) {
                $bender->variablesConfig = $this->_scssVariables;
            }
            if ($this->optimizeJs && $view->jsFiles) {
                foreach ($view->jsFiles as $position => &$arFiles) {
                    $arSelFiles = $this->chooseResources($arFiles);
                    $fn = $this->relPathCacheFile($arSelFiles, 'js');
                    $tag = $bender->output($fn, array_keys($arSelFiles));
                    $view->jsFiles[$position][$fn] = $tag;
                    if ($this->makePreload) {
                        $this->makePreloadFile($fn);
                    }
                }
            }
            if ($this->optimizeCss && $view->cssFiles) {
                $arSelFiles = $this->chooseResources($view->cssFiles);
                $fn = $this->relPathCacheFile($arSelFiles, 'css');
                $tag = $bender->output($fn, array_keys($arSelFiles));
                $view->cssFiles = [$fn => $tag] + $view->cssFiles;
                if ($this->makePreload) {
                    $this->makePreloadFile($fn);
                }
            }
            
			Yii::endProfile('resourse-collector-optimize');
			/*$stat = [];
			$bender->getCacheStat(Yii::getAlias('@webroot').'/'.$this->cacheDir, $stat);
			Yii::debug(print_r($stat, true));*/
        }
        // preload
        if ($this->preload) {
            if ($arPreload = $this->getListFromProp('preload')) {
                foreach ($arPreload as $fn) {
                    $this->makePreloadFile($fn);
                }
            }
        }
		
    } // end onOptimizeBender

    /**
     * Подключить ресурсы, собранные сборщиком
     *
     * @param \yii\web\Event $event
     * @return void
     */
    public function onAttachResources($event)
    {
        Yii::$app->view->registerAssetBundle('\x51\yii2\modules\resourceCollector\Assets');
    } // end onAttachResources

    /**
     * Соберем ресурсы шаблона
     *
     * @param \yii\base\ViewEvent $event
     * @return void
     */
    public function onAfterRender($event)
    {
        $arPath = pathinfo($event->viewFile);
        foreach (['css', 'js'] as $ext) {
            $f = $arPath['dirname'] . '/' . $arPath['filename'] . '.' . $ext;
            Assets::add($f);
        }
    }

    public function setScssImportPath(array $importPath) {
        $this->_arScssImportPath = $importPath;
    }

    public function setScssVar(array $vars) {
        $this->_scssVariables = $importPath;
    }

    public function setScssFunc(array $funcs) {
        $this->_scssFunctions = $funcs;
    }

    protected function getListFromProp($propName) {
        $arRes = [];
        if (!empty($this->$propName)) {            
            if (is_callable($this->$propName)) {
                $f = $this->$propName;
                $res = $f();
                if (is_array($res)) {
                    $arRes = $res;
                } else {
                    $arRes[] = $res;
                }
            } else {
                $arRes = $this->$propName;
            }
        }
        return $arRes;
    }

    protected function makePreloadFile($f) {
        $headers = Yii::$app->response->headers;
        // check type        
        $q = strpos($f, '?');
        if ($q !== false) {
            $ext = strtolower(pathinfo(substr($f, 0, $q), PATHINFO_EXTENSION));
        } else {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        }
        if (!empty($ext)) {
            $type = ($ext == 'css' ? 'style' : ($ext == 'js' ? 'script' : ''));
            if ($type) {
                Yii::$app->response->headers->add('Link', '<'.$f.'>; rel=preload; as='.$type);
                Yii::$app->view->registerLinkTag([
                    'rel' => 'preload',
                    'href' => $f,
                    'as' => $type
                ]);
            }
        }
    } // end preloadFile

    protected function chooseResources(array &$ar) {
        $res = [];
        if ($ar) {
            foreach ($ar as $fn => &$html) {
                if (is_array($html)) {
                    $res = array_merge($res, $this->chooseResources($html));
                } else {
                    if (strpos($html, ' media=') === false && strpos($fn, '/'.$this->cacheDir) !== 0 && strpos($fn, 'http') === false && strpos($fn, '.php') === false && strpos($fn, '?') === false) {
                        $res[$fn] = $html;
                        unset($ar[$fn]);
                    }
                }
            }
        }
        return $res;
    }

    protected function relPathCacheFile(array $arFiles, $ext) {
        ksort($arFiles);
        $key = md5(json_encode($arFiles));
        $subDir = substr($key, 0, 4);
        $relPath = '/'.$this->cacheDir.'/'.$subDir;
        $absPath = Yii::getAlias('@webroot'.$relPath);
        if (!is_dir($absPath)) {
            if (!@mkdir($absPath, 0755, true)) {
                Yii::debug('Error create dir '.$absPath);
                $relPath = $relPath = '/'.$this->cacheDir;
            }            
        }
        return $relPath.'/'.$key.'.'.$ext;
    }

} // end class
