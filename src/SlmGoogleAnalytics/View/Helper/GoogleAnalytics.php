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
namespace SlmGoogleAnalytics\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadScript;
use Zend\Json\Encoder;
use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;
use SlmGoogleAnalytics\Exception\RuntimeException;

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

    public function __construct(Tracker $tracker)
    {
        $this->tracker = $tracker;
    }

    public function getContainerName()
    {
        return $this->container;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function __invoke()
    {
        // We need to be sure $container->appendScript() can be called
        $container = $this->view->plugin($this->getContainerName());
        if (!$container instanceof HeadScript) {
            throw new RuntimeException(sprintf(
                    'Container %s does not extend HeadScript view helper', $this->getContainerName()
            ));
        }

        $script = $this->getScript();

        if (empty($script)) {
            return;
        }

        $container->appendScript($script);

        // Mark this GA as rendered
        $this->rendered = true;
    }

    public function getScript()
    {
        // Do not render the GA twice
        if ($this->rendered) {
            return '';
        }

        // Do not render when tracker is disabled
        if (!$this->tracker->enabled()) {
            return '';
        }

        $script = "var _gaq = _gaq || [];\n";

        $script .= $this->prepareSetAccount();
        $script .= $this->prepareSetDomain();
        $script .= $this->prepareSetAllowLinker();
        $script .= $this->prepareAnonymizeIp();
        $script .= $this->prepareCustomVariables();
        $script .= $this->prepareEnabledPageTracking();
        $script .= $this->prepareTrackEvents();
        $script .= $this->prepareTransactions();

        $script .= $this->getLoadScript();

        return $script;
    }

    protected function getLoadScript()
    {
        return <<<SCRIPT
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();\n
SCRIPT;
    }

    protected function push($methodName, array $params = array())
    {
        array_unshift($params, '_'. $methodName);
        $jsArray = Encoder::encode($params);
        $output  = sprintf('_gaq.push(%s);' . "\n", $jsArray);

        return $output;
    }

    protected function prepareSetAccount()
    {
        return $this->push('setAccount', array($this->tracker->getId()));
    }

    protected function prepareSetDomain()
    {
        $domainName = $this->tracker->getDomainName();

        if ($domainName) {
            return $this->push('setDomainName', array($domainName));
        }
        return '';
    }

    protected function prepareSetAllowLinker()
    {
        if ($this->tracker->getAllowLinker()) {
            return $this->push('setAllowLinker', array(true));
        }
        return '';
    }

    protected function prepareAnonymizeIp()
    {
        if ($this->tracker->getAnonymizeIp()) {
            return $this->push('gat._anonymizeIp');
        }
    }

    protected function prepareCustomVariables()
    {
        $customVariables = $this->tracker->getCustomVariables();
        $output          = '';

        foreach ($customVariables as $variable) {
            $output .= $this->push('setCustomVar', array(
                $variable->getIndex(),
                $variable->getName(),
                $variable->getValue(),
                $variable->getScope(),
            ));
        }
        return $output;
    }

    protected function prepareEnabledPageTracking()
    {
        if ($this->tracker->enabledPageTracking()) {
            return $this->push('trackPageview');
        }
        return '';
    }

    protected function prepareTrackEvents()
    {
        $events = $this->tracker->getEvents();
        $output = '';

        foreach ($events as $event) {
            $output .= $this->push('trackEvent', array(
                $event->getCategory(),
                $event->getAction(),
                $event->getLabel(),
                $event->getValue(),
            ));
        }
        return $output;
    }

    protected function prepareTransactions()
    {
        $transactions = $this->tracker->getTransactions();
        $output       = '';

        foreach ($transactions as $transaction) {
            $output .= $this->push('addTrans', array(
                $transaction->getId(),
                $transaction->getAffiliation(),
                $transaction->getTotal(),
                $transaction->getTax(),
                $transaction->getShipping(),
                $transaction->getCity(),
                $transaction->getState(),
                $transaction->getCountry(),
            ));

            $output .= $this->prepareTransactionItems($transaction);
        }
        $output .= $this->push('trackTrans');

        return $output;
    }

    protected function prepareTransactionItems(Transaction $transaction)
    {
        $output = '';
        $items  = $transaction->getItems();

        foreach ($items as $item) {
            $output .= $this->push('addItem', array(
                $transaction->getId(),
                $item->getSku(),
                $item->getProduct(),
                $item->getCategory(),
                $item->getPrice(),
                $item->getQuantity(),
            ));
        }
        return $output;
    }
}
