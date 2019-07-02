<?php

namespace TAO\Foundation;

use Mpdf;

class Pdf
{
	protected $mpdf = null;
	protected $config = [];
	protected $documentParams = [
		'title' => 'Document',
		'author' => '',
		'subject' => '',
		'keywords' => '',
		'creator' => 'Laravel/TAO3/MPDF',
		'display_mode' => 'fullpage',
	];

	public function mpdf()
	{
		if (is_null($this->mpdf)) {
			$config = array(
				'mode' => config('tao.pdf.mode', 'utf-8'),
				'format' => config('tao.pdf.format', 'A4'),
				'default_font_size' => config('tao.pdf.default_font_size'),
				'default_font' => config('tao.pdf.default_font'),
				'margin_left' => config('tao.pdf.margin_left'),
				'margin_right' => config('tao.pdf.margin_right'),
				'margin_top' => config('tao.pdf.margin_top'),
				'margin_bottom' => config('tao.pdf.margin_bottom'),
				'margin_header' => config('tao.pdf.margin_header'),
				'margin_footer' => config('tao.pdf.margin_footer'),
				'orientation' => config('tao.pdf.orientation'),
				'tempDir' => config('tao.pdf.tempDir', base_path('temp')),
			);
			$config = \TAO::merge($config, $this->config);
			$this->mpdf = new Mpdf\Mpdf($config);
			foreach ($this->documentParams as $key => $value) {
				$method = 'set' . ucfirst(camel_case($key));
				$this->mpdf->$method($value);
			}
		}
		return $this->mpdf;
	}

	/**
	 * Производит рендер из шаблона Blade
	 *
	 * @param $view
	 * @param array $context
	 * @return $this
	 */
	public function render($view, $context = [])
	{
		$html = view($view, $context)->render();
		$this->mpdf()->WriteHTML($html);
		return $this;
	}

	/**
	 * Производит рендер из готового HTML-файла
	 *
	 * @param $html
	 * @return $this
	 */
	public function html($html)
	{
		$this->mpdf()->WriteHTML($html);
		return $this;
	}

	/**
	 * Отдает PDF-документ в браузер. По умолчанию inline (для показа в окне браузера)
	 *
	 * @param string $filename
	 * @param string $disposition
	 * @return $this
	 */
	public function response($filename = 'document.pdf', $disposition = 'inline')
	{
		$pdf = (string)$this;
		return response($pdf)->header('Content-Type', 'application/pdf')->header('Content-disposition', $disposition . '; filename=' . $filename);
	}

	/**
	 * Отдает PDF-документ на скачивание
	 *
	 * @param string $filename
	 * @return Pdf
	 */
	public function download($filename = 'document.pdf')
	{
		return $this->response($filename, 'attachment');
	}

	/**
	 * Сохраняет PDF-документ в файл
	 *
	 * @param $filename
	 * @return mixed
	 */
	public function save($filename)
	{
		return $this->mpdf->Output($filename, 'F');
	}

	/**
	 * Отдает PDF-документ как строку
	 *
	 * @return mixed
	 */
	public function output()
	{
		return $this->mpdf->Output('', 'S');
	}

	public function __toString()
	{
		return $this->output();
	}

	public function __call($method, $args)
	{
		if ($m = \TAO::regexp('{^set(.+)$}', $method)) {
			$key = strtolower(snake_case($m[1]));
			if (isset($this->documentParams[$key])) {
				$this->documentParams[$key] = $args[0];
			} else {
				$this->config[$key] = $args[0];
			}
			return $this;
		}
		return call_user_func_array($this->mpdf(), $method, $args);
	}
}
