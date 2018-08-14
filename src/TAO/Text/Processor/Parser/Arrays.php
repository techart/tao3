<?php

namespace TAO\Text\Processor\Parser;

use TAO\Text\ProcessorInterface;

class Arrays implements ProcessorInterface
{
	protected $lines = [];
	protected $cursor = 0;
	protected $out = [];
	protected $errorFound = false;
	protected $errorMessage = false;

	public function process($src)
	{
		$this->lines = [];
		$m = explode("\n", $src);

		foreach ($m as $line) {
			if ($line = trim($line)) {
				if (!starts_with($line, '#')) {
					$this->lines[] = $line;
				}
			}
		}

		$this->cursor = 0;
		$this->out = [];
		$this->errorFound = false;
		while (!$this->eof()) {
			$rc = $this->parseStatement($this->out);
			if ($rc == '}') {
				return 'Ошибка синтаксиса';
			}
			if ($this->errorFound) {
				return $this->errorMessage;
			}
		}
		return $this->out;
	}

	protected function eof()
	{
		return !isset($this->lines[$this->cursor]);
	}

	protected function getLine()
	{
		$line = $this->lines[$this->cursor];
		$this->cursor++;
		return $line;
	}

	protected function error($message)
	{
		$this->errorFound = true;
		$this->errorMessage = $message;
	}

	protected function parseCompound(&$out)
	{
		$end = false;
		while (!$end) {
			$rc = $this->parseStatement($out);
			if ($this->errorFound) {
				return;
			}
			if ($rc == '}') {
				return;
			}
			if ($this->eof()) {
				$this->error('Не закончен блок');
				return;
			}
		}
	}

	protected function parseStatement(&$out)
	{
		if ($this->errorFound) {
			return;
		}

		$line = $this->getLine();

		if ($line == '}') {
			return '}';
		} else {
			if (preg_match('/^([^=]+)=\s*{$/', $line, $m)) {
				$key = trim($m[1]);
				$out[$key] = array();
				$this->parseCompound($out[$key]);
			} else {
				if (preg_match('/^([^=]+)=(.*)$/', $line, $m)) {
					$key = trim($m[1]);
					$val = trim($m[2]);
					$out[$key] = $val;
				} else {
					$key = $line;
					$val = '';
					$out[$key] = $val;
				}
			}
		}

	}
}