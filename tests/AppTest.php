<?php


namespace Tests;

use PHPUnit\Framework\TestCase;

final class AppTest extends TestCase
{
    public function testMain(): void
    {
        $file                = 'input.csv';
        $app                 = new \App($file);
        $transactionServices = $app->getTransactionService();
        $transactionServices->getCurrencyService()->setRates([
        'EUR' => 1,
        'JPY' => 129.53,
        'USD' => 1.1497,
        ]);
        $commissions = [];
        foreach ($transactionServices->calculateCommission()->getCommissions() as $commission)
        $commissions[] = $commission;

        $this->assertEquals('0.60, 3.00, 0.00, 0.06, 1.50, 0, 0.70, 0.30, 0.30, 3.00, 0.00, 0.00, 8612', implode(', ',
        $commissions));
    }

}
