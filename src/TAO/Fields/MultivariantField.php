<?php

namespace TAO\Fields;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use TAO\Callback;
use TAO\Foundation\Request;
use TAO\ORM\Model;
use TAO\Schema\Index\Builder;
use TAO\Type;

/**
 *
 * Абстрактный класс мультивариантного филда
 *
 * Class MultuvariantField
 * @package TAO\Fields
 */
abstract class MultivariantField extends Field
{
	protected $variant = false;

	/**
	 * Возвращает список вариантов для данного поля или false если оно не мультивариантное
	 *
	 * @return array|bool
	 */
	public function variants()
	{
		if ($this->param('variants')) {
			return \TAO::getVariants();
		}
		return false;
	}

	/**
	 * @return array|bool
	 */
	public function variantsWithDefault()
	{
		$variants = $this->variants();
		if (!$variants) {
			$variants = array(
				'default' => array(
					'postfix' => '',
				)
			);
		}
		return $variants;
	}

	/**
	 * @param $code
	 * @return mixed|string
	 */
	public function variantValue($code)
	{
		$variants = $this->variantsWithDefault();
		if (!isset($variants[$code])) {
			return $this->nullValue();
		}
		$column = $this->name . $variants[$code]['postfix'];
		return $this->item[$column];
	}

	/**
	 * @param Blueprint $table
	 * @return $this
	 */
	public function checkSchema(Blueprint $table)
	{
		if ($this->variants()) {
			return $this->checkVariantsSchema($table);
		}

		return parent::checkSchema($table);
	}

	/**
	 * @param Blueprint $table
	 * @return $this
	 */
	public function checkVariantsSchema(Blueprint $table)
	{
		foreach ($this->variants() as $code => $data) {
			$column = $this->name . $data['postfix'];

			if (!$this->item->hasColumn($column)) {
				$this->createField($table, $column);
			} else {
				$f = $this->createField($table, $column);
				if ($f) {
					$f->change();
				}
			}
		}
		return $this;
	}

	/**
	 * @param $code
	 * @param $value
	 */
	public function setForVariant($code, $value)
	{
		$variants = $this->variantsWithDefault();
		if (isset($variants[$code])) {
			$this->item[$this->name . $variants[$code]['postfix']] = $value;
		}
	}

	/**
	 * @param Request $request
	 * @return $this|void
	 */
	public function setFromRequest($request)
	{
		if ($variants = $this->variants()) {
			foreach ($variants as $code => $data) {
				$value = null;
				$column = $this->name . $data['postfix'];
				if ($request->has($column)) {
					$value = $request->input($column);
				}
				$this->setForVariant($code, $value ?? $this->nullValue());
			}
			return $this;
		}
		return parent::setFromRequest($request);
	}

	/**
	 * @return mixed
	 */
	public function rawValue()
	{
		$variants = $this->variants();
		if (!$variants) {
			return parent::rawValue();
		}
		$variants = $this->variantsWithDefault();
		$variant = $this->variant ?: \TAO::getVariant();
		if (!isset($variants[$variant])) {
			$variant = 'default';
		}
		$column = $this->name . $variants[$variant]['postfix'];
		return $this->item[$column];
	}

	/**
	 * @param $code
	 * @return $this
	 */
	public function setVariant($code)
	{
		$this->variant = $code;
		return $this;
	}

	/**
	 * @param Blueprint $table
	 * @return Fluent|void
	 */
	public function createField(Blueprint $table, $column = false)
	{

	}
}
