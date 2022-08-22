<?php


namespace Services;


use Exceptions\CurrencyException;
use Utils\Request;

class CurrencyService
{
    private $rates, $baseCurrency, $currencies;
    private $apiBaseUrl, $accessKey;

    public function __construct($currencies)
    {
        $this->currencies = $currencies;
        $this->currencies[] = 'EUR';

        $this->apiBaseUrl = 'http://api.exchangeratesapi.io/v1';
        $this->accessKey = '374c29fa9d2984c45b8abea52445230d';
        $this->loadLatestRates();
    }

    /**
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @param int $precision
     * @return array
     * @throws CurrencyException
     */
    public function convert(string $fromCurrency, string $toCurrency, float $amount, int $precision = 2)
    {
        if (!isset($this->rates[$fromCurrency]) || !isset($this->rates[$toCurrency]) || !isset($this->rates[$this->baseCurrency]))
            throw new CurrencyException('Invalid Currency to convert!');

        if ($fromCurrency != $this->baseCurrency)
            $amount = $amount / $this->rates[$fromCurrency];

        return [
            'amount' => round($amount * $this->rates[$toCurrency], $precision),
            'currency' => $toCurrency
        ];
    }

    /**
     * load latest currency exchange rates
     */
    private function loadLatestRates()
    {
        $response = (new Request(['base_uri' => $this->apiBaseUrl]))->get('latest', [
            'access_key' => $this->accessKey,
            'symbols' => implode(',', array_unique($this->currencies))
        ]);

        if (!empty($response['data']) && $response['data']['success']) {
            $this->baseCurrency = $response['data']['base'] ?? [];
            $this->rates = $response['data']['rates'] ?? [];
        }
    }

    /**
     * @return mixed
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    /**
     * for automation test
     * @param mixed $rates
     */
    public function setRates($rates): void
    {
        $this->rates = $rates;
    }

}
