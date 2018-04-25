<?php

namespace TAO\ORM\Traits;

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

	/**
	 *
	 */
	final public function immutableBeforeSave()
	{
		$this->generateUrl();
	}

	/**
	 *
	 */
	final public function immutableAfterSave()
	{
	}

	/**
	 *
	 */
	final public function immutableBeforeInsert()
	{
		if ($this->idType == 'uuid') {
			$this->{$this->getKeyName()} = (string)$this->generateNewId();
		}
	}

	/**
	 *
	 */
	final public function immutableAfterInsert()
	{
	}

	/**
	 *
	 */
	final public function immutableBeforeUpdate()
	{
	}

	/**
	 *
	 */
	final public function immutableAfterUpdate()
	{
	}

	/**
	 *
	 */
	final public function immutableBeforeDelete()
	{
	}

	/**
	 *
	 */
	final public function immutableAfterDelete()
	{
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