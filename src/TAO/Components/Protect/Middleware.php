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
						$this->invalid();
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
								$this->invalid();
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
		throw new Exception('Protection error');
	}
}