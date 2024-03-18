<?php

namespace TAO\Fields\Type;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class DateInteger extends Field
{
	/**
	 * @param Blueprint $table
	 * @return mixed
	 */
	public function createField(Blueprint $table)
	{
		return $table->integer($this->name, false, true)->default(0);
	}

	/**
	 * @return int
	 */
	public function defaultValue()
	{
		return 0;
	}

	/**
	 * @return int
	 */
	public function nullValue()
	{
		return 0;
	}

	/**
	 * @return null
	 */
	public function withTime()
	{
		return $this->param('with_time', false);
	}

	/**
	 * @return bool|null
	 */
	public function withSeconds()
	{
		if (!$this->withTime()) {
			return false;
		}
		return $this->param('with_seconds', false);
	}

	/**
	 * @return null|string
	 */
	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		return empty($style) ? 'width:200px' : $style;
	}

	/**
	 * @return array
	 */
	protected function defaultContext()
	{
		$context = parent::defaultContext();
		$context['format'] = $this->generateFormat();
		$context['with_datetimepicker'] = $this->param('with_datepicker', true);
		$context['datetimepicker_options'] = array(
			'format' => $this->datetimepickerFormat(),
			'locale' => 'ru',
			'tooltips' => array(
				'today' => 'Сегодня',
				'clear' => 'Очистить',
				'close' => 'Закрыть',
				'selectMonth' => 'Выбрать месяц',
				'prevMonth' => 'Предыдущий месяц',
				'nextMonth' => 'Следующий месяц',
				'selectYear' => 'Выбрать год',
				'prevYear' => 'Предыдущий год',
				'nextYear' => 'Следующий год',
				'selectDecade' => 'Выбрать десятилетие',
				'prevDecade' => 'Предыдущее десятилетие',
				'nextDecade' => 'Следующее десятилетие',
				'prevCentury' => 'Предыдущее столетие',
				'nextCentury' => 'Следующее столетие',
				'pickHour' => 'Выбрать часы',
				'incrementHour' => 'Добавить час',
				'decrementHour' => 'Отнять час',
				'pickMinute' => 'Выбрать минуты',
				'incrementMinute' => 'Добавить минуту',
				'decrementMinute' => 'Отнять минуту',
				'pickSecond' => 'Выбрать секунды',
				'incrementSecond' => 'Добавить секунду',
				'decrementSecond' => 'Отнять секунду',
				'togglePeriod' => 'Переключить период',
				'selectTime' => 'Выбрать время',
			),
		);
		return $context;
	}

	/**
	 * @return string
	 */
	protected function defaultTemplate()
	{
		return 'fields ~ date_integer.output';
	}

	/**
	 * @return string
	 */
	public function generateFormat()
	{
		$format = 'd.m.Y';
		if ($this->withTime()) {
			$format .= ' - H:i';
			if ($this->withSeconds()) {
				$format .= ':s';
			}
		}
		return $format;
	}

	/**
	 * @return string
	 */
	public function datetimepickerFormat()
	{
		if ($this->withTime()) {
			if ($this->withSeconds()) {
				return 'DD.MM.YYYY - HH:mm:ss';
			}
			return 'DD.MM.YYYY - HH:mm';
		}
		return 'DD.MM.YYYY';
	}

	/**
	 * @param $value
	 */
	public function set($value)
	{
		if (trim($value) == '') {
			$value = 0;
		} elseif (is_string($value)) {
			try {
				$dateTimeTimestamp = app('tao.utils')->dateTime($value, true)->getTimestamp();
			} catch (\Exception $e) {
				$this->data['fieldError'] = $e->getMessage();
				$dateTimeTimestamp = $value;
			}
			$value = $dateTimeTimestamp;
		}

		if ($this->isStorable()) {
			$this->item[$this->name] = $value;
		} else {
			$this->value = $value;
		}
	}

	public function carbon()
	{
		return app('tao.utils')->carbon($this->value());
	}

	public function dataExportValue()
	{
		return $this->value();
	}

	public function dataImport($src)
	{
		$this->set($src);
	}
}
