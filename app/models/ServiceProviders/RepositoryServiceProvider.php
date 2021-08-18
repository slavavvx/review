<?php
namespace Models\ServiceProviders;

use Core\Storage;
use Models\Repository\RepositoryFactory;
use \Closure;

class RepositoryServiceProvider
{
    private $db;

    /**
     * @param Storage $storage
     */
    public function register(Storage $storage) : void
    {
        $this->db = $storage->get('db');

        $storage->setShared('reviewRepository', $this->repository('review'));
        $storage->setShared('imageRepository', $this->repository('image'));
    }

    /**
     * @param string $entityName
     * @return Closure
     */
    public function repository(string $entityName) : Closure
    {
        $db = $this->db;

        return function() use ($entityName, $db) {
            return (new RepositoryFactory())->$entityName->setDb($db);
        };
    }
}
