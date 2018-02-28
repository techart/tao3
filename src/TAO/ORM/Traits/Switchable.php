<?php

namespace TAO\ORM\Traits;

/**
 * Trait Switchable
 *
 * @property bool $isactive
 */
trait Switchable
{
	public function initExtraSwitchable()
	{
		$this->extraFields = \TAO::merge($this->extraFields, [
			'isactive' => array(
				'type' => 'checkbox index',
				'label' => 'Включено к показу',
				'label_in_admin_list' => 'Вкл',
				'default' => 1,
				'weight' => -900,
				'in_list' => true,
				'in_form' => true,
				'group' => 'common',
				'admin_th_attrs' => 'style="width: 30px;text-align: center;"',
				'admin_td_attrs' => 'style="text-align: center;"',
				'before_tree_title' => true,
				'render_in_list' => function ($field) {
					return (bool)$field->value() ? '<img src="/tao/images/accept.png"" width="16" height="16">' : '&nbsp;';
				}
			),
		]);
	}
}