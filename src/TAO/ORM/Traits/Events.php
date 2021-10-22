<?php

namespace TAO\ORM\Traits;

use TAO\Callback;

/**
 * Class Events
 */
trait Events
{

	/**
	 * @param string $event
	 * @param bool|true $halt
	 * @return mixed
	 */
	protected function fireModelEvent($event, $halt = true)
	{
		$rc = null;
		switch ($event) {
			case 'saving':
				if (false === $this->beforeSave()) {
					return false;
				}
				$this->immutableBeforeSave();
				break;
			case 'creating':
				if (false === $this->beforeInsert()) {
					return false;
				}
				$this->immutableBeforeInsert();
				break;
			case 'updating':
				if (false === $this->beforeUpdate()) {
					return false;
				}
				$this->immutableBeforeUpdate();
				break;
			case 'deleting':
				if (false === $this->beforeDelete()) {
					return false;
				}
				$this->immutableBeforeDelete();
				break;
			case 'saved':
				$this->afterSave();
				$this->immutableAfterSave();
				break;
			case 'created':
				$this->afterInsert();
				$this->immutableAfterInsert();
				break;
			case 'updated':
				$this->afterUpdate();
				$this->immutableAfterUpdate();
				break;
		}
		return parent::fireModelEvent($event, $halt);
	}

	protected function generateUrl()
	{
	}

	protected function generateUrlAfterSave()
	{
	}

	protected function triggerEventForFields($eventName, $data = [])
	{
		foreach ($this->fieldsObjects() as $field) {
			Callback::instance([$field, $eventName])->call($data);
		}
	}

	/**
	 *
	 */
	final public function immutableBeforeSave()
	{
		$this->triggerEventForFields('beforeItemSave');
		$this->generateUrl();
	}

	/**
	 *
	 */
	final public function immutableAfterSave()
	{
		$this->triggerEventForFields('afterItemSave');
		$this->generateUrlAfterSave();
	}

	/**
	 *
	 */
	final public function immutableBeforeInsert()
	{
		if ($this->idType == 'uuid') {
			$this->{$this->getKeyName()} = (string)$this->generateNewId();
		}
		$this->triggerEventForFields('beforeItemInsert');
	}

	/**
	 *
	 */
	final public function immutableAfterInsert()
	{
		$this->triggerEventForFields('afterItemInsert');
	}

	/**
	 *
	 */
	final public function immutableBeforeUpdate()
	{
		$this->triggerEventForFields('beforeItemUpdate');
	}

	/**
	 *
	 */
	final public function immutableAfterUpdate()
	{
		$this->triggerEventForFields('afterItemUpdate');
	}

	/**
	 *
	 */
	final public function immutableBeforeDelete()
	{
		$this->triggerEventForFields('beforeItemDelete');
	}

	/**
	 *
	 */
	final public function immutableAfterDelete()
	{
		$this->triggerEventForFields('afterItemDelete');
	}

	/**
	 * Действия, выполняемые перед созданием новой записи в БД
	 * Для запрета создания записи метод должен вернуть false
	 *
	 */
	public function beforeInsert()
	{

	}

	/**
	 * Действия, выполняемые после создания новой записи в БД
	 *
	 */
	public function afterInsert()
	{

	}

	/**
	 * Действия выполняемые перед сохранением записи (как при обновлении существующей, так и создании новой)
	 * Для запрета сохранения метод должен вернуть false
	 *
	 */
	public function beforeSave()
	{

	}

	/**
	 * Действия выполняемые осле сохранения записи (как при обновлении существующей, так и создании новой)
	 *
	 */
	public function afterSave()
	{

	}

	/**
	 * Действия, выполняемые перед обновлением существующей записи в БД
	 * Для запрета обновления записи метод должен вернуть false
	 *
	 */
	public function beforeUpdate()
	{

	}

	/**
	 * Действия, выполняемые после обновления существующей записи в БД
	 *
	 */
	public function afterUpdate()
	{

	}

	/**
	 * Действия, выполняемые перед удалением записи
	 * Для запрета удаления записи метод должен вернуть false
	 *
	 */
	public function beforeDelete()
	{

	}

	/**
	 * Действия, выполняемые после удаления записи
	 */
	public function afterDelete()
	{

	}

}
