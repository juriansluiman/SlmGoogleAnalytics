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

namespace SlmGoogleAnalyticsTest\View\Helper;

use StdClass;
use PHPUnit_Framework_TestCase as TestCase;

use Zend\View\Renderer\PhpRenderer;
use SlmGoogleAnalytics\Analytics\Tracker;
use Zend\View\Helper\Placeholder\Registry as PlaceholderRegistry;
use Zend\Registry;
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
        $regKey = PlaceholderRegistry::REGISTRY_KEY;
        if (Registry::isRegistered($regKey)) {
            $registry = Registry::getInstance();
            unset($registry[$regKey]);
        }
        
        $this->tracker = new Tracker(123);
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
        $this->setExpectedException('Zend\Loader\Exception\RuntimeException');
        
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
        
        $broker = $view->getBroker();
        $broker->register('CustomViewHelper', $plugin);
        
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
