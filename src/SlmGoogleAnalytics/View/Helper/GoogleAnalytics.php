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
namespace SlmGoogleAnalytics\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadScript;
use SlmGoogleAnalytics\Exception\RuntimeException;

class GoogleAnalytics extends AbstractHelper
{
    /**
     * @var string
     */
    protected $containerName = 'InlineScript';

    /**
     * @var bool
     */
    protected $rendered = false;

    /**
     * @var Script\ScriptInterface
     */
    protected $script;

    public function __construct(Script\ScriptInterface $script)
    {
        $this->script = $script;
    }

    public function getContainerName()
    {
        return $this->containerName;
    }

    public function setContainerName($container)
    {
        $this->containerName = $container;
    }

    protected function getContainer()
    {
        $containerName = $this->getContainerName();
        $container     = $this->view->plugin($containerName);

        return $container;
    }

    public function __invoke()
    {
        return $this;
    }

    public function appendScript()
    {
        // Do not render the GA twice
        if ($this->rendered) {
            return;
        }

        // We need to be sure $container->appendScript() can be called
        $container = $this->getContainer();
        if (!$container instanceof HeadScript) {
            throw new RuntimeException(sprintf(
                'Container %s does not extend HeadScript view helper',
                $this->getContainerName()
            ));
        }

        $code = $this->script->getCode();

        if (empty($code)) {
            return;
        }

        $container->appendScript($code);

        // Mark this GA as rendered
        $this->rendered = true;

        return $this;
    }
}
