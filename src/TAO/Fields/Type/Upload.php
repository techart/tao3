<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Storage;
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
			$temp = false;
			if (\TAO::regexp('{^https?://}', $this->newFile)) {
				$dir = 'temp/'.uniqid();
				\Storage::makeDirectory($dir);
				$temp = app('tao.http')->saveFile($this->newFile, $dir);
				$this->newFile = $temp;
			}
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

			if ($temp) {
				\Storage::delete($temp);
				\Storage::deleteDirectory($dir);
			}

			return $dest;
		}
	}

	public function afterItemSave()
	{
		parent::afterItemSave();
		if ($dest = $this->processAfterSet()) {
			$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => $dest]);
			$this->item[$this->name] = $dest;
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
		if (!$this->exists($file)) {
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
	 * @return bool
	 */

	public function exists($file = null)
	{
		if (null === $file) {
			$file = $this->value();
		}
		if (empty($file)) {
			return false;
		}
		if ((!starts_with($file, 'data:')) &&
			(!\Storage::exists($file))) {
			return false;
		}
		return true;
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
		if (!\Storage::exists($file)) {
			return null;
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

	public function dataExportValue()
	{
		$value = $this->value();
		if (starts_with($value, 'data:')) {
			return chunk_split($value);
		}
		$path = $value;
		if (\Storage::exists($path)) {
			$name = preg_replace('{^.*/}', '', $value);
			return ":{$name}\n".chunk_split(base64_encode(\Storage::get($path)));
		}
	}

	public function dataImport($src)
	{
		$name = false;
		$src = trim($src);
		$p = strpos($src, "\n");
		if ($p>0) {
			$line = trim(substr($src, 0,  $p));
			if ($m = \TAO::regexp('{^:(.+)$}', $line)) {
				$name = trim($m[1]);
				$src = trim(substr($src, $p));
			}
		}
		$src = preg_replace('{\s+}sm', '', $src);
		if (starts_with($src, 'data:')) {

		} else {
			if ($name) {
				$ext = 'bin';
				$content = base64_decode($src);
				if ($m = \TAO::regexp('{\.([^.]+)$}', $name)) {
					$ext = $m[1];
				}
				$info = new \StdClass;
				$info->name = $name;
				$info->ext = $ext;
				$dest = $this->destinationPath($info);
				list($dir, $file) = $this->destinationDirAndName($info);
				\Storage::put($dest, $content);
				$this->item[$this->name] = $dest;
			}
		}
	}

	/**@see https://laravel.com/api/5.0/Illuminate/Filesystem/FilesystemAdapter.html
	 * Метод возвращает абсолютный путь до файла на сервере
	 * Использовать на свой страх и риск, корректно работает только с локальным хранилищем
	 * @return bool|string
	 */
	public function getAbsolutePath ()
	{
		$prefixPath = Storage::getDriver()->getAdapter()->getPathPrefix();
		if( $prefixPath ){
			return rtrim($prefixPath, '/').'/'.ltrim($this->value(), '/');

		}
		return false;
	}

	/** @see https://laravel.com/api/5.6/Illuminate/Http/File.html
	 * @return Illuminate\Http\File
	 */
	public function getFileObject ()
	{
		$absolutePath = $this->getAbsolutePath();
		if( $absolutePath ){
			return new File( $absolutePath );
		}
		return false;
	}
}
