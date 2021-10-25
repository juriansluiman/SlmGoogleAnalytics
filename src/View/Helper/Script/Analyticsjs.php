<?php

namespace LaminasGoogleAnalytics\View\Helper\Script;

use Laminas\Json\Json;
use LaminasGoogleAnalytics\Analytics\Event;
use LaminasGoogleAnalytics\Analytics\Tracker;
use LaminasGoogleAnalytics\Analytics\Ecommerce\Item;
use LaminasGoogleAnalytics\Analytics\Ecommerce\Transaction;

class Analyticsjs implements ScriptInterface
{
    public const DEFAULT_FUNCTION_NAME = 'ga';

    protected        $tracker;
    protected string $function      = self::DEFAULT_FUNCTION_NAME;
    protected array  $loadedPlugins = array();

    public function setTracker(Tracker $tracker)
    {
        $this->tracker = $tracker;
    }

    protected function callGa(array $params): string
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
            return;
        }

        $script = $this->getLoadScript();

        $script .= $this->prepareCreate();
        $script .= $this->prepareLinker();
        $script .= $this->prepareDisplayAdvertising();
        $script .= $this->prepareTrackEvents();
        $script .= $this->prepareTransactions();
        $script .= $this->prepareSend();

        return $script;
    }

    public function setFunctionName($name): void
    {
        $this->function = $name;
    }

    public function getFunctionName(): string
    {
        return $this->function;
    }

    protected function getLoadScript(): string
    {
        $script = <<<SCRIPT
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','%s');
SCRIPT;

        return sprintf($script, $this->getFunctionName());
    }

    protected function requirePlugin($name, $scriptName = null): string
    {
        $output = '';

        if (isset($this->loadedPlugins[$name]) === false) {
            $params = array(
                'require',
                $name,
            );

            if ($scriptName !== null) {
                $params[] = $scriptName;
            }

            $output = $this->callGa($params);
            $this->loadedPlugins[$name] = true;
        }
        return $output;
    }

    protected function prepareCreate(): string
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

    protected function prepareSend(): string
    {
        if (!$this->tracker->enabledPageTracking()) {
            return '';
        }

        $parameters = array();
        $params     = array(
            'send',
            'pageview',
        );

        $pageUrl = $this->tracker->getPageUrl();

        if ($pageUrl !== null) {
            $parameters['page'] = $pageUrl;
        }

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

    protected function prepareLinker(): string
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

    protected function prepareDisplayAdvertising(): string
    {
        $output = '';

        if ($this->tracker->getEnableDisplayAdvertising()) {
            $output .= $this->requirePlugin('displayfeatures');
        }

        return $output;
    }

    protected function prepareTrackEvents(): string
    {
        $events = $this->tracker->getEvents();
        $output = '';

        foreach ($events as $event) {
            $output .= $this->prepareTrackEvent($event);
        }
        return $output;
    }

    protected function prepareTrackEvent(Event $event): string
    {
        $params = array(
            'send',
            'event',
            $event->getCategory(),
            $event->getAction(),
        );

        $label = $event->getLabel();

        if ($label !== null) {
            $params[] = $label;
            $value = $event->getValue();

            if ($value !== null) {
                $params[] = $value;
            }
        }


        return $this->callGa($params);
    }

    protected function prepareTransactions(): string
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

    protected function prepareTransaction(Transaction $transaction): string
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

        $shipping = $transaction->getShipping();
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

    protected function prepareTransactionItems(Transaction $transaction): string
    {
        $output = '';
        $items  = $transaction->getItems();

        foreach ($items as $item) {
            $output .= $this->prepareTransactionItem($transaction, $item);
        }
        return $output;
    }

    protected function prepareTransactionItem(Transaction $transaction, Item $item): string
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
