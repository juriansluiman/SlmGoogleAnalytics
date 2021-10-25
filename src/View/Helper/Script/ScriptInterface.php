<?php

namespace LaminasGoogleAnalytics\View\Helper\Script;

use LaminasGoogleAnalytics\Analytics\Tracker;

interface ScriptInterface
{
    public function setTracker(Tracker $tracker);
    public function getCode();
}
