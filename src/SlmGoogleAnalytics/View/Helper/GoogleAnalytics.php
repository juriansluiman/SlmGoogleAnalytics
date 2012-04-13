<?php

namespace SlmGoogleAnalytics\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadScript;
use SlmGoogleAnalytics\Analytics\Tracker;

use SlmGoogleAnalytics\Exception\InvalidArgumentException;

class GoogleAnalytics extends AbstractHelper
{
    /**
     * @var Tracker
     */
    protected $tracker;
    
    /**
     * @var string
     */
    protected $container = 'InlineScript';
    
    /**
     * @var bool
     */
    protected $rendered = false;
    
    public function __construct (Tracker $tracker)
    {
        $this->tracker = $tracker;
    }
    
    public function getContainer ()
    {
        return $this->container;
    }
    
    public function setContainer ($container)
    {
        $this->container = $container;
    }
    
    public function __invoke ()
    {
        // Do not render the GA twice
        if  ($this->rendered) {
            return;
        }
        
        // Do not render when tracker is disabled
        $tracker = $this->tracker;
        if (!$tracker->enabled()) {
            return;
        }
        
        // We need to be sure $container->appendScript() can be called
        $container = $this->view->plugin($this->getContainer());
        if (!$container instanceof HeadScript) {
            throw new InvalidArgumentException(sprintf(
                'Container %s does not extend HeadScript view helper',
                 $this->getContainer()
            ));
        }
        
        $script  = "var _gaq = _gaq || [];\n";
        $script .= sprintf("_gaq.push(['_setAccount', '%s']);\n",
                           $tracker->getId());
        
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
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();\n
SCRIPT;

        $container->appendScript($script);
        
        // Mark this GA as rendered
        $this->rendered = true;
    }
}