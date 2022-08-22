<?php


use Services\TransactionService;

class App
{
    private $transactionService;

    public function __construct($file)
    {
        $this->transactionService = new TransactionService($file, $this->getTransactionCommissionConfigs());
    }

    public function outputOnConsole()
    {
        foreach ($this->transactionService->calculateCommission()->getCommissions() as $commission)
            echo round($commission, 2). PHP_EOL;
    }

    /**
     * @return TransactionService
     */
    public function getTransactionService(): TransactionService
    {
        return $this->transactionService;
    }

    /**
     * @return array
     */
    private function getTransactionCommissionConfigs(): array
    {
        return [
            'withdraw' => [
                'business'  => 0.5,
                'private'  => 0.3,
            ],
            'deposit' => [
                'business'  => 0.03,
                'private'  => 0.03,
            ],
            'weeklyLimit' => [
                'amount' => 1000,
                'count' => 3
            ]
        ];
    }
}
