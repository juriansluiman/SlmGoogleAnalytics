<?php

namespace SlmGoogleAnalytics\View\Helper;

use Zend\View\Helper\AbstractHelper,
    Zend\View\Helper\HeadScript,
    SlmGoogleAnalytics\Analytics\Tracker;

class GoogleAnalytics extends AbstractHelper
{
    /**
     * @var Tracker
     */
    protected $tracker;
    
    protected $container = 'InlineScript';
    
    public function __construct (Tracker $tracker)
    {
        $this->tracker = $tracker;
    }
    
    public function setViewContainer ($container)
    {
        $this->container = $container;
    }
    
    public function __invoke ()
    {
        $tracker = $this->tracker;
        if (!$tracker->enabled()) {
            return;
        }
        
        /**
         * We can use a HeadScript or InlineScript container or any other class
         * based on the HeadScript view helper.
         */
        $container = $this->view->getHelper($this->container);
        if (!$container instanceof HeadScript) {
            return;
        }
        
        $script  = "var _gaq = _gaq || [];\n";
        $script .= "_gaq.push(['_setAccount', '{$this->_id}']);\n";
        
        if ($tracker->enabledPageTracking()) {
            $script .= "_gaq.push(['_trackPageview']);\n";
        }
        
        if (null !== ($events = $tracker->events())) {
            foreach ($events as $event) {
                $script .= sprintf("_gaq.push(['_trackEvent', '%s', '%s', '%s', '%s']);\n",
                                   $event->getCategory(),
                                   $event->getAction(),
                                   $event->getLabel() ?: '',
                                   $event->getValue() ?: '');
            }
        }
        
        if (null !== ($transactions = $tracker->transactions())) {
            foreach ($transactions as $transaction) {
                $script .= sprintf("_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);\n",
                                   $transaction->getId(),
                                   $transaction->getAffiliation() ?: '',
                                   $transaction->getTotal(),
                                   $transaction->getTax() ?: '',
                                   $transaction->getShipping() ?: '',
                                   $transaction->getCity() ?: '',
                                   $transaction->getState() ?: '',
                                   $transaction->getCountry() ?: '');
                
                if (null !== ($items = $transaction->items())) {
                    foreach ($items as $item) {
                        $script .= sprintf("_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);\n",
                                           $transaction->getId(),
                                           $item->getSku() ?: '',
                                           $item->getProduct() ?: '',
                                           $item->getCategory() ?: '',
                                           $item->getPrice(),
                                           $item->getQuantity());
                    }
                }
            }
            
            $script .= "_gaq.push(['_trackTrans']);";
        }
        
        $script .= <<<SCRIPT
"(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();\n";
SCRIPT;

        $container->appendScript($script);
    }
}