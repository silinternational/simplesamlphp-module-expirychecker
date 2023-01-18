<?php

include __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use SimpleSAML\Module\expirychecker\Auth\Process\ExpiryDate;

class ExpiryTest extends TestCase
{
    const CONFIG = [
        'warnDaysBefore' => 10,
        'originalUrlParam' => 'example.org',
        'passwordChangeUrl' => 'example.org',
        'accountNameAttr' =>  'example.org',
        'expiryDateAttr' =>  'expiry_date',
        'dateFormat' => 'Y:m:d',
    ];
    
    public function testIsExpired()
    {
        $expDate = new ExpiryDate(self::CONFIG, []);

        $timestamp = time() + 1000;
        $results = $expDate->isDateInPast($timestamp);
        $this->assertFalse($results, "expected future date to not be expired.");

        $timestamp = time() - 10;
        $results = $expDate->isExpired($timestamp);
        $this->assertTrue($results, "expected past date to be expired.");
    }

    public function testIsTimeToWarn()
    {
        $expDate = new ExpiryDate(self::CONFIG, []);
        $secondsPerDay = 24*60*60;
        $timestamp = time() + 11 * $secondsPerDay;
        $results = $expDate->isTimeToWarn($timestamp, 10);
        $this->assertFalse($results, "expected distant future date to not trigger warning.");

        $timestamp = time() + 9 * $secondsPerDay;
        $results = $expDate->isTimeToWarn($timestamp, 10);
        $this->assertTrue($results, "expected near future date to trigger warning.");
    }
}
