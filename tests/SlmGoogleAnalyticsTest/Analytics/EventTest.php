<?php

/**
 * This is free and unencumbered software released into the public domain.
 * 
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any
 * means.
 * 
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * 
 * For more information, please refer to <http://unlicense.org/>
 * 
 * @category   SlmGoogleAnalytics
 * @copyright  Copyright (c) 2012 Jurian Sluiman <jurian@juriansluiman.nl>
 * @license    http://unlicense.org Unlicense
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
    
    public function testCannotAddSameEventTwice ()
    {
        $this->setExpectedException('SlmGoogleAnalytics\Exception\InvalidArgumentException');
        
        $tracker = new Tracker(123);
        $event   = new Event('Category', 'Action');
        
        $tracker->addEvent($event);
        $tracker->addEvent($event);
    }
}
