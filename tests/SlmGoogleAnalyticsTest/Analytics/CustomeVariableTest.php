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
namespace SlmGoogleAnalyticsTest\Analytics;

use PHPUnit_Framework_TestCase as TestCase;
use SlmGoogleAnalytics\Analytics\Tracker;
use SlmGoogleAnalytics\Analytics\CustomVariable;

class CustomeVariableTest extends TestCase
{
    public function testCanInstantiateCustomeVariable()
    {
        $variable = new CustomVariable(1, 'var1', 'value1');
        
        $this->assertEquals(1, $variable->getIndex());
        $this->assertEquals('var1', $variable->getName());
        $this->assertEquals('value1', $variable->getValue());
        $this->assertEquals(CustomVariable::SCOPE_PAGE_LEVEL, $variable->getScope());
    }

    public function testCanAddCustomeVariableToTrack()
    {
        $tracker = new Tracker(123);
        $variable = new CustomVariable(1, 'var1', 'value1');
        $tracker->addCustomVariable($variable);

        $this->assertCount(1, $tracker->customVariables());
    }

    public function testCanAddMultipleCustomVariablesToTracker()
    {
        $tracker = new Tracker(123);
        $variable1 = new CustomVariable(1, 'var1', 'value1');
        $variable2 = new CustomVariable(2, 'var2', 'value2');
        $tracker->addCustomVariable($variable1);
        $tracker->addCustomVariable($variable2);

        $this->assertCount(2, $tracker->customVariables());
    }

    public function testInvalidIndex()
    {
        $this->setExpectedException('SlmGoogleAnalytics\Exception\InvalidArgumentException');
        $variable = new CustomVariable('index', 'var1', 'value1');
    }
    
    public function testScopeIndex()
    {
        $this->setExpectedException('SlmGoogleAnalytics\Exception\InvalidArgumentException');
        $variable = new CustomVariable(1, 'var1', 'value1', 'scope');
    }

    
}
