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
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
namespace SlmGoogleAnalytics\Analytics;

use SlmGoogleAnalytics\Exception\InvalidArgumentException;

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
            throw new InvalidArgumentException(
            sprintf(
                    'Index must be of type integer, %s given', gettype($index)
            )
            );
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
            throw new InvalidArgumentException(
            sprintf(
                    'Invalid value given for scope. Acceptable values are: %s.', implode(', ', $allowed)
            )
            );
        }

        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }
}