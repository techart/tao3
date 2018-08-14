<?php

namespace TAO\ORM\Traits;

trait SchemaHelper
{
	private $generatedSchemaData = false;
	private $weightInCSV = 0;
	private $weightInForm = 0;
	private $weightInList = 0;

	public function fields()
	{
		return $this->fieldsHelper();
	}

	protected function fieldsHelper()
	{
		$data = $this->generateSchemaData();//dd($data);
		return $data['fields'];
	}

	protected function groupsHelper()
	{
		$data = $this->generateSchemaData();
		return $data['groups'];
	}

	protected function generateSchemaData()
	{
		if ($this->generatedSchemaData) {
			return $this->generatedSchemaData();
		}
		$ref = new \ReflectionClass($this);
		$path = $ref->getFileName();
		$cache = 'schema-helper'.md5($path);
		$time = filemtime($path);

		$data = \Cache::get($cache, 0);
		//if (is_array($data) && $data[''])

		//\Cache::put($key, $time, 500000);
		$data = $this->parseSource($path);
		return $data;
	}

	protected function parseSource($path)
	{
		$src = file_get_contents($path);
		if ($m = \TAO::regexp('{/\*schema(.+)schema\*/}ism', $src)) {
			$list = [];
			$form = [];
			$groups = [];
			$tab = false;
			$group = false;
			$src = trim($m[1]);
			$tabgroup = false;
			$prevField = false;
			foreach(explode("\n", $src) as $line) {
				$line = trim($line);
				if (!empty($line)) {
					if ($line[0] == '#' || $line[0] == '/') {

					} elseif (substr($line, 0, 2) == '@@') {
						$line = substr($line, 2);
						list($code, $label) = $this->parseGroup($line, 'group');
						$group = $code;
						$tabgroup = "{$tab}.{$group}";
						$groups[$tabgroup] = $label;

					} elseif (substr($line, 0, 1) == '@') {
						$line = substr($line, 1);
						list($code, $label) = $this->parseGroup($line, 'tab');
						$tab = $code;
						$group = false;
						$groups[$tab] = $label;
						$tabgroup = rtrim("{$tab}.{$group}", '.');
					} elseif ($m = \TAO::regexp('{^(\d+)\s*=\s*(.+)$}i', $line)) {
						if ($prevField) {
							$key = $m[1];
							$value = $this->valueString($m[2]);
							$items = $form[$prevField]['items'] ?? [];
							$items[$key] = $value;
							$form[$prevField]['items'] = $items;
						}
					} elseif ($m = \TAO::regexp('{^"([^"]+)"\s*=\s*(.+)$}i', $line)) {
						if ($prevField) {
							$key = trim($m[1]);
							$value = $this->valueString($m[2]);
							$items = $form[$prevField]['items'] ?? [];
							$items[$key] = $value;
							$form[$prevField]['items'] = $items;
						}
					} elseif ($m = \TAO::regexp('{^\*([0-9a-z_]+)(.*)$}i', $line)) {
						$list[$m[1]] = $this->parseListField($m);
					} elseif ($m = \TAO::regexp('{^([^\s]+)(.*)$}i', $line)) {
						list($field, $data) = $this->parseFormField($m, $tabgroup);
						if (\TAO::regexp('{^[a-z0-9_]+$}i', $field)) {
							$prevField = $field;
							$form[$field] = $data;
						}
					} else {

					}

				}
			}
			foreach ($list as $field => $data) {
				$fdata = $form[$field] ?? ['type' => 'dummy', 'in_form' => false];
				$fdata['in_list'] = true;
				$fdata['weight_in_list'] = $data['weight'];
				if (isset($data['label'])) {
					$fdata['label_in_list'] = $data['label'];
				} 
				if (isset($data['render'])) {
					$fdata['render_in_admin_list'] = $data['render'];
				}
				if (isset($data['th'])) {
					$fdata['admin_th_attrs'] = $data['th'];
				}
				if (isset($data['td'])) {
					$fdata['admin_td_attrs'] = $data['td'];
				}
				foreach(['formula', 'order'] as $key) {
					if (isset($data[$key])) {
						$fdata[$key] = $data[$key];
					}
				}
				foreach(['link_in_list'] as $key) {
					if (isset($data[$key])) {
						$fdata[$key] = $data[$key];
					}
				}
				$form[$field] = $fdata;
			}
			return [
				'time' => time(),
				'groups' => $groups,
				'fields' => $form,
			];
		} else {
			throw new \TAO\ORM\Exception\SchemaHelper("Schema block not found in {$path}");
		}
	}

	protected function valueString($s)
	{
		$s = trim($s);
		if (starts_with($s, '"') && ends_with($s, '"')) {
			$s = trim($s, '"');
		} elseif (starts_with($s, "'") && ends_with($s, "'")) {
			$s = trim($s, "'");
		}
		return $s;
	}

	protected function parseString($s)
	{
		$chunks = [];
		$s = trim($s);
		while (!empty($s)) {
			if ($m = \TAO::regexp('{^([0-9a-z_-]+)\s*=\s*"([^"]+)"(.*)$}i', $s)) {
				$key = $m[1];
				$value = trim($m[2]);
				$s = trim($m[3]);
				$chunks[$key] = $value;
			} elseif ($m = \TAO::regexp('{^([0-9a-z_-]+)\s*=\s*([^\s]+)(.*)$}i', $s)) {
				$key = $m[1];
				$value = trim($m[2]);
				$s = trim($m[3]);
				$chunks[$key] = $value;
			} elseif ($m = \TAO::regexp('{^"([^"]+)"(.*)$}i', $s)) {
				$chunks['label'] = $m[1];
				$s = trim($m[2]);
			} elseif ($m = \TAO::regexp('{^([^\s]+)(.*)$}i', $s)) {
				$chunks[] = $m[1];
				$s = trim($m[2]);
			} else {
				$chunks[] = $s;
				$s = '';
			}
		}
		return $chunks;
	}

	protected function parseGroup($src, $salt = 'tab')
	{
		$code = false;
		$label = false;
		foreach($this->parseString($src) as $key => $value) {
			if (is_numeric($key) && !$code && \TAO::regexp('{^[a-z0-9_]+$}i', $value)) {
				$code = $value;
			} elseif ($key == 'label') {
				$label = $value;
			}
		}
		if (!$code) {
			$code = $salt . md5($label);
		}
		if (!$label) {
			$label = $code;
		}
		return [$code, $label];
	}

	protected function parseListField($m)
	{
		$data = [];
		$line = trim($m[2]);
		$td = '';
		$th = '';
		foreach($this->parseString($line) as $key => $value) {
			if (!is_numeric($key)) {
				if ($key == 'link') {
					$data['link_in_list'] = $value;
				} else {
					$data[$key] = $value;
				}
			} elseif ($m = \TAO::regexp('{^(\d+)(px|\%)$}', $value)) {
				$th .= "width: {$value};";
			} elseif ($value == 'th-center' || $value == 'th-right') {
				$value = str_replace('th-', '', $value);
				$th .= "text-align: {$value};";
			} elseif ($value == 'center' || $value == 'right') {
				$td .= "text-align: {$value};";
			} elseif ($value == 'bold') {
				$td .= "font-weight: {$value};";
			}
		}
		if ($th) {
			$data['th'] = "style=\"{$th}\"";
		}
		if ($td) {
			$data['td'] = "style=\"{$td}\"";
		}
		$data['weight'] = (++$this->weightInList)*1000;
		return $data;
	}
	
	protected function parseType($type)
	{
		return ['type' => $type];
	}

	protected function parseFormField($m, $tabgroup)
	{
		$in_form = true;
		$type = 'string';
		$field = $m[1];
		$line = trim($m[2]);
		if ($m = \TAO::regexp('{^(.+):(.*)$}i', $field)) {
			$field = $m[1];
			$type = str_replace('+', ' ', $m[2]);
		}
		$width = false;
		$data = $this->parseType($type);
		if ($tabgroup) {
			$data['group'] = $tabgroup;
		}
		$style = '';
		foreach($this->parseString($line) as $key => $value) {
			if (!is_numeric($key)) {
				if ($key == 'style') {
					$style = trim($style, ';') . ";{$value}";
				} else {
					$data[$key] = $value;
				}
			} elseif ($value == '!form') {
				$in_form = false;
			} elseif ($m = \TAO::regexp('{^(\d+)(px|\%)$}', $value)) {
				$style .= "width: {$value};";
			} elseif ($m = \TAO::regexp('{^(\d+)h$}', $value)) {
				$style .= "height: {$m[1]}px;";
			}
		}
		if ($style) {
			$data['style'] = $style;
		}
		$data['label'] = $data['label'] ?? $field;
		$data['in_form'] = $data['in_form'] ?? $in_form;
		$data['in_list'] = false;
		$data['weight_in_form'] = (++$this->weightInForm)*1000;
		return [$field, $data];
	}
}
