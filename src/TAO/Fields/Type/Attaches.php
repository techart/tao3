<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Callback;
use TAO\Fields\Field;
use TAO\Fields\FileField;
use TAO\Fields\Type\Attaches\Entry;

class Attaches extends StringField implements \IteratorAggregate
{
	use FileField;

	/**
	 * @param Blueprint $table
	 * @param bool $column
	 * @return \Illuminate\Support\Fluent
	 */
	public function createField(Blueprint $table, $column = false)
	{
		$column = $column? $column : $this->name;
		return $table->text($column);
	}

	/**
	 * @return string
	 */
	protected function defaultFileNameTemplate()
	{
		return '{translit}.{ext}';
	}

	/**
	 * @return array
	 */
	public function defaultInfo()
	{
		$defs = [];
		foreach ($this->infoFields() as $name => $data) {
			$defs[$name] = $data['default'];
		}
		return $defs;
	}
	
	protected function createEntry($data)
	{
		return new Entry($data);
	}
	
	/**
	 * @param bool $raw
	 * @return array
	 */
	public function value($raw = false)
	{
		$defs = $this->defaultInfo();
		$value = unserialize(parent::value());
		$out = [];
		if (is_array($value)) {
			foreach ($value as $key => $data) {
				$path = $data['path'];
				if (\Storage::exists($path)) {
					$data['key'] = $key;
					$data['new'] = false;
					$data['url'] = \Storage::url($data['path']);
					if (!isset($data['info'])) {
						$data['info'] = $defs;
					}
					if ($raw) {
						$out[$key] = $data;
					} else {
						$out[$key] = $this->createEntry($data);
					}
				}
			}
		}
		return $out;
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->value());
	}

	/**
	 * @return string
	 */
	public function renderFilelistJSON()
	{
		return json_encode((object)$this->value(true));
	}

	/**
	 * @return null
	 */
	public function isSortable()
	{
		return $this->param('sortable', false);
	}

	/**
	 * @return null
	 */
	public function infoFieldsSrc()
	{
		return $this->param('info', []);
	}

	/**
	 * @return array
	 */
	public function infoFields()
	{
		$out = [];
		foreach ($this->infoFieldsSrc() as $name => $data) {
			if (is_array($data)) {
				$out[$name] = $data;
			} else {
				$label = $data;
				$type = 'string';
				if ($m = \TAO::regexp('{^(.+)\s+(.+)$}', $name)) {
					$type = strtolower(trim($m[1]));
					$name = trim($m[2]);
				}
				$out[$name] = [
					'type' => $type,
					'name' => $name,
					'label' => $label,
				];
			}
			$type = isset($out[$name]['type']) ? $out[$name]['type'] : 'string';
			$default = isset($out[$name]['default']) ? $out[$name]['default'] : null;
			if (is_null($default)) {
				if ($type == 'date') {
					$default = date('d.m.Y');
				} elseif ($type == 'checkbox') {
					$default = 0;
				} else {
					$default = '';
				}
			}
			$out[$name]['type'] = $type;
			$out[$name]['default'] = $default;
		}
		return $out;
	}

	/**
	 * @return bool
	 */
	public function withInfo()
	{
		$fields = $this->infoFields();
		return !empty($fields);
	}

	/**
	 * @param $name
	 * @return string
	 */
	public function infoFieldId($name)
	{
		return "ifield_{$this->name}_{$name}";
	}

	/**
	 * @return string
	 */
	public function templateJS()
	{
		return 'js';
	}

	/**
	 * @return string
	 */
	public function templateEditInfoJS()
	{
		return 'js-info';
	}

	/**
	 * @return string
	 */
	public function templateEntryJS()
	{
		return 'js-entry';
	}

	/**
	 * @return string
	 */
	public function templateFilelistJS()
	{
		return 'js-filelist';
	}

	/**
	 * @return string
	 */
	public function filelistClass()
	{
		return 'tao-fields-attaches-filelist';
	}

	/**
	 * @return array
	 */
	public function extraCSS()
	{
		return [];
	}

	/**
	 * @return array
	 */
	public function extraJS()
	{
		return [];
	}

	/**
	 * @param $request
	 */
	public function setFromRequestAfterSave($request)
	{
		$out = [];
		$files = (array)json_decode($request[$this->name]);
		$dir = $this->param('private', false) ? $this->item->getPrivateHomeDir() : $this->item->getHomeDir();
		$dir = "{$dir}/{$this->name}";
		$exists = [];
		foreach ($files as $key => $data) {
			$data = (array)$data;
			if (isset($data['name']) && isset($data['path'])) {
				$name = $data['name'];
				$path = $data['path'];
				$new = isset($data['new']) ? $data['new'] : false;
				
				if ($new) {
					$newPath = "{$dir}/{$name}";
					if (\Storage::exists($newPath)) {
						\Storage::delete($newPath);
					}
					\Storage::copy($path, $newPath);
					\Storage::delete($path);
					$data['path'] = $newPath;
				}
				
				unset($data['url']);
				unset($data['new']);
				unset($data['error']);
				unset($data['key']);
				
				$this->checkWidthAndHeight($data);
				
				$exists[$name] = $name;
				
				$out[$key] = $data;
			}
		}

		foreach (\Storage::files($dir) as $file) {
			$filename = basename($file);
			if (!isset($exists[$filename])) {
				\Storage::delete($file);
			}
		}

		$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => serialize($out)]);
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
		$files = app()->request()->file('uploadfile');
		if (!is_array($files)) {
			$files = array($files);
		}

		$returnInfo = array();
		foreach ($files as $file) {
			$returnInfo['files'][] = $this->uploadFile($file, $tid, $dir);
		}

		return $returnInfo;
	}

	/**
	 * @param $file
	 * @param $tid
	 * @param $dir
	 * @return array|bool
	 */
	protected function uploadFile($file, $tid, $dir)
	{
		$size = $file->getSize();
		$human_size = $this->generateHumanSize($size);

		$info = array(
			'upload_id' => $tid,
			'name' => $file->getClientOriginalName(),
			'ext' => $file->getClientOriginalExtension(),
			'mime' => $file->getClientMimeType(),
			'size' => $size,
			'human_size' => $human_size,
			'new' => true,
			'preview' => '',
		);
		$check = $this->checkUploadedFile($file, $info);
		if (is_string($check)) {
			return $check;
		}
		if (is_array($check)) {
			$info = $check;
		}
		$name = (string)$this->destinationFileName($info);
		$dir = rtrim($dir, '/');
		$path = "{$dir}/{$name}";
		$file->storeAs($dir, $name);

		$key = 'f' . md5($path);

		return [
			'path' => $path,
			'name' => basename($path),
			'url' => false,
			'new' => true,
			'key' => $key,
			'info' => $this->defaultInfo(),
		];
	}

	/**
	 * @return mixed|null
	 */
	public function renderableEntries()
	{
		return $this->callParam('renderable_entries', function () {
			return $this->value();
		});
	}
	
	public function countRenderableEntries()
	{
		return count($this->renfderableEntries());
	}

	/**
	 * @return mixed|null
	 */
	public function renderable()
	{
		return $this->callParam('renderable', function () {
			$entries = $this->renderableEntries();
			return count($entries) > 0;
		});
	}

	/**
	 * @return null
	 */
	public function containerClass()
	{
		return $this->param('container_class', "b-{$this->type}");
	}

	/**
	 * @return null
	 */
	public function entryClass()
	{
		return $this->param('entry_class', $this->containerClass() . '__entry');
	}

	/**
	 * @return string
	 */
	public function entryTemplate()
	{
		return 'fields ~ attaches.entry';
	}

	/**
	 * @return array
	 */
	protected function defaultContext()
	{
		$context = parent::defaultContext();
		$context['entry_template'] = $this->entryTemplate();
		$context['entry_class'] = $this->entryClass();
		$context['container_class'] = $this->containerClass();
		return $context;
	}
}
