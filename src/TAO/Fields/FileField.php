<?php

namespace TAO\Fields;

use TAO\Text\StringTemplate;

trait FileField
{
	/**
	 * @var bool
	 */
	protected $tempId = false;

	/**
	 * @param $info
	 * @return string
	 */
	public function destinationFileName($info)
	{
		$cb = $this->param('generate_file_name', false);
		if (is_callable($cb)) {
			return call_user_func($cb, $info);
		}

		$fileNameTemplate = $this->param('file_name_template', $this->defaultFileNameTemplate());
		$binds = $this->fileNameBinds($info);
		return StringTemplate::process($fileNameTemplate, $binds);
	}

	/**
	 * @param $info
	 * @return string
	 */
	public function destinationPath($info)
	{
		$dir = $this->param('private', false) ? $this->item->getPrivateHomeDir() : $this->item->getHomeDir();
		$file = $this->destinationFilename($info);
		return "{$dir}/{$file}";
	}

	/**
	 * @return string
	 */
	public function tempId()
	{
		if (!$this->tempId) {
			$this->tempId = $this->generateTempId();
		}
		return $this->tempId;
	}

	/**
	 * @return string
	 */
	protected function generateTempId()
	{
		return uniqid($this->item->getDatatype() . '_' . $this->name);
	}

	/**
	 * @param $temp_id
	 * @return string
	 */
	public function tempDir($temp_id)
	{
		$session_id = \Session::getId();
		return "session-files/{$session_id}/{$temp_id}";
	}

	/**
	 * @param $file
	 * @param $info
	 * @return bool|mixed
	 */
	public function checkUploadedFile($file, &$info)
	{
		$cb = $this->param('check_uploaded_file', false);
		if (is_callable($cb)) {
			return call_user_func($cb, $file);
		}
		return true;
	}

	/**
	 * @param $size
	 * @return string
	 */
	public function generateHumanSize($size)
	{
		if ($size >= 10485760) {
			return ((int)round($size / 1048576)) . 'M';
		}
		if ($size >= 1048576) {
			return number_format($size / 1048576, 1) . 'M';
		}
		if ($size >= 10240) {
			return ((int)round($size / 1024)) . 'K';
		}
		if ($size >= 1024) {
			return number_format($size / 1024, 1) . 'K';
		}

		return $size . 'B';
	}

	/**
	 * @return string
	 */
	public function uploadUrl()
	{
		return $this->apiUrl('upload', ['_token' => csrf_token(), 'upload_id' => $this->tempId()]);
	}

	/**
	 * @param $info
	 * @return array
	 */
	protected function fileNameBinds($info)
	{
		if (is_array($info)) {
			$info = (object)$info;
		}

		$nameWithoutExt = str_replace(".{$info->ext}", '', $info->name);
		return [
			'ext' => !empty($info->ext) ? strtolower($info->ext) : '',
			'Ext' => !empty($info->ext) ? $info->ext : '',
			'datatype' => $this->item->getDatatype(),
			'field' => $this->name,
			'id' => $this->item->getKey(),
			'filename' => $info->name,
			'translit' => \TAO\Text::process($nameWithoutExt, 'translit_for_url'),
		];
	}

	/**
	 * @return mixed
	 */
	abstract protected function defaultFileNameTemplate();
}