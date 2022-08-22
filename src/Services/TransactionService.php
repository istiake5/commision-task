<?php


namespace Services;

use Maps\AccountTypeMap;
use Maps\OperationMap;
use Models\Transaction;
use Utils\CSVConverter;

/**
 * Class TransactionService
 * @package Services
 */
class TransactionService
{

    private $transactions, $currencyService;
    private $commissionCharge;

    public function __construct($file, $commissionCharge)
    {
        $currencies = [];
        $rows = (new CSVConverter($file))()->getRows();
        foreach ($rows as $row) {
            $transaction = new Transaction($row);
            $currencies[] = $transaction->getCurrency();
            $this->transactions[] = $transaction;
        }

        $this->commissionCharge = @json_decode(json_encode($commissionCharge)) ;
        $this->currencyService = new CurrencyService($currencies);
    }

    public function calculateCommission(): TransactionService
    {
        foreach ($this->transactions as $transaction)
            $this->{$transaction->operation()->getAction()}($transaction);

        return $this;
    }

    /**
     * @param Transaction $transaction
     * @throws \Exceptions\CurrencyException
     */
    private function generateCommissionWithdraw(Transaction &$transaction)
    {
        //when business account use flat commission charge
        if ($transaction->account()->type()->getType() === AccountTypeMap::BUSINESS) {
            $transaction->commission = $this->getParentValue($transaction->getAmount(), $this->commissionCharge->withdraw->business );
            return;
        }

        //get config data for personal transaction
        $configCharge = $this->commissionCharge->withdraw->private;
        $configWeeklyLimit = $this->commissionCharge->weeklyLimit->count;
        $configWeeklyLimitAmount = $this->commissionCharge->weeklyLimit->amount;

        //get weekly details
        list($weeklyTransactionTotal, $weeklyTransactionCount) = $this->getWeeklyTotal($transaction);

        //check if weekly limit exceeded
        if (
            $weeklyTransactionCount > $configWeeklyLimit ||
            $weeklyTransactionTotal > $configWeeklyLimitAmount
        ) {
            $transaction->commission = $this->getParentValue($transaction->getAmount(), $configCharge);
            return;
        }

        //check if at least one withdrawal in week
        if ($weeklyTransactionTotal < $configWeeklyLimitAmount) {
            $weeklyLimit = $configWeeklyLimitAmount - $weeklyTransactionTotal;

            $amount = $transaction->getAmount();
            if ($transaction->getCurrency() != $this->currencyService->getBaseCurrency())
                $amount = $this->convertCurrency($transaction->getAmount(), $transaction->getCurrency());

            $partialAmount = $amount - $weeklyLimit;
            //when full transaction amount into limit amount
            if ($partialAmount < 0) {
                $transaction->commission = 0;
                return;
            }

            if ($transaction->getCurrency() != $this->currencyService->getBaseCurrency())
                $partialAmount = $this->convertCurrency($partialAmount, $transaction->getCurrency(), true);

             $transaction->commission = $this->getParentValue($partialAmount, $configCharge);
             return;
        }

        //when no weekly payment has been made, check if this transaction exceeded weekly limit once
        $amount = $transaction->getAmount();
        if ($transaction->getCurrency() != $this->currencyService->getBaseCurrency())
            $amount = $this->convertCurrency($transaction->getAmount(), $transaction->getCurrency());

        $partialAmount = $amount - $configWeeklyLimitAmount;
        if ($partialAmount > 0) {
            if ($transaction->getCurrency() != $this->currencyService->getBaseCurrency())
                $partialAmount = $this->convertCurrency($partialAmount, $transaction->getCurrency(), true);

            $transaction->commission = $this->getParentValue($partialAmount, $configCharge);
            return;
        }

        $transaction->commission = 0;
    }

    /**
     * @param $amount
     * @param $charge
     * @return string
     */
    private function getParentValue($amount, $charge): string
    {
        return number_format(round(($amount * $charge) / 100, 2, PHP_ROUND_HALF_DOWN), 2, '.', '');
    }

    /**
     * @param Transaction $paramTransaction
     * @return array
     * @throws \Exceptions\CurrencyException
     */
    private function getWeeklyTotal(Transaction $paramTransaction): array
    {
        $targetedYearlyWeek = date('Y-W', strtotime($paramTransaction->getDate()));
        $weeklyWithdrawn = 0;
        $count = 0;
        foreach ($this->transactions as $transaction) {
            if (
                strtotime($paramTransaction->getDate()) < strtotime($transaction->getDate()) ||  //later transactions filtered
                $transaction->account()->type()->getType() !== AccountTypeMap::PRIVATE || //only private account's transactions filtered
                $transaction->operation()->getType() !== OperationMap::WITHDRAW || //only withdraw operation's transactions filtered
                $transaction->getAccountId() !== $paramTransaction->getAccountId() || //only targeted account holder's transactions filtered
                $targetedYearlyWeek !== date('Y-W', strtotime($transaction->getDate())) || //only same week's transactions filtered
                $transaction->getId() === $paramTransaction->getId() //targeted transactions filtered
            )
                continue;

            $weeklyWithdrawn += $this->convertCurrency($transaction->getAmount(), $transaction->getCurrency());
            $count++;
        }

        return [$weeklyWithdrawn, $count];
    }

    /**
     * @param float $amount
     * @param string $currency
     * @param bool $isConvertBack
     * @return mixed
     * @throws \Exceptions\CurrencyException
     */
    private function convertCurrency(float $amount, string $currency, bool $isConvertBack = false): float
    {
        if ($isConvertBack)
            $convertedCurrency = $this->currencyService->convert($this->currencyService->getBaseCurrency(), $currency, $amount);
        else
            $convertedCurrency = $this->currencyService->convert($currency, $this->currencyService->getBaseCurrency(), $amount);

        return $convertedCurrency['amount'];
    }

    /**
     * @param Transaction $transaction
     */
    private function generateCommissionDeposit(Transaction &$transaction)
    {
        $charge = $this->commissionCharge->deposit->business;
        if ($transaction->account()->type()->getType() === AccountTypeMap::PRIVATE)
            $charge = $this->commissionCharge->deposit->private;

        $transaction->commission = ($transaction->getAmount() * $charge) / 100;;
    }

    /**
     * @return mixed
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @return array
     */
    public function getCommissions(): array
    {
        return array_map(
            function (Transaction $transaction) {
                return $transaction->commission;
            }, $this->transactions
        );
    }

    /**
     * @return CurrencyService
     */
    public function getCurrencyService(): CurrencyService
    {
        return $this->currencyService;
    }



}
