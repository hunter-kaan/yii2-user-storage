Yii2 User storage
=================
simple user storage

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist hunter-kaan/yii2-user-storage "*"
```

or add

```
"hunter-kaan/yii2-user-storage": "*"
```

to the require section of your `composer.json` file.


Configuration
-------------

To use this extension, you have to configure the `Storage` class in your application configuration:

```php
return [
    //....
    'components' => [
        'userStorage' => [
            'class' => HunterKaan\UserStorage\Storage::class,
        ],
    ]
    //....
];
```