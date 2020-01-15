<?php

namespace TAO\Foundation;
use Leafo\ScssPhp\Compiler;

class Scss
{
	public function compile($content)
	{
		$compiler = new Compiler();
		return $compiler->compile($content);
	}

	public function compileFile($path)
	{
		$content = file_get_contents($path);
		return $this->compile($content);
	}

	public function make($files, $name = false)
	{
		if (is_string($files)) {
			$files = [$files];
		}
		$time = 0;
		$toCompile = [];
		foreach($files as $file) {
			if ($path = app('view.finder')->findInResources("styles/{$file}.scss")) {
				$fileTime = filemtime($path);
				$time = $fileTime > $time ? $fileTime : $time;
				$toCompile[$file] = $path;
			}
		}
		if (!empty($toCompile)) {
			if (!$name) {
				$name = str_replace('/', '.', implode('-', array_keys($toCompile)));
				if (strlen($name) > 40) {
					$name = md5($name);
				}
			}
			$dir = "builds-scss";
			$compiledUrl = "/{$dir}/{$name}.css";
			$compiledDir = \TAO::publicPath() . "/{$dir}";
			$compiledPath = \TAO::publicPath() . $compiledUrl;
			$do = false;
			if (!is_file($compiledPath)) {
				$do = true;
			} else {
				$compiledTime = filemtime($compiledPath);
				if ($time > $compiledTime) {
					$do = true;
				}
			}

			if ($do) {
				$content = '';
				foreach($toCompile as $file => $path) {
					$content .= ("// -- {$file} --\n\n" . file_get_contents($path) . "\n\n\n");
				}
				$content = $this->compile($content);
				if (!is_dir($compiledDir)) {
					mkdir($compiledDir);
					chmod($compiledDir, 0777);
				}
				file_put_contents($compiledPath, $content);
				chmod($compiledPath, 0666);
			}
			return $compiledUrl;
		}
	}
}
