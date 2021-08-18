<?php
namespace Core;

use \Exception;

class View
{
	const LAYOUT = 'layout';

	/**
     * @var string viewsDir
     */
	private $viewsDir;

	/**
     * @var array vars
     */
	private $vars = [];

	/**
	 * Setting path to folder where is views
	 *
	 * @param string $viewsDir
	 */
	public function setViewsDir(string $viewsDir) : void
	{
		$this->viewsDir = $viewsDir;
	}

	/**
	 * Setting the variables for layout
	 *
	 * @param string $name
	 * @param string | array $data
	 */
	public function setVar(string $name, $data) : void
	{
		$this->vars[$name] = $data;
	}

	/**
	 * @param string $template
	 * @return string
	 * @throws Exception
	 */
	public function renderTemplate(string $template) : string
	{
		$path = $this->viewsDir . $template . '.phtml';
		
		if (!is_readable($path)) {
			throw new Exception('Template "' . $path . '" is not exists!');
		}

		ob_start();
		extract($this->vars, EXTR_SKIP);
		require_once $path;

		return ob_get_clean();
	}

    /**
     * @return bool
     * @throws Exception
     */
	public function getLayout() : bool
	{
		$result = $this->renderTemplate(self::LAYOUT);
		echo $result;
		return true;
	}
}
