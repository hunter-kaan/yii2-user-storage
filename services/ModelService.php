<?php

namespace HunterKaan\UserStorage\services;

use HunterKaan\UserStorage\Storage;
use HunterKaan\UserStorage\UserStorageModelInterface;
use yii\base\Model;

/**
 * Class ModelService
 * @package HunterKaan\UserStorage\services
 */
class ModelService
{
	private $_storage;

	private $_model;

	/**
	 * ModelService constructor.
	 *
	 * @param Storage $storage
	 * @param Model $model
	 */
	public function __construct(Storage $storage, Model $model)
	{
		$this->_storage = $storage;
		$this->_model = $model;
	}

	/**
	 * Set user values to model.
	 *
	 * @param string|null $storage_key
	 * @param array $attributes
	 * @param array $except_attributes
	 * @internal param Model $model
	 */
	public function load($storage_key = null, array $attributes = null, array $except_attributes = [])
	{
		if ($this->_storage->getIdentityId()) {
			$storage_key = $this->buildStorageKey($storage_key);

			$userValues = $this->_storage->get($storage_key);
			if ($userValues !== false) {
				$attributes = $this->getStorageAttributes($attributes, $except_attributes);
				$attributes = array_replace($attributes, array_intersect_key($userValues, $attributes));

				$this->_model->setAttributes($attributes, true);
			}
		}
	}

	/**
	 * Store user values from model.
	 *
	 * @param string|null $storage_key
	 * @param array|null $attributes
	 * @param array $except_attributes
	 * @return bool
	 */
	public function save($storage_key = null, array $attributes = null, array $except_attributes = [])
	{
		$storage_key = $this->buildStorageKey($storage_key);

		$attributes = $this->getStorageAttributes($attributes, $except_attributes);
		return $this->_storage->set($storage_key, $attributes);
	}

	/**
	 * Get storage attributes from model.
	 *
	 * @param array|null $attributes
	 * @param array $except_attributes
	 * @return array
	 */
	protected function getStorageAttributes(array $attributes = null, array $except_attributes = [])
	{
		if ($this->_model instanceof UserStorageModelInterface) {
			$userStorageAttributes = $this->_model->userStorageAttributes();

			$attributes = is_null($attributes) ? $userStorageAttributes
				: array_merge($attributes, $this->_model->userStorageAttributes());

			$attributes = array_unique($attributes);
		}
		$attributes = $this->_model->getAttributes($attributes, $except_attributes);

		return $attributes;
	}


	/**
	 * Build model storage key.
	 *
	 * @param null $storage_key
	 * @return string
	 */
	protected function buildStorageKey($storage_key = null)
	{
		if (is_null($storage_key)) {
			$reflector = new \ReflectionClass($this->_model);
			$storage_key = $reflector->getName();
		}
		return $storage_key;
	}
}