<?php
/**
 * Copyright (c) 2012 Jurian Sluiman.
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
 * @package     SlmGoogleAnalytics
 * @author      Jurian Sluiman <jurian@juriansluiman.nl>
 * @copyright   2012 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://juriansluiman.nl
 */
namespace SlmGoogleAnalyticsTest\View\Helper;

use StdClass;
use PHPUnit_Framework_TestCase as TestCase;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Helper\Placeholder\Registry as PlaceholderRegistry;
use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\View\Helper\GoogleAnalytics as Helper;

use SlmGoogleAnalyticsTest\View\Helper\TestAsset\CustomViewHelper;
use SlmGoogleAnalytics\Analytics\Event;
use SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;
use SlmGoogleAnalytics\Analytics\Ecommerce\Item;

class GoogleAnalyticsTest extends TestCase
{
    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * @var Helper
     */
    protected $helper;

    public function setUp ()
    {
        PlaceholderRegistry::unsetRegistry();

        $this->tracker = new Tracker(123);
        $this->tracker->setAllowLinker(true);
        $this->helper  = new Helper($this->tracker);

        $view = new PhpRenderer;
        $this->helper->setView($view);
    }

    public function tearDown ()
    {
        unset($this->tracker);
        unset($this->helper);
    }

    public function testHelperRendersAccountId ()
    {
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_setAccount', '123'])", $output);
    }

    public function testHelperTracksPagesByDefault ()
    {
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_trackPageview'])", $output);
    }

    public function testHelperReturnsNullWithDisabledTracker ()
    {
        $this->tracker->setEnableTracking(false);
        $helper = $this->helper;
        $return = $helper();

        $this->assertEquals(null, $return);
    }

    public function testHelperThrowsExceptionWithNonExistingContainer ()
    {
        $this->setExpectedException('Zend\ServiceManager\Exception\ServiceNotFoundException');

        $this->helper->setContainer('NonExistingViewHelper');
        $helper = $this->helper;
        $helper();
    }

    public function testHelperThrowsExceptionWithContainerNotInheritedFromHeadscript ()
    {
        $this->setExpectedException('SlmGoogleAnalytics\Exception\RuntimeException');

        $view   = $this->helper->getView();
        $plugin = new CustomViewHelper;
        $plugin->setView($view);

        $broker = $view->getHelperPluginManager();
        $broker->setService('CustomViewHelper', $plugin);

        $this->helper->setContainer('CustomViewHelper');
        $helper = $this->helper;
        $helper();
    }

    public function testHelperRendersNoPagesWithPageTrackingOff ()
    {
        $this->tracker->setEnablePageTracking(false);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertGreaterThan(0, strlen($output));
        $this->assertFalse(strpos($output, "_gaq.push(['_trackPageview'])"));
    }

    public function testHelperLoadsFileFromGoogle ()
    {
        $helper = $this->helper;
        $helper();

        $script = <<<SCRIPT
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();\n
SCRIPT;

        $output = $this->getOutput($this->helper);
        $this->assertContains($script, $output);
    }

    public function testHelperDoesNotRenderTwice ()
    {
        $helper = $this->helper;
        $helper();
        $output1 = $this->getOutput($this->helper);
        $helper();
        $output2 = $this->getOutput($this->helper);

        $this->assertEquals($output1, $output2);
    }

    public function testHelperRendersDomainName ()
    {
        $this->tracker->setDomainName('foobar');
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_setDomainName', 'foobar'])", $output);
    }

    public function testHelperRendersAllowLinker ()
    {
        $this->tracker->setAllowLinker(true);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_setAllowLinker', true])", $output);
    }

    public function testHelperRendersEvent ()
    {
        $event = new Event('Category', 'Action', 'Label', 'Value');

        $this->tracker->addEvent($event);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_trackEvent', 'Category', 'Action', 'Label', 'Value'])", $output);
    }

    public function testHelperRendersMultipleEvents ()
    {
        $fooEvent = new Event('CategoryFoo', 'ActionFoo', 'LabelFoo', 'ValueFoo');
        $barEvent = new Event('CategoryBar', 'ActionBar', 'LabelBar', 'ValueBar');

        $this->tracker->addEvent($fooEvent);
        $this->tracker->addEvent($barEvent);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_trackEvent', 'CategoryFoo', 'ActionFoo', 'LabelFoo', 'ValueFoo'])", $output);
        $this->assertContains("_gaq.push(['_trackEvent', 'CategoryBar', 'ActionBar', 'LabelBar', 'ValueBar'])", $output);
    }

    public function testHelperRendersEmptyLabelAsEmptyString ()
    {
        $event = new Event('Category', 'Action', null, 'Value');

        $this->tracker->addEvent($event);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_trackEvent', 'Category', 'Action', '', 'Value'])", $output);
    }

    public function testHelperRendersEmptyValueAsEmptyString ()
    {
        $event = new Event('Category', 'Action', 'Label');

        $this->tracker->addEvent($event);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_trackEvent', 'Category', 'Action', 'Label', ''])", $output);
    }

    public function testHelperRendersEmptyValueAndLabelAsEmptyStrings ()
    {
        $event = new Event('Category', 'Action');

        $this->tracker->addEvent($event);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_trackEvent', 'Category', 'Action', '', ''])", $output);
    }

    public function testHelperRendersTransaction ()
    {
        $transaction = new Transaction(123, 12.55);

        $transaction->setAffiliation('Affiliation');
        $transaction->setTax(9.66);
        $transaction->setShipping(3.22);

        $transaction->setCity('City');
        $transaction->setState('State');
        $transaction->setCountry('Country');

        $this->tracker->addTransaction($transaction);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_addTrans', '123', 'Affiliation', '12.55', '9.66', '3.22', 'City', 'State', 'Country'])", $output);
    }

    public function testHelperRendersTransactionTracking ()
    {
        $transaction = new Transaction(123, 12.55);

        $this->tracker->addTransaction($transaction);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_trackTrans'])", $output);
    }

    public function testHelperRendersTransactionWithOptionalValuesEmpty ()
    {
        $transaction = new Transaction(123, 12.55);

        $this->tracker->addTransaction($transaction);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_addTrans', '123', '', '12.55', '', '', '', '', ''])", $output);
    }

    public function testHelperRendersTransactionItem ()
    {
        $transaction = new Transaction(123, 12.55);
        $item        = new Item(456, 9.66, 1, 'Product', 'Category');
        $transaction->addItem($item);

        $this->tracker->addTransaction($transaction);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_addItem', '123', '456', 'Product', 'Category', '9.66', '1'])", $output);
    }

    public function testHelperRendersTransactionWithMultipleItems ()
    {
        $transaction = new Transaction(123, 12.55);
        $item1       = new Item(456, 9.66, 1, 'Product1', 'Category1');
        $item2       = new Item(789, 15.33, 2, 'Product2', 'Category2');
        $transaction->addItem($item1);
        $transaction->addItem($item2);

        $this->tracker->addTransaction($transaction);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_addItem', '123', '456', 'Product1', 'Category1', '9.66', '1'])", $output);
        $this->assertContains("_gaq.push(['_addItem', '123', '789', 'Product2', 'Category2', '15.33', '2'])", $output);
    }

    public function testHelperRendersItemWithOptionalValuesEmpty ()
    {
        $transaction = new Transaction(123, 12.55);
        $item        = new Item(456, 9.66, 1);
        $transaction->addItem($item);

        $this->tracker->addTransaction($transaction);
        $helper = $this->helper;
        $helper();

        $output = $this->getOutput($this->helper);
        $this->assertContains("_gaq.push(['_addItem', '123', '456', '', '', '9.66', '1'])", $output);
    }

    protected function getOutput (Helper $helper)
    {
        $container = $helper->getContainer();
        $container = $helper->getView()->plugin($container);

        return $container->toString();
    }
}
