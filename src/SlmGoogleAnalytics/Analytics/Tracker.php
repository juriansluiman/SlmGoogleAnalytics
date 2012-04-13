<?php

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
    
    protected $events;
    protected $transactions;
    
    public function __construct ($id)
    {
        $this->setId($id);
    }
    
    public function getId ()
    {
        return $this->id;
    }
    
    public function setId ($id)
    {
        $this->id = $id;
    }
    
    public function enabled ()
    {
        return $this->enableTracking;
    }
    
    public function setEnableTracking ($enable_tracking = true)
    {
        $this->enableTracking = (bool) $enable_tracking;
    }
    
    public function enabledPageTracking ()
    {
        return $this->enablePageTracking;
    }
    
    public function setEnablePageTracking ($enable_page_tracking = true)
    {
        $this->enablePageTracking = (bool) $enable_page_tracking;
    }
    
    public function events ()
    {
        return $this->events;
    }
    
    public function addEvent (Event $event)
    {
        if (null === $this->events) {
            $this->events = array();
        }
        
        $this->events[] = $event;
    }
    
    public function transactions ()
    {
        return $this->transactions;
    }
    
    public function addTransaction (Transaction $transaction)
    {
        if (null === $this->transactions) {
            $this->transactions = array();
        }
        
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