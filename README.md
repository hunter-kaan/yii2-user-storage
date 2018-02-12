Yii2 User storage
=================
Simple storage of user settings, options & etc.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist hunter-kaan/yii2-user-storage "^1.0"
```

or add

```
"hunter-kaan/yii2-user-storage": "^1.0"
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

```
php yii migrate/up --migrationPath=@vendor/hunter-kaan/yii2-user-storage/migrations
```

The component extends the `yii\caching\Cache` class.

A typical usage is like the following:
```php
    // To get default form values.
	$formDefaultValues = Yii::$app->userStorage->get('myForm-default-values');

	// To store user choose as default form values.
	$formDefaultValues = ['city_id' => 3];
	Yii::$app->userStorage->set('myForm-options', $formDefaultValues);
```

Examples
-------------

### Model service

Store and load model data:

In your controller:
```php
    $model = new Post();
    $model->loadDefaultValues();
    
    // Create model service
    $storageService = Yii::$app->userStorage->buildModelService($model);
    
    // Load data from user storage to model
    $storageService->load();

    // ...
    if ($model->load(Yii::$app->getRequest()->getBodyParam()) && $model->save()) {
        // Save user data to storage
        $storageService->save();
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
    // Create model service
    $storageService = Yii::$app->userStorage->buildModelService($model);
    
    // Load data from user storage to model
    $storageService->load(null, ['pinned']);
    // ...
    if ($model->load(Yii::$app->getRequest()->getBodyParam()) && $model->save()) {
        // Save user data to storage
        $storageService->save(null, ['pinned']);
        // ...
    }
```

By default model storage key is `YourModel::class`, but it can be redefined:
```php
    // Create model service
    $storageService = Yii::$app->userStorage->buildModelService($model);
    
    // Load data from user storage to model
    $storageService->load(CommonModel::class);
    // ...
    if ($model->load(Yii::$app->getRequest()->getBodyParam()) && $model->save()) {
        // Save user data to storage
        $storageService->save(CommonModel::class);
        // ...
    }
```

Note
-------------
* Only secure attributes can be saved. [See models guide.](http://www.yiiframework.com/doc-2.0/guide-structure-models.html#safe-attributes)
* If the model does not implement the `UserStorageModelInterface` interface and when loading(saving) does not specify attributes, all safe attributes will be saved.
* At the moment, only storage in the database is supported.