<?php

namespace TAO\Middleware;

class Urls
{
	public function handle($request, \Closure $next)
	{
		if ($r = \Assets::redirectIfNeed()) {
			return $r;
		}
		return $next($request);
	}
}