<?php

namespace TAO\ORM\Model;

use Illuminate\Notifications\Notifiable;
use TAO\ORM\Abstracts\User as AbstractUser;

class VitaUser extends AbstractUser
{
	use Notifiable;
	
	public $table = 'users';
	
	public function fields()
	{
		$fields = parent::fields();
		unset($fields['roles']);
		unset($fields['social']);
		unset($fields['social_info']);
		$fields = array_merge($fields, array(
			'nomination' => array(
				'type' => 'string(50)',
				'in_list' => false,
				'in_form' => false,
			),
			'office_id' => array(
				'type' => 'integer index',
				'in_list' => false,
				'in_form' => false,
			),
			'techart_dep' => array(
				'type' => 'integer',
				'in_list' => false,
				'in_form' => false,
			),
			'company' => array(
				'type' => 'string(250)',
				'in_list' => false,
				'in_form' => false,
			),
			'agent_id' => array(
				'type' => 'integer',
				'in_list' => false,
				'in_form' => false,
			),
			'groups' => array(
				'type' => 'text',
				'in_list' => false,
				'in_form' => false,
			),
		));
		return $fields;
	}
	
	public function loginUrl()
	{
		return \TAO\Users\VitaAuth::authUrl();
	}
}


