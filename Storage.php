<?php

namespace HunterKaan\UserStorage;

use HunterKaan\UserStorage\services\ModelService;
use yii\base\Model;
use yii\caching\Cache;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;


/**
 * Class Storage
 * @package HunterKaan\UserStorage
 */
class Storage extends Cache
{
	/**
	 * @var \yii\db\Connection|array|string
	 */
	public $db = 'db';

	/**
	 * Table name for storage user options (only for database method).
	 *
	 * @var string
	 */
	public $userStorageTable = '{{%user_storage}}';

	/**
	 * User table for create fk keys.
	 *
	 * @var string
	 */
	public $userTable = '{{%user}}';

	/**
	 * Identity ID.
	 *
	 * @var
	 */
	private $_identity_id;

	/**
	 * @inheritDoc
	 */
	public function init()
	{
		$this->db = Instance::ensure($this->db, Connection::class);

		if (!is_a(\Yii::$app, 'yii\console\Application')) {
			$this->_identity_id = \Yii::$app->getUser()->getIdentity()->getId();
		}
	}

	/**
	 * @param Model $model
	 * @return ModelService
	 */
	public function buildModelService(Model $model)
	{
		return \Yii::createObject(ModelService::class, [$this, $model]);
	}

	/**
	 * Identity ID getter.
	 *
	 * @return mixed
	 */
	public function getIdentityId()
	{
		return $this->_identity_id;
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This method should be implemented by child classes to retrieve the data
	 * from specific cache storage.
	 * @param string $key a unique key identifying the cached value
	 * @return mixed|false the value stored in cache, false if the value is not in the cache or expired. Most often
	 * value is a string. If you have disabled [[serializer]], it could be something else.
	 */
	protected function getValue($key)
	{
		$result = false;
		if ($this->_identity_id) {
			$query = new Query();
			$result = $query->select(['option_value'])
				->from($this->userStorageTable)
				->where(['identity_id' => $this->_identity_id])
				->andWhere(['or', 'expire_at IS NULL', ['>=', 'expire_at', time()]])
				->scalar($this->db);
		}

		return $result;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This method should be implemented by child classes to store the data
	 * in specific cache storage.
	 * @param string $key the key identifying the value to be cached
	 * @param mixed $value the value to be cached. Most often it's a string. If you have disabled [[serializer]],
	 * it could be something else.
	 * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return bool true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key, $value, $duration)
	{
		$rows_affected = 0;
		if ($this->_identity_id) {
			$id = $this->findIdBy($key);

			$rows_affected = $id ? $this->updateRow($id, $value, $duration) :
				$this->insertRow($key, $value, $duration);
		}

		return $rows_affected > 0;
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This method should be implemented by child classes to store the data
	 * in specific cache storage.
	 * @param string $key the key identifying the value to be cached
	 * @param mixed $value the value to be cached. Most often it's a string. If you have disabled [[serializer]],
	 * it could be something else.
	 * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return bool true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key, $value, $duration)
	{
		$rows_affected = 0;
		if ($this->_identity_id && !$this->findIdBy($key)) {
			$rows_affected = $this->insertRow($key, $value, $duration);
		}
		return $rows_affected > 0;
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This method should be implemented by child classes to delete the data from actual cache storage.
	 * @param string $key the key of the value to be deleted
	 * @return bool if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		if ($this->_identity_id) {
			$this->db->createCommand()
				->delete($this->userStorageTable, [['key' => $key, 'identity_id' => $this->_identity_id]])
				->execute();
		}

		return true;
	}

	/**
	 * Deletes all values from cache.
	 * Child classes may implement this method to realize the flush operation.
	 * @return bool whether the flush operation was successful.
	 */
	protected function flushValues()
	{
		if ($this->_identity_id) {
			$this->db->createCommand()
				->delete($this->userStorageTable, ['identity_id' => $this->_identity_id])
				->execute();
		}

		return true;
	}

	/**
	 * Find row ID by key.
	 *
	 * @param String $key
	 * @return integer|false - return row id or FALSE if key is not present in table.
	 */
	protected function findIdBy($key)
	{
		$result = false;
		if ($this->_identity_id) {
			$query = new Query();
			$result = $query->select(['id'])
				->from($this->userStorageTable)
				->where(['identity_id' => $this->_identity_id])
				->andWhere(['option_key' => $key])
				->scalar($this->db);
		}

		return $result;
	}

	/**
	 * @param $key
	 * @param $value
	 * @param $duration
	 * @return int
	 */
	protected function insertRow($key, $value, $duration)
	{
		/** @noinspection MissedFieldInspection */
		return $this->db->createCommand()
			->insert($this->userStorageTable, [
				'option_key' => $key,
				'option_value' => $value,
				'identity_id' => $this->_identity_id,
				'expire_at' => $this->convertDurationToExpireAt($duration),
			])->execute();
	}

	/**
	 * Convert duration to expire at.
	 *
	 * @param $duration
	 * @return int|null
	 */
	private function convertDurationToExpireAt($duration)
	{
		return $duration > 0 ? time() + $duration : null;
	}

	/**
	 * @param $id
	 * @param $value
	 * @param $duration
	 * @return int
	 */
	protected function updateRow($id, $value, $duration)
	{
		return $this->db->createCommand()
			->update($this->userStorageTable, [
				'option_value' => $value,
				'expire_at' => $this->convertDurationToExpireAt($duration),
			], ['id' => $id])->execute();
	}
}
