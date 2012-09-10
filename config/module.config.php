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

use SlmGoogleAnalytics\Analytics;
use SlmGoogleAnalytics\View\Helper;

return array(
    'google_analytics' => array(
        'id'           => '',
        'domain_name'  => '',
        'allow_linker' => '',
    ),

	'view_helpers' => array(
        'factories' => array(
            'googleAnalytics' => function($sm) {
            	$tracker = $sm->getServiceLocator()->get('google-analytics');
            	$helper  = new Helper\GoogleAnalytics($tracker);

            	return $helper;
            },
        ),
    ),
    'service_manager' => array(
    	'aliases' => array(
    		'google-analytics' => 'SlmGoogleAnalytics\Analytics\Tracker',
		),
        'factories' => array(
            'SlmGoogleAnalytics\Analytics\Tracker' => function($sm) {
            	$config = $sm->get('config');
            	$config = $config['google_analytics'];

            	$tracker = new Analytics\Tracker($config['id']);

                if (isset($config['domain_name'])) {
                    $tracker->setDomainName($config['domain_name']);
                }

                if (isset($config['allow_linker'])) {
                    $tracker->setAllowLinker($config['allow_linker']);
                }

            	return $tracker;
            },
        ),
    ),
);