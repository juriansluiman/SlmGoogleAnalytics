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
namespace SlmGoogleAnalytics\Analytics;

use SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;
use SlmGoogleAnalytics\Exception\InvalidArgumentException;

class Tracker
{
    /**
     * Web property ID for Google Analytics
     *
     * @var string
     */
    protected $id;

    /**
     * Flag if tracking is enabled or not
     *
     * By default tracking is enabled when the tracker is instantiated
     *
     * @var bool
     */
    protected $enableTracking = true;
    protected $enablePageTracking = true;
    protected $allowLinker = false;
    protected $domainName;
    protected $anonymizeIp = false;
    protected $customVariables = array();
    protected $events          = array();
    protected $transactions    = array();
    protected $pageUrl;

    public function __construct($id)
    {
        $this->setId($id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = (string) $id;
    }

    public function enabled()
    {
        return $this->enableTracking;
    }

    public function setEnableTracking($enable_tracking = true)
    {
        $this->enableTracking = (bool) $enable_tracking;
    }

    public function enabledPageTracking()
    {
        return $this->enablePageTracking;
    }

    public function setEnablePageTracking($enable_page_tracking = true)
    {
        $this->enablePageTracking = (bool) $enable_page_tracking;
    }

    public function setAllowLinker($allow_linker)
    {
        $this->allowLinker = (bool) $allow_linker;
    }

    public function getAllowLinker()
    {
        return $this->allowLinker;
    }

    public function setDomainName($domain_name)
    {
        $this->domainName = (string) $domain_name;
    }

    public function getDomainName()
    {
        return $this->domainName;
    }

    public function setPageUrl($pageUrl)
    {
        $this->pageUrl = $pageUrl;
    }

    public function getPageUrl()
    {
        return $this->pageUrl;
    }
    public function clearDomainName()
    {
        $this->domainName = null;
    }

    public function getAnonymizeIp()
    {
        return $this->anonymizeIp;
    }

    public function setAnonymizeIp($flag)
    {
        $this->anonymizeIp = (bool) $flag;
    }

    public function getCustomVariables()
    {
        return $this->customVariables;
    }

    public function addCustomVariable(CustomVariable $variable)
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

    public function getEvents()
    {
        return $this->events;
    }

    public function addEvent(Event $event)
    {
        $this->events[] = $event;
    }

    public function getTransactions()
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction)
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
