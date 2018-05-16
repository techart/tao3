<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\File;
use TAO\Fields\Field;
use TAO\Fields;

/**
 * Class Upload
 * @package TAO\Fields\Type
 */
class Upload extends Field
{
	use Fields\FileField;

	protected $newFile = false;

	/**
	 * @param Blueprint $table
	 * @return \Illuminate\Support\Fluent
	 */
	public function createField(Blueprint $table)
	{
		if ($this->isBase64()) {
			return $table->text($this->name);
		}
		return $table->string($this->name, 250)->default('');
	}

	/**
	 * @param $request
	 */
	public function setFromRequest($request)
	{
		if ($request[$this->name] == 'delete') {
			$this->delete();
			$this->item[$this->name] = '';
		}
	}

	public function isBase64()
	{
		return $this->param('base64', false);
	}

	/**
	 *
	 */
	public function delete()
	{
		$file = trim($this->value());
		if (!empty($file) && \Storage::exists($file)) {
			\Storage::delete($file);
		}
	}

	/**
	 * @param $request
	 */
	public function setFromRequestAfterSave($request)
	{
		$tid = $request[$this->name];
		$path = $this->tempDir($tid);
		if (\Storage::exists("{$path}/file") && \Storage::exists("{$path}/info.json")) {
			$info = json_decode(\Storage::get("{$path}/info.json"));
			$this->delete();
			$filePath = "{$path}/file";

			$dest = $this->setFile($filePath, $info);

			\Storage::delete("{$path}/info.json");
			\Storage::delete($filePath);
			$this->oldValue = $this->item[$this->name];
			$this->item[$this->name] = $dest;
			$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => $dest]);
		}
	}

	public function setFile($path, $info)
	{
		$dest = $this->destinationPath($info);
		if (\Storage::exists($dest)) {
			\Storage::delete($dest);
		}
		if ($this->isBase64()) {
			$content = is_file($path)? file_get_contents($path) : \Storage::get($path);
			$dest = "data:{$info->mime};base64,".base64_encode($content);
		} else {
			if (is_file($path)) {
				list($dir, $file) = $this->destinationDirAndName($info);
				\Storage::putFileAs($dir, new File($path), $file);
			} else {
				\Storage::copy($path, $dest);
			}
		}
		return $dest;
	}

	public function setDefault($value)
	{
		return parent::set($value);
	}

	public function set($path)
	{
		$this->newFile = $path;
	}

	public function processAfterSet()
	{
		if ($this->newFile) {
			$name = $this->newFile;
			$ext = '';
			if ($m = \TAO::regexp('{/([^/]+)$}', $name)) {
				$name = $m[1];
			}
			if ($m = \TAO::regexp('{\.([^.]+)$}', $name)) {
				$ext = $m[1];
			}
			$info = new \StdClass;
			$info->name = $name;
			$info->ext = $ext;
			$dest = $this->setFile($this->newFile, $info);
			$this->newFile = false;
			return $dest;
		}
	}

	public function afterItemSave()
	{
		parent::afterItemSave();
		if ($dest = $this->processAfterSet()) {
			$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => $dest]);
		}
	}

	/**
	 * @return array|bool|mixed
	 */
	public function apiActionUpload()
	{
		$tid = app()->request()->get('upload_id');
		$this->tempId = $tid;
		$dir = $this->tempDir($tid);
		if (!\Storage::exists($dir)) {
			\Storage::makeDirectory($dir);
		}
		$file = app()->request()->file('uploadfile');
		$size = $file->getSize();
		$human_size = $this->generateHumanSize($size);

		$info = array(
			'upload_id' => $tid,
			'name' => $file->getClientOriginalName(),
			'ext' => $file->getClientOriginalExtension(),
			'mime' => $file->getClientMimeType(),
			'size' => $size,
			'human_size' => $human_size,
			'preview' => '',
		);
		$check = $this->checkUploadedFile($file, $info);
		if (is_string($check)) {
			return $check;
		}
		if (is_array($check)) {
			$info = $check;
		}
		\Storage::put("{$dir}/info.json", json_encode($info));
		$file->storeAs($dir, 'file');
		return $info;
	}

	/**
	 * @return int
	 */
	public function size()
	{
		$file = $this->value();
		if (empty($file)) {
			return 0;
		}
		if (starts_with($file, 'data:')) {
			$file = preg_replace('{^.+;base64,}', '', $file);
			return strlen(base64_decode($file));
		}
		return \Storage::size($file);
	}

	/**
	 * @return string
	 */
	public function humanSize()
	{
		return $this->generateHumanSize($this->size());
	}

	/**
	 * @return string
	 */
	protected function defaultFileNameTemplate()
	{
		return '{datatype}-{field}-{id}.{ext}';
	}


	/**
	 * @return bool|string
	 */
	public function url()
	{
		$file = $this->value();
		if (empty($file)) {
			return false;
		}
		if (starts_with($file, 'data:')) {
			return $file;
		}
		return \Storage::url($file);
	}

	/**
	 * @return string
	 */
	public function renderForAdminList()
	{
		$render = $this->callableParam(['render_in_admin_list', 'render_in_list'], null, [$this], $this->item);
		if (is_null($render)) {
			$url = $this->url();
			$value = $this->value();
			$render = "<a href='{$url}'>{$value}</a>";
		}
		return $render;
	}
}
