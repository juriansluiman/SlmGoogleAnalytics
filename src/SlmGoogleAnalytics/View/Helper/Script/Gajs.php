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
 * @author      Witold Wasiczko <witold@wasiczko.pl>
 * @copyright   2012-2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.psd2html.pl
 */
namespace SlmGoogleAnalytics\View\Helper\Script;

use Zend\Json\Encoder;
use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\Analytics\CustomVariable;
use SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;
use SlmGoogleAnalytics\Analytics\Ecommerce\Item;
use SlmGoogleAnalytics\Analytics\Event;

class Gajs implements ScriptInterface
{
    const METHOD_PREFIX = '_';

    protected $tracker;

    public function setTracker(Tracker $tracker)
    {
        $this->tracker = $tracker;
    }

    public function getCode()
    {
        // Do not render when tracker is disabled
        if (!$this->tracker->enabled()) {
            return;
        }

        $script = $this->getVarCreate();

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
        $script = 'google-analytics.com/ga.js';
        $httpsOrHttp = "'https://ssl.' : 'http://www.'";
        if (true === $this->tracker->getEnableDisplayAdvertising()) {
            $script = 'stats.g.doubleclick.net/dc.js';
            $httpsOrHttp = "'https://' : 'http://'";
        }

        return <<<SCRIPT
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? $httpsOrHttp) + '$script';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();\n
SCRIPT;
    }

    protected function getVarCreate()
    {
        return 'var _gaq = _gaq || [];'."\n";
    }

    protected function push($methodName, array $params = array())
    {
        array_unshift($params, self::METHOD_PREFIX . $methodName);
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
        return '';
    }

    protected function prepareCustomVariables()
    {
        $customVariables = $this->tracker->getCustomVariables();
        $output          = '';

        foreach ($customVariables as $variable) {
            $output .= $this->prepareCustomVariable($variable);
        }
        return $output;
    }

    protected function prepareCustomVariable(CustomVariable $customVariable)
    {
        $data = array(
            $customVariable->getIndex(),
            $customVariable->getName(),
            $customVariable->getValue(),
            $customVariable->getScope(),
        );

        return $this->push('setCustomVar', $data);
    }

    protected function prepareEnabledPageTracking()
    {
        if ($this->tracker->enabledPageTracking()) {
            $pageUrl = $this->tracker->getPageUrl();
            if ($pageUrl !== null) {
                return $this->push('trackPageview', array($pageUrl));
            } else {
                return $this->push('trackPageview');
            }
        }
        return '';
    }

    protected function prepareTrackEvents()
    {
        $events = $this->tracker->getEvents();
        $output = '';

        foreach ($events as $event) {
            $output .= $this->prepareTrackEvent($event);
        }
        return $output;
    }

    protected function prepareTrackEvent(Event $event)
    {
        return $this->push('trackEvent', array(
            $event->getCategory(),
            $event->getAction(),
            $event->getLabel(),
            $event->getValue(),
        ));
    }

    protected function prepareTransactions()
    {
        $transactions = $this->tracker->getTransactions();
        $output       = '';

        foreach ($transactions as $transaction) {
            $output .= $this->prepareTransaction($transaction);
        }
        if ($output !== '') {
            $output .= $this->push('trackTrans');
        }
        return $output;
    }

    protected function prepareTransaction(Transaction $transaction)
    {
        return $this->push('addTrans', array(
            $transaction->getId(),
            $transaction->getAffiliation(),
            $transaction->getTotal(),
            $transaction->getTax(),
            $transaction->getShipping(),
            $transaction->getCity(),
            $transaction->getState(),
            $transaction->getCountry(),
        )) . $this->prepareTransactionItems($transaction);
    }

    protected function prepareTransactionItems(Transaction $transaction)
    {
        $output = '';
        $items  = $transaction->getItems();

        foreach ($items as $item) {
            $output .= $this->prepareTransactionItem($transaction, $item);
        }
        return $output;
    }

    protected function prepareTransactionItem(Transaction $transaction, Item $item)
    {
        return $this->push('addItem', array(
            $transaction->getId(),
            $item->getSku(),
            $item->getProduct(),
            $item->getCategory(),
            $item->getPrice(),
            $item->getQuantity(),
        ));
    }
}
