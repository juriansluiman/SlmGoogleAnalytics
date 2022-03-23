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

return [
    'google_analytics' => [
        'enable'                     => true,
        'id'                         => '',
        'domain_name'                => '',
        'allow_linker'               => false,
        'enable_display_advertising' => false,
        'anonymize_ip'               => false,
        'script'                     => 'google-analytics-ga',
    ],
    'service_manager'  => [
        'aliases'    => [
            'google-analytics'           => SlmGoogleAnalytics\Analytics\Tracker::class,
            'google-analytics-universal' => SlmGoogleAnalytics\View\Helper\Script\Analyticsjs::class,
            'google-analytics-ga'        => SlmGoogleAnalytics\View\Helper\Script\Gajs::class,
        ],
        'factories'  => [
            SlmGoogleAnalytics\Analytics\Tracker::class     => SlmGoogleAnalytics\Service\TrackerFactory::class,
            SlmGoogleAnalytics\Service\ScriptFactory::class => SlmGoogleAnalytics\Service\ScriptFactory::class,
            SlmGoogleAnalytics\View\Helper\Script\Analyticsjs::class => \Zend\ServiceManager\Factory\InvokableFactory::class,
            SlmGoogleAnalytics\View\Helper\Script\Gajs::class        => \Zend\ServiceManager\Factory\InvokableFactory::class,
        ],
    ],
    'view_helpers'     => [
        'factories' => [
            'googleAnalytics' => 'SlmGoogleAnalytics\Service\GoogleAnalyticsFactory',
        ],
    ]
];
