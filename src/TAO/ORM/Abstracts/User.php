<?php

namespace TAO\ORM\Abstracts;

use TAO\ORM\Model as AbstractModel;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use TAO\ORM\Model\Role;

abstract class User extends AbstractModel implements
	AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword;

	public $isBlocked = false;

	public function fields()
	{
		$fields = array(
			'remember_token' => array(
				'type' => 'remember_token',
				'in_list' => false,
				'in_form' => false,
			),
			'is_admin' => array(
				'type' => 'checkbox',
				'label' => 'Супер-администратор',
				'in_list' => false,
				'in_form' => true,
				'group' => 'access',
			),
			'is_secondary_admin' => array(
				'type' => 'checkbox',
				'label' => 'Администратор',
				'in_list' => false,
				'in_form' => true,
				'group' => 'access',
			),
			'access_realm_admin' => array(
				'type' => 'checkbox',
				'label' => 'Редактор',
				'in_list' => false,
				'in_form' => true,
				'group' => 'access',
			),
			'name' => array(
				'type' => 'string(150)',
				'label' => 'Имя',
				'in_list' => true,
				'weight_in_list' => 100,
				'in_form' => true,
				'style' => 'width: 90%',
				'group' => 'common',
			),
			'email' => array(
				'type' => 'string(250) index',
				'label' => 'E-Mail',
				'in_list' => true,
				'weight_in_list' => 200,
				'in_form' => true,
				'style' => 'width: 250px',
				'group' => 'common',
			),
			'password' => array(
				'type' => 'password',
				'label' => 'Хеш пароля',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common',
			),
			'roles' => array(
				'type' => 'multilink',
				'model' => Role::class,
				'label' => false,
				'in_list' => false,
				'in_form' => true,
				'group' => 'access.roles',
			),
			'social' => array(
				'type' => 'string(20)',
				'in_list' => false,
				'in_form' => false,
			),
			'social_info' => array(
				'type' => 'text',
				'in_list' => false,
				'in_form' => false,
			),
		);
		return $fields;
	}

	public function filter()
	{
		return array(
			'search' => array(
				'type' => 'string',
				'label' => 'По имени/адресу',
			),
		);
	}

	public function applyFilterSearch($builder, $value)
	{
		$value = "%{$value}%";
		return $builder->where(\DB::raw('CONCAT_WS(" ", name, email)'), 'like', $value);
	}

	public function accessEdit($user = false)
	{
		if (!$user) {
			$user = \Auth::user();
		}
		// Только супер-админы могут админить юзеров
		return $user['is_admin'];
	}

	/**
	 *
	 * Настройка нового пользователя, добавленного механизмом внешней авторизации. До записи.
	 * По умолчанию считается, что по внешей авторизации приходять только супер-админы.
	 * Переопределите класс пользователя, чтобы это изменить
	 *
	 * @param array $data
	 */
	public function setupAfterExtraAuth($data = [])
	{
		$this->field('is_admin')->set(1);
	}

	/**
	 *
	 * Настройка нового пользователя, добавленного механизмом внешней авторизации. После записи записи.
	 *
	 * @param array $data
	 */
	public function setupAfterExtraAuth2($data = [])
	{
	}

	public function setupAfterSocialAuth($data)
	{
	}

	public function setupAfterSocialAuth2($data)
	{
	}

	/**
	 *
	 * Имеет ли данный пользователь право на вход в указанный реалм.
	 * Админы могут войти в любой реалм.
	 * По умолчанию есть только один админский реалм. Если нужны другие, то добавьте access_realm_<name> поле в переопределенном классе
	 *
	 * @param $name
	 * @return bool|int
	 */
	public function accessToRealm($name)
	{
		if ($this['is_admin'] || $this['is_secondary_admin']) {
			return true;
		}
		return (int)$this["access_realm_{$name}"];
	}

	public function checkAccessGroup($group)
	{
		$group = trim($group);
		if (!empty($group)) {
			if ($role = \TAO::datatype('roles')->findByCode($group)) {
				if ($this->field('roles')->isAttached($role->id)) {
					return true;
				}
			}
		}
		return false;
	}

	public function checkAccess($groups)
	{
		if ($this['is_admin'] || $this['is_secondary_admin']) {
			return true;
		}
		foreach (explode(',', $groups) as $group) {
			if ($this->checkAccessGroup($group)) {
				return true;
			}
		}
		return false;
	}


	public function adminFormGroups()
	{
		return array(
			'common' => 'Основные параметры',
			'access' => 'Управление доступом',
			'access.roles' => 'Состоит в группах',
		);
	}

	public function adminMenuSection()
	{
		return false;
	}

	public function adminTitleList()
	{
		return 'Зарегистрированные пользователи';
	}

	public function adminTitleEdit()
	{
		return 'Редактирование пользователя';
	}

	public function adminAddButtonText()
	{
		return 'Создать';
	}
}
