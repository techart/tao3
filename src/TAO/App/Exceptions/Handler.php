<?php

namespace TAO\App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Contracts\View\Factory as ViewFactory;
use TAO\ErrorsNotifier;


class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		\Illuminate\Auth\AuthenticationException::class,
		\Illuminate\Auth\Access\AuthorizationException::class,
		\Symfony\Component\HttpKernel\Exception\HttpException::class,
		ModelNotFoundException::class,
		\Illuminate\Session\TokenMismatchException::class,
		\Illuminate\Validation\ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception $exception
	 * @return mixed
	 * @throws Exception
	 */
	public function report(Exception $exception)
	{
		if ($this->shouldReport($exception)) {
			$this->notify($exception);
		}
		return parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Exception $exception
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, \Exception $exception)
	{
		if ($this->isHttpException($exception)) {
			$status = $exception->getStatusCode();
			$view = "{$status}";
			$factory = app(ViewFactory::class);
			if ($factory->exists($view)) {
				return response(view($view), $status);
			}
		}
		if ($exception instanceof ModelNotFoundException) {
			abort(404);
		}
		return parent::render($request, $exception);
	}

	/**
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Illuminate\Auth\AuthenticationException $exception
	 * @return \Illuminate\Http\Response
	 * @throws \TAO\Exception\UnknownDatatype
	 */
	protected function unauthenticated($request, AuthenticationException $exception)
	{
		if ($request->expectsJson()) {
			return response()->json(['error' => 'Unauthenticated.'], 401);
		}

		return redirect()->guest(\TAO::datatype('users')->loginUrl());
	}

	/**
	 * @param \Exception $exception
	 * @return void
	 */
	protected function notify($exception)
	{
		app(ErrorsNotifier::class)->run($exception);
	}
}
