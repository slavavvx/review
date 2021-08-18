<?php
namespace Core;

use Library\DbAdapter;
use \PDO;

class Model
{
    /**
     * @var object DbAdapter
     */
    private static $db;

    /**
     * @param DbAdapter $db
     *
     */
    public static function setDb(DbAdapter $db)
    {
        self::$db = $db;
    }

    /**
     * @return PDO
     */
    public function getDb() : PDO
    {
    	return self::$db->getConnection();
    }
}
