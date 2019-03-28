<?php
namespace x51\yii2\modules\resourceCollector;
use \x51\yii2\modules\resourceCollector\Assets;
use \Yii;

class Module extends \yii\base\Module
{
    
    public $cacheDir = 'cache';

    public function init()
    {
        parent::init();
        $view = Yii::$app->view;
        //$view->on($view::EVENT_END_PAGE, [$this, 'onAttachResources']);
        $view->on($view::EVENT_END_BODY, [$this, 'onAttachResources']);
        $view->on($view::EVENT_AFTER_RENDER, [$this, 'onAfterRender']);
        Assets::$module = $this;
        Assets::$cacheDir = $this->cacheDir;
    }

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
} // end class
