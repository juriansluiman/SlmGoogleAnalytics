<?php

namespace LaminasGoogleAnalytics\Analytics;

use LaminasGoogleAnalytics\Analytics\Ecommerce\Transaction;
use LaminasGoogleAnalytics\Exception\InvalidArgumentException;

class Tracker
{
    protected string $id;

    protected bool  $enableTracking           = true;
    protected bool  $enablePageTracking       = true;
    protected bool  $allowLinker              = false;
    protected bool  $enableDisplayAdvertising = false;
    protected       $domainName;
    protected bool  $anonymizeIp              = false;
    protected array $customVariables          = array();
    protected array $events                   = array();
    protected array $transactions             = array();
    protected       $pageUrl;

    public function __construct($id)
    {
        $this->setId($id);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = (string) $id;
    }

    public function enabled(): bool
    {
        return $this->enableTracking;
    }

    public function setEnableTracking($enable_tracking = true): void
    {
        $this->enableTracking = (bool) $enable_tracking;
    }

    public function enabledPageTracking(): bool
    {
        return $this->enablePageTracking;
    }

    public function setEnablePageTracking($enable_page_tracking = true): void
    {
        $this->enablePageTracking = (bool) $enable_page_tracking;
    }

    public function setAllowLinker($allow_linker): void
    {
        $this->allowLinker = (bool) $allow_linker;
    }

    public function getAllowLinker(): bool
    {
        return $this->allowLinker;
    }

    public function setEnableDisplayAdvertising($enableDisplayAdvertising): void
    {
        $this->enableDisplayAdvertising = $enableDisplayAdvertising;
    }

    public function getEnableDisplayAdvertising(): bool
    {
        return $this->enableDisplayAdvertising;
    }

    public function setDomainName($domain_name): void
    {
        $this->domainName = (string) $domain_name;
    }

    public function getDomainName()
    {
        return $this->domainName;
    }

    public function setPageUrl($pageUrl): void
    {
        $this->pageUrl = $pageUrl;
    }

    public function getPageUrl()
    {
        return $this->pageUrl;
    }

    public function clearDomainName(): void
    {
        $this->domainName = null;
    }

    public function getAnonymizeIp(): bool
    {
        return $this->anonymizeIp;
    }

    public function setAnonymizeIp($flag): void
    {
        $this->anonymizeIp = (bool) $flag;
    }

    public function getCustomVariables(): array
    {
        return $this->customVariables;
    }

    public function addCustomVariable(CustomVariable $variable): void
    {
        $index = $variable->getIndex();
        if (array_key_exists($index, $this->customVariables)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot add custom variable with index %d, it already exists',
                $index
            ));
        }

        $this->customVariables[$index] = $variable;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function addEvent(Event $event): void
    {
        $this->events[] = $event;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): void
    {
        $id = $transaction->getId();
        if (array_key_exists($id, $this->transactions)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot add transaction with id %s, it already exists',
                $id
            ));
        }
        $this->transactions[$id] = $transaction;
    }
}
