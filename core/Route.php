<?php
namespace Core;

use \Exception;


class Route
{
	/**
	 * @var string $uri
	 */
	private $uri;

	/**
	 * @var object View
	 */
	private $view;

	/**
	 * @var array $routes
	 */
	private $routes = [];
	
	/**
	 * @var array $routesNotFound
	 */
	private $routesNotFound = [];

	/**
	 * @var string $defaultDir
	 */
	private $defaultDir;

	/**
	 * @var array $viewDirs
	 */
	private $viewDirs = [];


	public function __construct(View $view)
	{
		$this->view = $view;
		$this->setRequest();
	}

    /**
     * Receiving request
     */
    private function setRequest() : void
    {
        if (isset($_GET['_uri'])) {
        	$this->uri = $_GET['_uri'];
        }
    }

    /**
     * Default directory for Index and Base controllers
     * @param string $dir
     */
    public function setDefaultDir(string $dir) : void
    {
        $this->defaultDir = $dir;
    }

    /**
     * If page is not found
     * @param array $routesNotFound
     * @throws Exception
     */
    public function notFound(array $routesNotFound) : void
    {
    	if (isset($routesNotFound['controller']) && isset($routesNotFound['action'])) {

	    	$this->routesNotFound = [
	    		'controller' => $this->defaultDir . '-' . $routesNotFound['controller'],
	    		'action' => $routesNotFound['action'],
	    	];
		}
    }

    /**
     * Mounting of list of the routes and setting path to views
     * @param string $routesName
     * @param object $appRoutes
     */
    public function mount(string $routesName, object $appRoutes) : void
    {
    	$routes = $appRoutes->getRoutes();

    	if (!empty($this->routes)) {
            $this->routes = array_merge($this->routes, $routes);
        } else {
            $this->routes = $routes;
        }

        $viewsDir = $appRoutes->getViewsDir();
        $this->viewDirs[$routesName] = $viewsDir;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function findMatch() : array
    {
		if (empty($this->uri)) {
			return ['controller' => $this->defaultDir . '-index', 'action' => 'index'];
		}

		if (empty($this->routesNotFound)) {
			throw new Exception('There is no data for case "page is not found".');
		}

		$method = $_SERVER['REQUEST_METHOD'];
		$request = rtrim($method . $this->uri, '/');

		foreach ($this->routes as $routeKey => $routeParts) {

			if (preg_match("~^$routeKey$~i", $request)) {
				
				if (isset($routeParts['ajax']) && !$routeParts['ajax']) {
					return $this->routesNotFound;
				} 

				return $routeParts;
			}
		}

		return $this->routesNotFound;
    }

    /**
     * @return bool
     * @throws Exception
     */
	public function start() : bool
	{	
		$routeParts = $this->findMatch();

		if (!isset($routeParts['controller']) || !isset($routeParts['action'])) {
			throw new Exception('Data array of route have to contain keys "controller" and "action".');
		}

		$dataController = explode('-', $routeParts['controller']);
		$controllerDir = $dataController[0];
		$controllerName = $dataController[1];

		$namespace = ucfirst($controllerDir) . '\Controllers\\';
		$controllerName = $namespace . ucfirst($controllerName) . 'Controller';
		$actionName = $routeParts['action'] . 'Action';

		$controller = new $controllerName();
		$controller->$actionName();

		$viewsDir = $this->viewDirs[$controllerDir];
		$this->view->setViewsDir($viewsDir);
		return true;
	}
}
