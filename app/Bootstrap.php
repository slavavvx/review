<?php
declare(strict_types=1);

namespace Review;

use Core\AutoLoader;
use Core\Storage;
use Core\Controller;
use Core\Model;
use Models\ServiceProviders\RepositoryServiceProvider;
use Services\ServiceProviders\ServiceServiceProvider;
use \Exception;


class Bootstrap
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var string $environment
     */
    private $environment;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Service
     */
    private $service;

    /**
     * Bootstrap constructor.
     */
    public function __construct()
    {
        if (!isset($_SERVER['APP_ENV']) || $_SERVER['APP_ENV'] !== 'production') {
            $this->registerEnv();
        }

        $this->environment = $_SERVER['APP_ENV'];
    }

    /**
     * Registration vendors
     * @return void
     */
    private function registerEnv() : void
    {   
        // registration of environment variables 
        require_once ROOT_PATH . '/vendor/autoload.php';

        $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
        $dotenv->load();
    }

    /**
     * @param Storage $storage
     */
    public function setStorage(Storage $storage) : void
    {
        $this->storage = $storage;
    }

    /**
     * @return Storage
     */
    public function getStorage() : Storage
    {
        return $this->storage;
    }

    /**
     * @throws Exception
     */
    public function register() : void
    {
        $this->initConstants();
        $this->initLoader();
        $this->initConfig();
        $this->registerStartServices();
        $this->registerServiceProviders();
        $this->initServices();
    }

    /**
     * @return void
     * Register start application services
     */
    protected function registerStartServices() : void
    {
        $storage = $this->getStorage();
        $service = $this->getService();

        $storage->setConfig($this->config);
        $storage->setShared('error', $service->getError($this->config));
        $storage->setShared('db', $service->initDb($this->config));
        $storage->setShared('request', $service->getRequest());
        $storage->setShared('response', $service->getResponse());
        $storage->setShared('view', $service->getView());
    }

    /**
     * @return void
     * Register service providers
     */
    protected function registerServiceProviders() : void
    {   
        $storage = $this->getStorage();

        $storage->setShared('repositoryServiceProvider', new RepositoryServiceProvider);
        $storage->setShared('serviceServiceProvider', new ServiceServiceProvider);
    }

    /**
     * @throws Exception
     */
    private function initServices() : void
    {
        /** @var \Core\Storage $storage */
        $storage = $this->getStorage();
        $service = $this->getService();

        Model::setDb($storage->get('db'));
        Controller::setStorage($storage);

        $service->getRoute($storage)->start();
    }

    /**
     * @return void
     */
    protected function initConstants() : void
    {
        if (!defined('ROOT_PATH')) {
            exit ("Error, wrong way to file.<br><a href=\"/\">Go to main</a>.");
        }

        defined('APP_DIR') || define('APP_DIR', ROOT_PATH . '/app/');
    }

    private function initLoader() : void
    {
        require_once ROOT_PATH . '/core/AutoLoader.php';

        $nameSpaces = [
            'Core'                      => ROOT_PATH . '/core/',
            'Review'                    => APP_DIR,
            'Routes'                    => APP_DIR . 'routes/',
            'Library'                   => APP_DIR . 'library/',
            'Library\Http'              => APP_DIR . 'library/Http/',
            'Frontend\Controllers'      => APP_DIR . 'frontend/controllers/',
            'Models'                    => APP_DIR . 'models/',
            'Models\ServiceProviders'   => APP_DIR . 'models/ServiceProviders/',
            'Models\Repository'         => APP_DIR . 'models/Repository/',
            'Services'                  => APP_DIR . 'services/',
            'Services\Reviews'          => APP_DIR . 'services/Reviews/',
            'Services\ServiceProviders' => APP_DIR . 'services/ServiceProviders/',
            'Services\ReCaptcha'        => APP_DIR . 'services/ReCaptcha/',
            'Services\Forms'            => APP_DIR . 'services/Forms/',
            'Services\Entities'         => APP_DIR . 'services/Entities/',
            'Services\Validator'        => APP_DIR . 'services/Validator/',
            'Services\DataUpload'       => APP_DIR . 'services/DataUpload/',
            'Traits'                    => APP_DIR . 'traits/',
        ];

        $loader = new AutoLoader();
        $loader->addNameSpaces($nameSpaces);
        // $loader->registerFiles([
        //     ROOT_PATH . '/vendor/dotenv/autoload.php',
        // ]);
        $loader->register();
    }

    /**
     * @throws Exception
     */
    private function initConfig() : void
    {
        $configFile = ROOT_PATH . '/config/config-' . $this->environment . '.php';

        if (!file_exists($configFile)) {
            throw new Exception('Config file "' . $configFile . '" is not found.');
        }

        $this->config = require_once $configFile;
    }

    /**
     * @return Service
     */
    public function getService() : Service
    {
        if ($this->service instanceof Service) {
            return $this->service;
        }

        $this->service = new Service();
        return $this->service;
    }
}
