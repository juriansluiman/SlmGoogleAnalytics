<?php
/**
 * Copyright (c) 2012-2013 Jurian Sluiman.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Jurian Sluiman <jurian@juriansluiman.nl>
 * @copyright   2012-2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://juriansluiman.nl
 */
namespace LaminasGoogleAnalyticsTest\View\Helper\Script;

use PHPUnit_Framework_TestCase as TestCase;

use LaminasGoogleAnalytics\Analytics\CustomVariable;
use LaminasGoogleAnalytics\Analytics\Ecommerce\Transaction;
use LaminasGoogleAnalytics\Analytics\Ecommerce\Item;
use LaminasGoogleAnalytics\Analytics\Event;
use LaminasGoogleAnalytics\Analytics\Tracker;
use LaminasGoogleAnalytics\View\Helper\Script\Analyticsjs;

class AnalyticsjsTest extends TestCase
{
    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * @var Script\Gajs
     */
    protected $script;

    public function setUp()
    {
        $tracker = new Tracker(123);
        $script  = new Analyticsjs();
        $script->setTracker($tracker);

        $this->tracker = $tracker;
        $this->script  = $script;
    }

    public function tearDown()
    {
        unset($this->tracker);
        unset($this->script);
    }

    public function testHelperRendersAccountId()
    {
        $expected = 'ga("create","123");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperTracksPagesByDefault()
    {
        $expected = 'ga("send","pageview");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperReturnsNullWithDisabledTracker()
    {
        $this->tracker->setEnableTracking(false);

        $actual = $this->script->getCode();
        $this->assertNull($actual);
    }

    public function testHelperRendersNoPagesWithPageTrackingOff()
    {
        $this->tracker->setEnablePageTracking(false);

        $needle = 'ga("send","pageview");';
        $actual = $this->script->getCode();
        $this->assertNotEmpty($actual);
        $this->assertNotContains($needle, $actual);
    }

    public function testHelperLoadsFileFromGoogle()
    {
        $expected = <<<SCRIPT
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
SCRIPT;

        $actual = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersDisplayFeaturesAdvertising()
    {
        $this->tracker->setEnableDisplayAdvertising(true);

        $expected = 'ga("require","displayfeatures");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersDomainName()
    {
        $this->tracker->setDomainName('foobar');

        $expected = 'ga("require","linker");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);

        $expected = 'ga("linker:autoLink",["foobar"]);';
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersAllowLinker()
    {
        $this->tracker->setAllowLinker(true);

        $expected = 'ga("create","123",{"allowLinker":true})';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersAnonymizeIp()
    {
        $this->tracker->setAnonymizeIp(true);

        $expected = 'ga("create","123",{"anonymizeIp":true});';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperOmitsAnonymipzeIpOnFalse()
    {
        $expected = 'ga("create","123",{"anonymizeIp":true});';
        $actual   = $this->script->getCode();
        $this->assertNotContains($expected, $actual);

        $expected = 'ga("create","123");';
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersCustomVariables()
    {
        $variable = new CustomVariable(1, 'var1', 'value1');
        $this->tracker->addCustomVariable($variable);

        $expected = 'ga("send","pageview",{"dimension1":"value1"});';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    /**
     * @todo Is this a bug? Is the 2nd argument ignored?
     */
    public function testHelperRendersMultipleCustomVariables()
    {
        $variable1 = new CustomVariable(1, 'var1', 'value1');
        $variable2 = new CustomVariable(2, 'var2', 'value2');

        $this->tracker->addCustomVariable($variable1);
        $this->tracker->addCustomVariable($variable2);

        $expected = 'ga("send","pageview",{"dimension1":"value1","dimension2":"value2"});';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersEvent()
    {
        $event = new Event('Category', 'Action', 'Label', 'Value');
        $this->tracker->addEvent($event);

        $expected = 'ga("send","event","Category","Action","Label","Value");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersMultipleEvents()
    {
        $fooEvent = new Event('CategoryFoo', 'ActionFoo', 'LabelFoo', 'ValueFoo');
        $barEvent = new Event('CategoryBar', 'ActionBar', 'LabelBar', 'ValueBar');

        $this->tracker->addEvent($fooEvent);
        $this->tracker->addEvent($barEvent);

        $expected = 'ga("send","event","CategoryFoo","ActionFoo","LabelFoo","ValueFoo");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);

        $expected = 'ga("send","event","CategoryBar","ActionBar","LabelBar","ValueBar");';
        $this->assertContains($expected, $actual);
    }

    /**
     * @todo  This is a bug; "Value" is removed from the output
     */
    public function testHelperRendersEmptyLabelAsEmptyString()
    {
        $event = new Event('Category', 'Action', null, 'Value');
        $this->tracker->addEvent($event);

        $expected = 'ga("send","event","Category","Action");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersEmptyValueAsEmptyString()
    {
        $event = new Event('Category', 'Action', 'Label');
        $this->tracker->addEvent($event);

        $expected = 'ga("send","event","Category","Action","Label");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersEmptyValueAndLabelAsEmptyStrings()
    {
        $event = new Event('Category', 'Action');
        $this->tracker->addEvent($event);

        $expected = 'ga("send","event","Category","Action");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersTransaction()
    {
        $transaction = new Transaction(123, 12.55);
        $this->tracker->addTransaction($transaction);

        $expected = 'ga("require","ecommerce","ecommerce.js");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);

        $expected = 'ga("ecommerce:addTransaction",{"id":123,"revenue":12.55});';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersTransactionTracking()
    {
        $transaction = new Transaction(123, 12.55);
        $this->tracker->addTransaction($transaction);

        $expected = 'ga("ecommerce:send");';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersTransactionWithAdditionalValues()
    {
        $transaction = new Transaction(123, 12.55);
        $transaction->setAffiliation('Affiliation');
        $transaction->setTax(9.66);
        $transaction->setShipping(3.22);

        $this->tracker->addTransaction($transaction);

        $expected = 'ga("ecommerce:addTransaction",{"id":123,"affiliation":"Affiliation","revenue":12.55,"shipping":3.22,"tax":9.66});';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersTransactionItem()
    {
        $transaction = new Transaction(123, 12.55);
        $item        = new Item(456, 9.66, 1, 'Product');
        $transaction->addItem($item);

        $this->tracker->addTransaction($transaction);

        $expected = 'ga("ecommerce:addItem",{"id":123,"name":"Product","sku":456,"price":9.66,"quantity":1});';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersTransactionItemWithAdditionalValues()
    {
        $transaction = new Transaction(123, 12.55);
        $item        = new Item(456, 9.66, 1, 'Product', 'Category');
        $transaction->addItem($item);

        $this->tracker->addTransaction($transaction);

        $expected = 'ga("ecommerce:addItem",{"id":123,"name":"Product","sku":456,"category":"Category","price":9.66,"quantity":1});';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);
    }

    public function testHelperRendersTransactionWithMultipleItems()
    {
        $transaction = new Transaction(123, 12.55);
        $item1       = new Item(456, 9.66, 1, 'Product1', 'Category1');
        $item2       = new Item(789, 15.33, 2, 'Product2', 'Category2');
        $transaction->addItem($item1);
        $transaction->addItem($item2);

        $this->tracker->addTransaction($transaction);

        $expected = 'ga("ecommerce:addItem",{"id":123,"name":"Product1","sku":456,"category":"Category1","price":9.66,"quantity":1});';
        $actual   = $this->script->getCode();
        $this->assertContains($expected, $actual);

        $expected = 'ga("ecommerce:addItem",{"id":123,"name":"Product2","sku":789,"category":"Category2","price":15.33,"quantity":2});';
        $this->assertContains($expected, $actual);
    }
}
