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
namespace SlmGoogleAnalytics;

use Zend\EventManager\EventInterface;
use Zend\Http\Request as HttpRequest;
use Zend\ModuleManager\Feature;
use Zend\Mvc\MvcEvent;
use SlmGoogleAnalytics\Analytics;
use SlmGoogleAnalytics\View\Helper;

class Module implements
Feature\AutoloaderProviderInterface, Feature\ConfigProviderInterface, Feature\ViewHelperProviderInterface, Feature\ServiceProviderInterface, Feature\BootstrapListenerInterface
{

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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'googleAnalytics' => function($sm) {
                    $script = $sm->getServiceLocator()->get('google-analytics-script');
                    $helper = new Helper\GoogleAnalytics($script);

                    return $helper;
                },
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases'    => array(
                'google-analytics' => 'SlmGoogleAnalytics\Analytics\Tracker',
            ),
            'invokables' => array(
                'google-analytics-script-analytics-js' => 'SlmGoogleAnalytics\View\Helper\Script\Analyticsjs',
                'google-analytics-script-ga-js'        => 'SlmGoogleAnalytics\View\Helper\Script\Gajs',
            ),
            'factories'  => array(
                'SlmGoogleAnalytics\Analytics\Tracker' => function($sm) {
                    $config = $sm->get('config');
                    $config = $config['google_analytics'];

                    $tracker = new Analytics\Tracker($config['id']);

                    if (isset($config['domain_name'])) {
                        $tracker->setDomainName($config['domain_name']);
                    }

                    if (isset($config['allow_linker'])) {
                        $tracker->setAllowLinker($config['allow_linker']);
                    }

                    if (true === $config['anonymize_ip']) {
                        $tracker->setAnonymizeIp(true);
                    }

                    if (false === $config['enable']) {
                        $tracker->setEnableTracking(false);
                    }

                    return $tracker;
                },
                'google-analytics-script' => function($sm) {
                    $config     = $sm->get('config');
                    $scriptName = $config['google_analytics']['script'];

                    $script = $sm->get($scriptName);
                    $ga     = $sm->get('google-analytics');

                    $script->setTracker($ga);

                    return $script;
                },
            ),
        );
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
