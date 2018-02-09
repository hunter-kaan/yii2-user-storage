<?php
/**
 * Created by PhpStorm.
 * User: zero
 * Date: 09.02.2018
 * Time: 18:09
 */

namespace HunterKaan\UserStorage;

/**
 * Class UserStorageModelInterface
 * @package HunterKaan\UserStorage
 */
interface UserStorageModelInterface
{
	/**
	 * Storage safe attributes.
	 *
	 * @return array
	 */
	public function userStorageAttributes();
}