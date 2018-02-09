<?php

use HunterKaan\UserStorage\Storage;
use yii\base\InvalidConfigException;
use yii\db\Migration;

/**
 * Class m180209_093808_create_user_storage_table
 *
 * Handles the creation of table `user_storage`.
 */
class m180209_093808_create_user_storage_table extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$userStorage = $this->getUserStorage();
		$db = $userStorage->db;

		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($userStorage->userStorageTable, [
			'id' => $this->primaryKey(),
			'option_key' => $this->string(64)->notNull(),
			'option_value' => $this->text(),
			'identity_id' => $this->integer()->notNull(),
			'expire_at' => $this->integer()->null(),
		], $tableOptions);

		$this->createIndex('idx_' . $db->tablePrefix . 'user_storage_unique', $userStorage->userStorageTable,
			['option_key', 'identity_id'], true);

		$this->addForeignKey('fk_' . $db->tablePrefix . 'user_options_table_x_identity',
			$userStorage->userStorageTable, 'identity_id',
			$userStorage->userTable, 'id', 'CASCADE', 'CASCADE');
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$userStorage = $this->getUserStorage();
		$db = $userStorage->db;

		$this->dropForeignKey('fk_' . $db->tablePrefix . 'user_options_table_x_identity',
			$userStorage->userStorageTable);

		$this->dropTable($userStorage->userStorageTable);

		return null;
	}


	/**
	 * Get user storage component.
	 *
	 * @throws yii\base\InvalidConfigException
	 * @return HunterKaan\UserStorage\Storage
	 */
	protected function getUserStorage()
	{
		$userStorage = Yii::$app->get('userStorage', false);
		if (!$userStorage instanceof Storage) {
			throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
		}

		return $userStorage;
	}
}
