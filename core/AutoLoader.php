<?php
namespace Core;

use \Exception;

class AutoLoader
{    
    /**
     * @var array $nameSpaces
     */
    private $nameSpaces = [];


    /**
     * @param array $nameSpaces
     */
    public function addNameSpaces(array $nameSpaces) : void
    {
        if (!empty($this->nameSpaces)) {
            $this->nameSpaces = array_merge($this->nameSpaces, $nameSpaces);
        } else {
            $this->nameSpaces = $nameSpaces;
        }
    }

    /**
     *
     * @param bool $prepend
     */
    public function register(bool $prepend = false)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    /**
     * @param array $paths
     * @return bool
     * @throws Exception 
     */
    public function registerFiles(array $paths) : bool
    {
        // if the file exists, require it
        foreach ($paths as $path) {

            if (!is_readable($path)) {
                throw new Exception('File ' . $path . ' does not exist!');
            }

            require_once $path;
        }

        return true;
    }

    /**
     * Autoload function of the class
     *
     * @param  string $class The name of the class
     * @return bool|Exception True if loaded, Exception otherwise
     * @throws Exception
     */
    private function loadClass(string $class)
    {
        if ($file = $this->findFile($class)) {
            
            // if the file exists, require it
            if (is_readable($file)) {
                require_once $file;

                return true;
            }

            throw new Exception('File ' . $file . ' does not exist!');
        } else {
            throw new Exception('Class: ' . $class . ' is not found in namespaces array!');
        }
    }

    /**
     * Search of the path to file of the class with namespace
     *
     * @param  string $class
     * @return string|null
     */
    private function findFile(string $class) : ?string
    {
        $pos = strrpos($class, '\\');
        $nameSpace = substr($class, 0, $pos);
        $className = substr($class, ($pos + 1));

        if (array_key_exists($nameSpace, $this->nameSpaces)) {
            $file = $this->nameSpaces[$nameSpace] . $className . '.php';

            return $file;
        }
                
        return null;
    }
}    
    