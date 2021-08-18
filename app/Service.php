<?php
namespace Review;

use Core\Route;
use Core\View;
use Core\Storage;
use Library\DbAdapter;
use Library\Error;
use Library\Http\Request;
use Library\Http\Response;
use Routes\FrontendRoutes;
use Closure;

class Service
{
    /**
    * @param array $config
    * @return Closure
    */
    public function initDb(array $config) : Closure
    {
        return function() use ($config) {

            $dbAdapter = new DbAdapter([

                "host"      => $config['mysql']['host'],
                "dbname"    => $config['mysql']['dbname'],
                "username"  => $config['mysql']['username'],
                "password"  => $config['mysql']['password'],
                "port"      => $config['mysql']['port'],
                "charset"   => $config['mysql']['charset'],
            ]);
            return $dbAdapter;
        };
    }

    /**
     * @param Storage $storage
     * @return Route
     * @throws \Exception
     */
    public function getRoute(Storage $storage) : Route
    {
        /** @var \Core\View $view */
        $view = $storage->get('view');
        /** @var \Library\Http\Request $request */
        $request = $storage->get('request');

        $route = new Route($view);
        $route->setDefaultDir('frontend');
        $route->notFound([
            'controller' => 'base',
            'action' => 'errorNotFound',
        ]);

        $route->mount('frontend', new FrontendRoutes($request));

        return $route;
    }

    /**
     * @return Closure
     */
    public function getView() : Closure
    {
        return function () {
            return new View();
        };
    }

    /**
     * @return Closure
     */
    public function getRequest() : Closure
    {
        return function() {
            return new Request();
        };
    }

    /**
     * @return Closure
     */
    public function getResponse() : Closure
    {
        return function() {
            return new Response();
        };
    }

    /**
     * @param array $config
     * @return Closure
     */
    public function getError(array $config) : Closure
    {
        return function() use ($config) {
            return new Error($config['debug']);
        };
    }
}
