<?php

namespace TAO\Text\Processor;

use TAO\Text\ProcessorInterface;

class Insertions implements ProcessorInterface
{
	public function process($text)
	{
		return preg_replace_callback('{%([a-z0-9_-]+)\{(.*?)\}}ism', function($m) {
			$name = strtolower($m[1]);
			$arg = $m[2];
			$insertion = config("tao.insertions.{$name}");
			if ($insertion) {
				$args = [];
				$namedArgs = [
					'insertion_source' => $m[0],
					'insertion_code' => $m[1],
					'insertion_args' => $m[2],
				];
				$unnamedArgs = [];
				$callback = false;
				if (is_array($insertion) && isset($insertion['action'])) {
					$callback = $insertion['action'];
				} else {
					$callback = $insertion;
				}

				if (is_string($callback) || \TAO\Callback::isValidCallback($callback)) {
					if (is_array($insertion) && isset($insertion['params'])) {
						$args = $namedArgs = array_merge($namedArgs, $insertion['params']);
					}
					foreach (explode("\n", $arg) as $line) {
						foreach (explode(';', $line) as $item) {
							$item = trim($item);
							if ($item) {
								if ($m = \TAO::regexp('{^([a-z0-9_]+)\s*=(.*)$}', $item)) {
									$args[$m[1]] = trim($m[2]);
									$namedArgs[$m[1]] = trim($m[2]);
								} else {
									$args[] = $item;
									$unnamedArgs[] = $item;
								}
							}
						}
					}
					if (\TAO\Callback::isValidCallback($callback)) {
						return \TAO\Callback::instance($callback)->args([$args, $namedArgs, $unnamedArgs])->call();
					}
					if (view()->exists($callback)) {
						$namedArgs['params'] = $unnamedArgs;
						return view($callback, $namedArgs);
					}
					return app($callback)->run($args, $namedArgs, $unnamedArgs);
				}
			}
			return $m[0];
		}, $text);
	}
}