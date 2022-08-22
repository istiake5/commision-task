<?php


namespace Models;


use Maps\AccountTypeMap;
use Maps\OperationMap;

class Transaction
{
    private $id, $date, $accountId, $accountType, $type, $amount, $currency;

    public function __construct($arg)
    {
        $this->id = uniqid();
        $this->date = $arg[0];
        $this->accountId = $arg[1];
        $this->accountType = $arg[2];
        $this->type = $arg[3];
        $this->amount = $arg[4];
        $this->currency = $arg[5];
    }

    public function account(): Account
    {
        return new Account($this->getAccountId(), $this->getAccountType());
    }

    public function operation(): OperationMap
    {
        return new OperationMap($this->getType());
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return mixed
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

}
