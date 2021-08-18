<?php
namespace Library;

use \PDO;
use \PDOException;
use \Exception;


class DbAdapter
{
	/**
	 * @var string
	 */
    private static $host;
	
	/**
	 * @var string
	 */
    private static $dbname;
	
	/**
	 * @var string
	 */
    private static $user;
	
	/**
	 * @var string
	 */
    private static $pwd;
    
	/**
	 * @var int
	 */
    private static $port;
	
	/**
	 * @var string
	 */
    private static $charset;
	
	/**
	 * @var null | PDO
	 */
    private $link = null;

    public function __construct(array $config)
    {
        self::$host = $config['host'];
        self::$dbname = $config['dbname'];
        self::$user = $config['username'];
        self::$pwd = $config['password'];
        self::$port = $config['port'];
        self::$charset = $config['charset'];
    }

    /**
     * @return void
     * @throws Exception
     */
    private function connect() : void
    {   
        $dsn = 'mysql:host=' . self::$host . 
            ';dbname=' . self::$dbname .
            ';port=' . self::$port .
            ';charset=' . self::$charset;

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->link = new PDO($dsn, self::$user, self::$pwd, $options);
        } catch (PDOException $e) {
            throw new Exception('It is not managed to connect to the database! ' . $e->getMessage());
        }
    }
	
	/**
	 * @return PDO
	 * @throws Exception
	 */
    public function getConnection() : PDO
    {
        if (empty($this->link)) {
            $this->connect();
        }
        return $this->link;
    }
}
