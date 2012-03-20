<?php

namespace SlmGoogleAnalytics\Analytics;

class Event
{
    protected $category;
    protected $action;
    protected $label;
    protected $value;
    
    public function __construct ($category, $action, $label = null, $value = null)
    {
        $this->category = $category;
        $this->action   = $action;
        $this->label    = $label;
        $this->value    = $value;
    }
    
    public function getCategory ()
    {
        return $this->category;
    }

    public function setCategory ($category)
    {
        $this->category = $category;
    }

    public function getAction ()
    {
        return $this->action;
    }

    public function setAction ($action)
    {
        $this->action = $action;
    }

    public function getLabel ()
    {
        return $this->label;
    }

    public function setLabel ($label)
    {
        $this->label = $label;
    }

    public function getValue ()
    {
        return $this->value;
    }

    public function setValue ($value)
    {
        $this->value = $value;
    }
}