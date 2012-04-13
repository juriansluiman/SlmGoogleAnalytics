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
use SlmGoogleAnalytics\Analytics\Ecommerce\Item;
use SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;

class ItemTest extends TestCase
{
    public function testCanInstantiateItem ()
    {
        $item = new Item(123, 12.50, 1);
        
        $this->assertEquals(123, $item->getSku());
        $this->assertEquals(12.50, $item->getPrice());
        $this->assertEquals(1, $item->getQuantity());
    }
    
    public function testCanAddItemToTransaction ()
    {
        $item        = new Item(123, 12.50, 1);
        $transaction = new Transaction(1, 12.50);
        $transaction->addItem($item);
        
        $items = count($transaction->items());
        $this->assertEquals(1, $items);
    }
    
    public function testCanAddItemsToTransaction ()
    {
        $item1       = new Item(123, 12.50, 1);
        $item2       = new Item(456, 22.80, 1);
        $transaction = new Transaction(1, 12.50);
        $transaction->addItem($item1);
        $transaction->addItem($item2);
        
        $items = count($transaction->items());
        $this->assertEquals(2, $items);
    }
    
    public function testCanAddSameSkuMoreThanOnce ()
    {
        $item1       = new Item(123, 12.50, 1);
        $item2       = new Item(123, 22.80, 1);
        $transaction = new Transaction(1, 12.50);
        $transaction->addItem($item1);
        $transaction->addItem($item2);
        
        $items = $transaction->items();
        $this->assertEquals(1, count($items));
        
        $item = reset($items);
        $this->assertEquals(2, $item->getQuantity());
    }
}
