resourseCollector модуль для Yii 2
==================================

Модуль позволяет задавать к каждому шаблону стили и скрипты. Для этого задаются
два файла с именем шаблона и с расширениями css и js.

Каждый тип ресурсов объединяется в файл. Эти файлы размещаются в папку,
доступную для вебсервиса. И эти ресурсы подключаются к странице.

В ресурсах нельзя использовать относительные ссылки.

Установка и подключение
-----------------------

1.  Скопировать в папку с модулями

2.  Подключить *autoload.php*

3.  Подключить в конфигурации

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$config = [
    'bootstrap' => [
        'collector',
    ],
    'modules' => [
        'collector' => [ // сборщик ресурсов для шаблонов
            'class' => 'x51\yii2\modules\resourceCollector\Module',
            'cacheDir' => 'cache', // задает имя папки для сохранения кешированных ресурсов (не обязательно)
        ],
    ]
];
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

События
-------

нет

Методы
------

нет
