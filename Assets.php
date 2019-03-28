<?php
namespace x51\yii2\modules\resourceCollector;

class Assets extends \yii\web\AssetBundle
{
    public $baseUrl = '/';

    /* накопление css и js.
    вместе с шаблоном могут быть расположены 2 файла с именем шаблона и расширением css или js
     */
    protected static $arCssFiles = array();
    protected static $arJsFiles = array();
    
    public static $module = null;
    public static $cacheDir = 'cache';

    public static function add($f)
    {
        $r = is_readable($f) && file_exists($f);
        if (!$r) {
            $f = $_SERVER['DOCUMENT_ROOT'] . $f;
            $r = is_readable($f) && file_exists($f);
        }
        if ($r) {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            switch ($ext) {
                case 'js':{
                        static::$arJsFiles[$f] = 'js';
                        return 'js';
                        break;
                    }
                case 'css':{
                        static::$arCssFiles[$f] = 'css';
                        return 'css';
                        break;
                    }
            }
        }
    } // end add

    protected static function resourcesHash($arRes)
    {
        $str = '';
        foreach ($arRes as $f => $type) {
            $str .= $f . ' ';
        }
        return md5($str);
    } // resourcesHash

    protected static function outputFiles()
    {
        $out = [];
        $dirCache = $_SERVER['DOCUMENT_ROOT'] . '/'.static::$cacheDir.'/';

        if (!is_dir($dirCache)) {
            @mkdir($dirCache, 0754, true);
        }

        if (is_writable($dirCache)) {
            if (static::$arCssFiles) {
                //ksort($this->arCssFiles);
                $fn = $dirCache . static::resourcesHash(static::$arCssFiles) . '.css';
                static::outputResourcesToFile(static::$arCssFiles, $fn);
                if (file_exists($fn)) {
                    $out['css'] = $fn;
                }
            }
            if (static::$arJsFiles) {
                //ksort($this->arJsFiles);
                $fn = $dirCache . static::resourcesHash(static::$arJsFiles) . '.js';
                static::outputResourcesToFile(static::$arJsFiles, $fn);
                if (file_exists($fn)) {
                    $out['js'] = $fn;
                }
            }
        }
        return $out;
    }

    protected static function outputResourcesToFile(&$arRes, $fn)
    {
        if (!file_exists($fn)) {
            foreach ($arRes as $f => $type) {
                file_put_contents($fn, "/*** " . $f . " ***/\n" . file_get_contents($f), FILE_APPEND);
            }
        }
    } // end outputResourcesToFile

    public function init()
    {
        $this->basePath = $_SERVER['DOCUMENT_ROOT'];
        //$this->baseUrl = $collector->getCacheDir();
        $arFiles = static::outputFiles();
        foreach ($arFiles as $type => $fn) {
            $relFn = $this->relativePath(
                $fn,
                $this->basePath
            );

            switch ($type) {
                case 'css':{
                        $this->css[] = $relFn . '?v=' . filemtime($fn);
                        break;
                    }
                case 'js':{
                        $this->js[] = $relFn . '?v=' . filemtime($fn);
                        break;
                    }
            }
        }
        parent::init();
    }

    /**
     * возвращает путь относительно $relDir или false, если $fullPath не может быть задан относительно $relDir
     *
     * @param [type] $fullPath
     * @param [type] $relDir
     * @return void
     */
    public function relativePath($fullPath, $relDir)
    {
        if (!$fullPath || !$relDir) {
            return false;
        }
        if (substr($relDir, strlen($relDir) - 1, 1) != '/') {
            $relDir .= '/';
        }

        if (strpos($fullPath, $relDir) === 0) {
            return '/' . substr($fullPath, strlen($relDir));
        }
        return false;
    } // end relativePath

} // end class