<?php
namespace Models\Repository;

use Library\DbAdapter;
use \PDO;
use \Exception;

abstract class RepositoryAbstract
{
	/**
     * @var DbAdapter
     */
    private $db;

	/**
     * @return string
     */
    abstract public function getModelName();

    /**
     * @return object
     */
    public function getNew() : object
    {
        $modelClass = $this->getModelName();
        /** @var \Models\$modelClass $newModel */
        $newModel = new $modelClass;

//        if (!empty($data)) {
//            $newModel->setData($data);
//        }

        return $newModel;
    }

    /**
     * @param DbAdapter $db
     * @return self
     */
    public function setDb(DbAdapter $db) : self
    {
        $this->db = $db;

        return $this;
    }
	
	/**
	 * @return PDO
	 * @throws Exception
	 */
    public function getDb() : PDO
    {   
        return $this->db->getConnection();
    }
	
	/**
	 * @return int
	 * @throws Exception
	 */
    public function getLastInsertId() : int
    {
        return $this->getDb()->lastInsertId();
    }
}
