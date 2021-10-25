<?php

namespace LaminasGoogleAnalytics\Analytics;

use LaminasGoogleAnalytics\Exception\InvalidArgumentException;

class CustomVariable
{
    const SCOPE_VISITOR    = 1;
    const SCOPE_SESSION    = 2;
    const SCOPE_PAGE_LEVEL = 3;

    protected $index;
    protected $name;
    protected $value;
    protected $scope;

    public function __construct($index, $name, $value, $scope = self::SCOPE_PAGE_LEVEL)
    {
        $this->setIndex($index);
        $this->setName($name);
        $this->setValue($value);
        $this->setScope($scope);
    }

    public function setIndex($index)
    {
        if (!is_int($index)) {
            throw new InvalidArgumentException(sprintf(
                'Index must be of type integer, %s given',
                gettype($index)
            ));
        }

        $this->index = $index;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setScope($scope)
    {
        $allowed = array(
            self::SCOPE_VISITOR,
            self::SCOPE_SESSION,
            self::SCOPE_PAGE_LEVEL
        );

        if (!in_array($scope, $allowed, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid value given for scope. Acceptable values are: %s.',
                implode(', ', $allowed)
            ));
        }

        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }
}
