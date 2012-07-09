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
use SlmGoogleAnalytics\Analytics\Event;

class EventTest extends TestCase
{
    public function testCanInstantiateEvent ()
    {
        $event = new Event('Category', 'Action');

        $this->assertEquals('Category', $event->getCategory());
        $this->assertEquals('Action', $event->getAction());
    }

    public function testCanAddEventToTracker ()
    {
        $tracker = new Tracker(123);
        $event   = new Event('Category', 'Action');
        $tracker->addEvent($event);

        $events = count($tracker->events());
        $this->assertEquals(1, $events);
    }

    public function testCanAddMultipleEventsToTracker ()
    {
        $tracker = new Tracker(123);
        $event1  = new Event('Category', 'Action');
        $event2  = new Event('Category', 'Action');
        $tracker->addEvent($event1);
        $tracker->addEvent($event2);

        $events = count($tracker->events());
        $this->assertEquals(2, $events);
    }

    public function testCanHaveEventLabel ()
    {
        $event = new Event('Category', 'Action', 'Label');

        $this->assertEquals('Label', $event->getLabel());
    }

    public function testCanHaveEventValue ()
    {
        $event = new Event('Category', 'Action', null, 123);

        $this->assertEquals(123, $event->getValue());
    }

    public function testCanHaveEventLabelAndValue ()
    {
        $event = new Event('Category', 'Action', 'Label', 123);

        $this->assertEquals('Label', $event->getLabel());
        $this->assertEquals(123, $event->getValue());
    }
}
