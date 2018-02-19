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
     *
     */
    public function beforeInsert()
    {

    }

    /**
     *
     */
    public function afterInsert()
    {

    }

    /**
     *
     */
    public function beforeSave()
    {

    }

    /**
     *
     */
    public function afterSave()
    {

    }

    /**
     *
     */
    public function beforeUpdate()
    {

    }

    /**
     *
     */
    public function afterUpdate()
    {

    }

    /**
     *
     */
    public function beforeDelete()
    {

    }

    /**
     *
     */
    public function afterDelete()
    {

    }

}