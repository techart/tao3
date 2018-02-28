<?php

namespace TAO;

/**
 * Class Application
 * @package TAO
 */
class Application extends \Illuminate\Foundation\Application
{
	/**
	 * @var null
	 */
	protected $taoKernel = null;
	/**
	 * @var null
	 */
	protected $taoRequest = null;
	/**
	 * @var null
	 */
	protected $taoResponse = null;
	/**
	 * @var null
	 */
	protected $taoCLIStatus = null;
	/**
	 * @var null
	 */
	protected $taoCLIInput = null;
	/**
	 * @var null
	 */
	protected $taoCLIKernel = null;

	/**
	 * @return mixed|null
	 */
	public function kernel()
	{
		if (is_null($this->taoKernel)) {
			$this->taoKernel = $this->make(\Illuminate\Contracts\Http\Kernel::class);
		}
		return $this->taoKernel;
	}

	/**
	 * @return null|static
	 */
	public function request()
	{
		if (is_null($this->taoRequest)) {
			$this->taoRequest = \TAO\Foundation\Request::capture();
		}
		return $this->taoRequest;
	}

	public function modifyRequest()
	{
		if ($urls = \TAO::datatype('urlrewriter', false)) {
			$this->taoRequest = $urls->modifyRequest($this->request());
		}
	}

	/**
	 * @return null
	 */
	public function response()
	{
		if (is_null($this->taoResponse)) {
			$this->taoResponse = $this->kernel()->handle($this->request());
		}
		return $this->taoResponse;
	}

	/**
	 *
	 */
	public function run()
	{
		$this->response()->send();
		$this->kernel()->terminate($this->request(), $this->response());
	}

	/**
	 * @return mixed|null
	 */
	public function cliKernel()
	{
		if (is_null($this->taoCLIKernel)) {
			$this->taoCLIKernel = $this->make(\Illuminate\Contracts\Console\Kernel::class);
		}
		return $this->taoCLIKernel;
	}

	/**
	 * @return null|\Symfony\Component\Console\Input\ArgvInput
	 */
	public function cliInput()
	{
		if (is_null($this->taoCLIInput)) {
			$this->taoCLIInput = new \Symfony\Component\Console\Input\ArgvInput;
		}
		return $this->taoCLIInput;
	}

	/**
	 * @return null
	 */
	public function cliStatus()
	{
		if (is_null($this->taoCLIStatus)) {
			$this->taoCLIStatus = $this->cliKernel()->handle($this->cliInput(), new \Symfony\Component\Console\Output\ConsoleOutput);
		}
		return $this->taoCLIStatus;
	}

	/**
	 *
	 */
	public function cli()
	{
		$input = $this->cliInput();
		$status = $this->cliStatus();
		$this->cliKernel()->terminate($input, $status);
		exit($status);
	}
}