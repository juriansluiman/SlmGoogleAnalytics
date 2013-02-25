<?php
/**
 * Copyright (c) 2012 Jurian Sluiman.
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
 * @package     SlmGoogleAnalytics
 * @author      Jurian Sluiman <jurian@juriansluiman.nl>
 * @copyright   2012 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://juriansluiman.nl
 */
namespace SlmGoogleAnalyticsTest\Analytics;

use PHPUnit_Framework_TestCase as TestCase;
use SlmGoogleAnalytics\Analytics\Tracker;

class TrackerTest extends TestCase
{
    public function testCanInstantiateTracker ()
    {
        $tracker = new Tracker(
            123,
            new \Zend\Session\Container('google_analytics_storage')
        );
        $this->assertEquals(123, $tracker->getId());
    }

    public function testIsEnabledByDefault ()
    {
        $tracker = new Tracker(
            123,
            new \Zend\Session\Container('google_analytics_storage')
        );
        $this->assertTrue($tracker->enabled());
    }

    public function testHasPageTrackingEnabledByDefault ()
    {
        $tracker = new Tracker(
            123,
            new \Zend\Session\Container('google_analytics_storage')
        );
        $this->assertTrue($tracker->enabledPageTracking());
    }

    public function testDomainNameDefaultsToFalse ()
    {
        $tracker = new Tracker(
            123,
            new \Zend\Session\Container('google_analytics_storage')
        );
        $this->assertNull($tracker->getDomainName());
    }

    public function testDomainName ()
    {
        $tracker = new Tracker(
            123,
            new \Zend\Session\Container('google_analytics_storage')
        );
        $tracker->setDomainName('foobar');
        $this->assertEquals($tracker->getDomainName(), 'foobar');
    }

    public function testClearDomainNameReturnsNull()
    {
        $tracker = new Tracker(
            123,
            new \Zend\Session\Container('google_analytics_storage')
        );
        $tracker->setDomainName('foobar');
        $tracker->clearDomainName();
        $this->assertNull($tracker->getDomainName());
    }

    public function testAllowLinkerDefaultsToFalse ()
    {
        $tracker = new Tracker(
            123,
            new \Zend\Session\Container('google_analytics_storage')
        );
        $tracker->setAllowLinker(true);
        $this->assertTrue($tracker->getAllowLinker());
    }

}


