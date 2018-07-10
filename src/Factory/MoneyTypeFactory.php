<?php
namespace TZ\Factory;

use Money\Currency;
use Money\Money;

class MoneyTypeFactory
{
    const CURRENCY = 'USD';
    public function getMoneyType($amount)
    {
         return new Money($amount, new Currency(self::CURRENCY));
    }
}