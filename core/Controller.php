<?php
namespace Core;

//use Core\Storage;
use Library\Http\Request;
use Library\Http\Response;
use Library\Error;

class Controller
{
	/**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Error
     */
    protected $error;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var Storage
     */
    private static $storage;


    public function __construct()
    {
        $this->initialize();
    }
    
    protected function initialize()
    {   
        $storage = $this->getStorage();

        $this->request = $storage->get('request');
        $this->response = $storage->get('response');
        $this->error = $storage->get('error');
        $this->view = $storage->get('view');
    }

    /**
     * @param Storage $storage
     */
    public static function setStorage(Storage $storage) : void
    {
        self::$storage = $storage;
    }

    /**
     * @return Storage
     */
    protected function getStorage() : Storage
    {
        return self::$storage;
    }
}
