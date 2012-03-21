<?php

namespace SlmGoogleAnalytics;

use Zend\Module\Manager,
    Zend\Module\Consumer\AutoloaderProvider,
        
    Zend\EventManager\Event,
    Zend\EventManager\StaticEventManager,
    
    Zend\Mvc\MvcEvent;

class Module implements AutoloaderProvider
{
    public function init (Manager $manager)
    {
        $events = StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'initViewListener'));
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    /**
     * When the render event is triggered, we invoke the view helper to 
     * render the javascript code.
     * 
     * @param MvcEvent $e
     */
    public function initViewListener (Event $e)
    {
        $app = $e->getParam('application');
        $app->events()->attach('render', function (MvcEvent $e) use ($app) {
            $view   = $app->getLocator()->get('Zend\View\HelperBroker');
            $plugin = $view->load('googleAnalytics');
            $plugin();
        });
    }
}