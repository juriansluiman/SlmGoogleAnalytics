<?php

namespace LaminasGoogleAnalytics;

use Laminas\EventManager\EventInterface;
use Laminas\Http\Request as HttpRequest;
use Laminas\ModuleManager\Feature;
use Laminas\Mvc\MvcEvent;

final class Module implements
    Feature\ConfigProviderInterface,
    Feature\BootstrapListenerInterface
{

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * When the render event is triggered, we invoke the view helper to
     * render the javascript code.
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(EventInterface $e)
    {
        $app = $e->getParam('application');

        if (!$app->getRequest() instanceof HttpRequest) {
            return;
        }

        $sm = $app->getServiceManager();
        $em = $app->getEventManager();

        $em->attach(MvcEvent::EVENT_RENDER, function(MvcEvent $e) use ($sm) {
            $view   = $sm->get('ViewHelperManager');
            $plugin = $view->get('googleAnalytics');
            $plugin->appendScript();
        });
    }
}
