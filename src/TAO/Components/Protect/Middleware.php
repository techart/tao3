<?php

namespace TAO\Components\Protect;

class Middleware
{
	public function handle($request, \Closure $next)
	{
		if ($request->getMethod() == 'POST') {
			if ($request->has('_tao_form_info')) {
				$info = \Crypt::decrypt($request->get('_tao_form_info'));
				if (isset($info['min_time'])) {
					if (time() < (int)$info['min_time']) {
						return $this->invalid();
					}
				}
			} else {
				$thisUrl = $request->getPathInfo();
				foreach (config('tao.protect_urls', []) as $url) {
					if ($url == $thisUrl) {
						$this->invalid();
					} else {
						if (ends_with($url, '*')) {
							if (starts_with($thisUrl, substr($url, 0, strlen($url) - 1))) {
								return $this->invalid();
							}
						}
					}
				}
			}
		}
		return $next($request);
	}

	protected function invalid()
	{
		if (request()->ajax()) {
			return response([
				'result' => 'protect_error',
				'errors' => [
					'_protect' => config('tao.robots_protection_message', 'Robot detected!'),
				],
				'redirect' => false,
			]);
		}
		if ($url = config('tao.robots_protection_redirect')) {
			return redirect($url);
		}
		if ($tpl = config('tao.robots_protection_template')) {
			return response(view($tpl));
		}
		throw new Exception('Protection error');
	}
}