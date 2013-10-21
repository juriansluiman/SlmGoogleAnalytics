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

use Zend\Json\Json;
use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\Analytics\Ecommerce\Item;
use SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;

class Analyticsjs implements ScriptInterface
{
    const DEFAULT_FUNCTION_NAME = 'ga';

    protected $tracker;
    protected $function      = self::DEFAULT_FUNCTION_NAME;
    protected $loadedPlugins = array();

    public function setTracker(Tracker $tracker)
    {
        $this->tracker = $tracker;
    }

    protected function callGa(array $params)
    {
        $jsArray         = Json::encode($params);
        $jsArrayAsParams = substr($jsArray, 1, -1);
        $output          = sprintf("\n" . '%s(%s);', $this->getFunctionName(), $jsArrayAsParams);

        return $output;
    }

    public function getCode()
    {
        // Do not render when tracker is disabled
        if (!$this->tracker->enabled()) {
            return '';
        }

        $script = $this->getLoadScript();

        $script .= $this->prepareCreate();
        $script .= $this->prepareLinker();
        $script .= $this->prepareTrackEvents();
        $script .= $this->prepareTransactions();
        $script .= $this->prepareSend();

        return $script;
    }

    public function setFunctionName($name)
    {
        $this->function = $name;
    }

    public function getFunctionName()
    {
        return $this->function;
    }

    protected function getLoadScript()
    {
        $script = <<<SCRIPT
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','%s');
SCRIPT;

        return sprintf($script, $this->getFunctionName());
    }

    protected function requirePlugin($name, $scriptName = null)
    {
        $output = '';

        if (array_search($name, $this->loadedPlugins) === false) {
            $params = array(
                'require',
                $name,
            );

            if ($scriptName !== null) {
                $params[] = $scriptName;
            }

            $output = $this->callGa($params);
        }
        return $output;
    }

    protected function prepareCreate()
    {
        $parameters = array();
        $params     = array(
            'create',
            $this->tracker->getId(),
        );


        if ($this->tracker->getAllowLinker()) {
            $parameters['allowLinker'] = true;
        }

        if ($this->tracker->getAnonymizeIp()) {
            $parameters['anonymizeIp'] = true;
        }

        if (count($parameters) > 0) {
            $params[] = $parameters;
        }

        return $this->callGa($params);
    }

    protected function prepareSend()
    {
        if (!$this->tracker->enabledPageTracking()) {
            return '';
        }

        $parameters = array();
        $params     = array(
            'send',
            'pageview',
        );

        $customVariables = $this->tracker->getCustomVariables();

        if (count($customVariables) > 0) {
            foreach ($customVariables as $customVariable) {
                $index = $customVariable->getIndex();
                $key   = 'dimension' . $index;
                $value = $customVariable->getValue();

                $parameters[$key] = $value;
            }
        }

        if (count($parameters) > 0) {
            $params[] = $parameters;
        }

        return $this->callGa($params);
    }

    protected function prepareLinker()
    {
        $domainName = $this->tracker->getDomainName();
        $output     = '';

        if ($domainName) {
            $output .= $this->requirePlugin('linker');

            $params = array(
                'linker:autoLink',
                array($domainName),
            );

            $output .= $this->callGa($params);
        }
        return $output;
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

    protected function prepareTrackEvent(\SlmGoogleAnalytics\Analytics\Event $event)
    {
        $params = array(
            'send',
            'event',
            $event->getCategory(),
            $event->getAction(),
            $event->getLabel(),
            $event->getValue(),
        );

        return $this->callGa($params);
    }

    protected function prepareTransactions()
    {
        $transactions = $this->tracker->getTransactions();
        $output       = '';

        $hasTransactions = count($transactions) > 0;

        if ($hasTransactions) {
            $output .= $this->requirePlugin('ecommerce', 'ecommerce.js');
        }

        foreach ($transactions as $transaction) {
            $output .= $this->prepareTransaction($transaction);
            $output .= $this->prepareTransactionItems($transaction);
        }

        if ($hasTransactions) {
            $output .= $this->callGa(array('ecommerce:send'));
        }
        return $output;
    }

    protected function prepareTransaction(Transaction $transaction)
    {
        $transactionParams = array(
            'id' => $transaction->getId(),
        );

        $affiliation = $transaction->getAffiliation();
        if ($affiliation !== null) {
            $transactionParams['affiliation'] = $affiliation;
        }

        $revenue = $transaction->getTotal();
        if ($revenue !== null) {
            $transactionParams['revenue'] = $revenue;
        }

        $shipping = $transaction->getItems();
        if ($shipping !== null) {
            $transactionParams['shipping'] = $shipping;
        }

        $tax = $transaction->getTax();
        if ($tax !== null) {
            $transactionParams['tax'] = $tax;
        }

        $params = array(
            'ecommerce:addTransaction',
            $transactionParams,
        );

        return $this->callGa($params);
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
        $itemParams = array(
            'id'   => $transaction->getId(),
            'name' => $item->getProduct(),
        );

        $sku = $item->getSku();
        if ($sku !== null) {
            $itemParams['sku'] = $sku;
        }

        $category = $item->getCategory();
        if ($category !== null) {
            $itemParams['category'] = $category;
        }

        $price = $item->getPrice();
        if ($price !== null) {
            $itemParams['price'] = $price;
        }

        $quantity = $item->getQuantity();
        if ($quantity !== null) {
            $itemParams['quantity'] = $quantity;
        }

        $params = array(
            'ecommerce:addItem',
            $itemParams,
        );

        return $this->callGa($params);
    }
}
