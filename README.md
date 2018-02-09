Yii2 User storage
=================
Simple storage of user settings, options & etc.

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
            'keyPrefix' => '',
            'userStorageTable' => '{{%user_storage}}',
            'userTable' => '{{%user}}',
        ],
    ]
    //....
];
```

Before you can go on you need to create those tables in the database.
To do this, you can use the migration stored in `@vendor/hunter-kaan/yii2-user-storage/migrations`:

```php
php yii migrate/up --migrationPath=@vendor/hunter-kaan/yii2-user-storage/migrations
```

The component extends the `yii\caching\Cache` class.

A typical usage is like the following:
```php
    // To get default form values.
	$formDefaultValues = $userStorage->get('myForm-default-values');

	// To store user choose as default form values.
	$formDefaultValues = ['city_id' => 3];
	$userStorage->set('myForm-options', $formDefaultValues);
```

Examples
-------------

Store and load model data:

In your controller:
```php
    $model = new Post();
    $model->loadDefaultValues();
    Yii::$app->userStorage->loadUserValues($model);
    // ...
    if ($model->load(Yii::$app->getRequest()->getBodyParam()) && $model->save()) {
        Yii::$app->userStorage->saveUserValues($model);
        // ...
    }
```

The model must implement interface `HunterKaan\UserStorage\UserStorageModelInterface`:
```php
class Post extends ActiveRecord implements UserStorageModelInterface
{
    // ...

	/**
	 * Storage safe attributes.
	 *
	 * @return array
	 */
	public function userStorageAttributes()
	{
		return ['pinned'];
	}
	// ...
```

or you must specify safe attributes when loading(saving):
```php
    Yii::$app->userStorage->loadUserValues($model, null, ['pinned']);
    // ...
    if ($model->load(Yii::$app->getRequest()->getBodyParam()) && $model->save()) {
        Yii::$app->userStorage->saveUserValues($model, null, ['pinned']);
        // ...
    }
```

Note
-------------
* Only secure attributes can be saved. [See models guide.](http://www.yiiframework.com/doc-2.0/guide-structure-models.html#safe-attributes)
* If the model does not implement the `UserStorageModelInterface` interface and when loading(saving) does not specify attributes, all safe attributes will be saved.
* At the moment, only storage in the database is supported.