resourseCollector module for Yii 2
==================================

-   The module allows you to set styles and scripts for each template. To do
    this, set two files with the template name and with the extensions css and /
    or js. In such resources relative links cannot be used.

-   Each type of resource is combined into a file. These files are placed in a
    folder, available for web service. And these resources are connected to the
    page.

-   Compression of other resources into one file is available.

-   Scss files will be converted to css. Customization required.

-   Marking resources as preload is supported
    (https://developer.mozilla.org/ru/docs/Web/HTML/Preloading_content).

\--------------------------------------------------------------------------

-   Модуль позволяет задавать к каждому шаблону стили и скрипты. Для этого
    задаются два файла с именем шаблона и с расширениями css и/или js. В таких
    ресурсах нельзя использовать относительные ссылки.

-   Каждый тип ресурсов объединяется в файл. Эти файлы размещаются в папку,
    доступную для вебсервиса. И эти ресурсы подключаются к странице.

-   Доступно сжатие других ресурсов в один файл.

-   Файлы scss будут преобразованы в css. Необходима настройка.

-   Поддерживается пометка ресусов, как preload
    (https://developer.mozilla.org/ru/docs/Web/HTML/Preloading_content).

\--------------------------------------------------------------------------

Installation
------------

1.  Copy to the folder with modules and connect *autoload.php*

2.  Or use composer: add to the *require* section of the project
    `"quanzo/yii2-resource-collector": "*"` or `composer require
    "quanzo/yii2-resource-collector"`

3.  Add to configuration

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$config = [
    'bootstrap' => [
        'collector',
    ],
    'modules' => [
        'collector' => [
            'class' => 'x51\yii2\modules\resourceCollector\Module',
            'cacheDir' => 'cache', // sets the name of the folder for saving cached resources (optional)
            'optimizeCss' => false, // merge styles files 
            'optimizeJs' => false, // merge script files
            'preload' => [], // or function ():array list of files to preload
            'exclude' => [
                '*ckeditor*'
            ], // or function ():array list of mask for exclude from optimize
            'scssImportPath' => [], // a list of directories in which files scss for @import will occur
            'scssVar' => [], // variables for scss
            'scssFunc' => [], // functions for scss
        ],
    ]
];
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Events
------

no

Methods
-------

no
