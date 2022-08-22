<?php


namespace Models;


use Maps\AccountTypeMap;

class Account
{
    private $id, $type;

    public function __construct($id, $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    public function type(): AccountTypeMap
    {
       return new AccountTypeMap($this->getType());
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }


}
