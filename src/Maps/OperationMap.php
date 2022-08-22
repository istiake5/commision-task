<?php


namespace Maps;


use Exceptions\CommonException;

class OperationMap
{
    const WITHDRAW = 1;
    const DEPOSIT = 2;

    private $typeStr;

    public function __construct(string $type)
    {
        if (!in_array($type, ['withdraw', 'deposit']))
            throw new CommonException('Invalid transaction operation type: '.$type);

        $this->typeStr = $type;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->typeStr == 'withdraw' ? self::WITHDRAW : self::DEPOSIT;
    }

    public function getAction(): string
    {
        return $this->getType() === self::WITHDRAW ? 'generateCommissionWithdraw' : 'generateCommissionDeposit';
    }
}
