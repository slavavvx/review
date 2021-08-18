<?php
namespace Models;

class User
{
     //---------- Model config params ----------//
    /**
     * Table name
     *
     * @var string
     */
    const TABLE_NAME = 'users';


    //---------- Model data params ----------//

    /**
     * @var integer
     */
    protected $user_id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var object
     */
    private $link;
    

    //---------- Model Getters -----------//

    /**
     * Get the user_id column value.
     *
     * @return  int
     */
    public function getId() : int
    {
        return $this->link->lastInsertId();
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
     


}
