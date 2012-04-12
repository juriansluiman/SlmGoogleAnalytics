<?php

/** 
 * This is free and unencumbered software released into the public domain.
 * 
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any
 * means.
 * 
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * 
 * For more information, please refer to <http://unlicense.org/>
 * 
 * @category   SlmGoogleAnalytics
 * @copyright  Copyright (c) 2012 Jurian Sluiman <jurian@juriansluiman.nl>
 * @license    http://unlicense.org Unlicense
 */

namespace SlmGoogleAnalyticsTest\Analytics\Ecommerce;

use PHPUnit_Framework_TestCase as TestCase;
use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;

class TransactionTest extends TestCase
{
    public function testCanInstantiateTransaction ()
    {
        $transaction = new Transaction(123, 12.50);

        $this->assertEquals(123, $transaction->getId());
        $this->assertEquals(12.50, $transaction->getTotal());
    }
    
    public function testCanAddTransactionToTracker ()
    {
        $tracker     = new Tracker(123);
        $transaction = new Transaction(123, 12.50);
        $tracker->addTransaction($transaction);
        
        $transactions = count($tracker->transactions());
        $this->assertEquals(1, $transactions);
    }
    
    public function testCanAddMultipleTransactionsToTracker ()
    {
        $tracker      = new Tracker(123);
        $transaction1 = new Transaction(123, 12.50);
        $transaction2 = new Transaction(456, 12.50);
        $tracker->addTransaction($transaction1);
        $tracker->addTransaction($transaction2);
        
        $transactions = count($tracker->transactions());
        $this->assertEquals(2, $transactions);
    }
    
    public function testCannotAddTransactionsWithSameId ()
    {
        $this->setExpectedException('SlmGoogleAnalytics\Exception\InvalidArgumentException');
        
        $tracker      = new Tracker(123);
        $transaction1 = new Transaction(456, 12.50);
        $transaction2 = new Transaction(456, 12.50);
        
        $tracker->addTransaction($transaction1);
        $tracker->addTransaction($transaction2);
    }
}
