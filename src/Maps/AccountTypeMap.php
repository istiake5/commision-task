<?php


namespace Maps;


use Exceptions\CommonException;

class AccountTypeMap
{
    const PRIVATE = 1;
    const BUSINESS = 2;

    private $typeStr;

    public function __construct(string $type)
    {
        if (!in_array($type, ['private', 'business']))
            throw new CommonException('Invalid account type: '.$type);

        $this->typeStr = $type;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->typeStr == 'private' ? self::PRIVATE : self::BUSINESS;
    }
}
