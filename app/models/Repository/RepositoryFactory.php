<?php
namespace Models\Repository;

use \Exception;

class RepositoryFactory
{
    /**
     * @param string $repositoryName
     * @return object
     * @throws Exception
     */
    public function getRepository(string $repositoryName) : object
    {
        $someRepository = __NAMESPACE__ . '\\' . $repositoryName;

        if (!class_exists($someRepository)) {
            throw new Exception('Can\'t load Repository "' . $repositoryName . '". Unknown Repository.');
        }

        return new $someRepository;
    }

    /**
     * @param string $name
     * @return object
     * @throws Exception
     */
    function __get(string $name) : object
    {
        return $this->getRepository(ucfirst($name));
    }

}
