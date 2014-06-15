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
namespace SlmGoogleAnalyticsTest\View\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use SlmGoogleAnalytics\View\Helper\GoogleAnalytics as Helper;

class GoogleAnalyticsTest extends TestCase
{
    /**
     * @var Helper
     */
    protected $helper;

    public function setUp()
    {
        $script = $this->getMock('SlmGoogleAnalytics\View\Helper\Script\ScriptInterface', 'getCode');
        $script->expects($this->once())
               ->method('getCode')
               ->will($this->returnValue('foo'));

        $helper = new Helper($script);
        $view   = new PhpRenderer;
        $helper->setView($view);

        $this->helper = $helper;
    }

    public function tearDown()
    {
        unset($this->helper);
    }

    public function testHelperThrowsExceptionWithNonExistingContainer()
    {
        $this->setExpectedException('Zend\ServiceManager\Exception\ServiceNotFoundException');

        $this->helper->setContainerName('NonExistingViewHelper');
        $helper = $this->helper;
        $helper()->appendScript();
    }

    public function testHelperThrowsExceptionWithContainerNotInheritedFromHeadscript()
    {
        $this->setExpectedException('SlmGoogleAnalytics\Exception\RuntimeException');

        $view   = $this->helper->getView();
        $plugin = new CustomViewHelper;
        $plugin->setView($view);

        $broker = $view->getHelperPluginManager();
        $broker->setService('CustomViewHelper', $plugin);

        $this->helper->setContainerName('CustomViewHelper');
        $helper = $this->helper;
        $helper()->appendScript();
    }

    public function testHelperDoesNotRenderTwice()
    {
        $helper  = $this->helper;
        $helper();
        $output1 = $this->getOutput($this->helper);
        $helper();
        $output2 = $this->getOutput($this->helper);

        $this->assertEquals($output1, $output2);
    }

    protected function getOutput(Helper $helper)
    {
        $helper->appendScript();
        $containerName = $helper->getContainerName();
        $container = $helper->getView()->plugin($containerName);

        return $container->toString();
    }
}
