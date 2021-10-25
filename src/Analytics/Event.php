<?php

namespace LaminasGoogleAnalytics\Analytics;

class Event
{
    protected $category;
    protected $action;
    protected $label;
    protected $value;

    public function __construct($category, $action, $label = null, $value = null)
    {
        $this->setCategory($category);
        $this->setAction($action);
        $this->setLabel($label);
        $this->setValue($value);
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category): void
    {
        $this->category = $category;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action): void
    {
        $this->action = $action;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label): void
    {
        $this->label = $label;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }
}
