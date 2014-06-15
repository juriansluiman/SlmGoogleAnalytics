<?php
/**
 * SlmGoogleAnalytics Configuration
 *
 * If you have a ./configs/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$googleAnalytics = array(
    /**
     * Web property ID (something like UA-xxxxx-x)
     */
    'id' => '',

    /**
     * Disable/enable page tracking
     *
     * It is adviced to turn off tracking in a development/staging environment. Put this
     * configuration option in your local.php in the autoload folder and set "enable" to
     * false.
     */
    // 'enable' => false,

    /**
     * Set the type of javascript to use for Google Analytics
     *
     * This can be the ga.js or "universal" script. Default is the ga.js script, but
     * SlmGoogleAnalytics does support the univesal code as well.
     *
     * Allowed values: "google-analytics-ga" or "google-analytics-universal".
     */
    // 'script' => 'google-analytics-ga',

    /**
     * Set the flag to anonymize the IP address
     *
     * False by default, with "true" you enable this feature.
     */
    // 'anonymize_ip' => false,

    /**
     * Tracking across multiple (sub)domains
     *
     * False by default, with "true" you enable this feature.
     * @see https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingSite
     */
    // 'domain_name'  => '',
    // 'allow_linker' => false,

    /**
     * Enable Google's Analytics Display Advertising features
     *
     * Display Adversiting includes the following:
     * - Demographics and Interests reporting
     * - Remarketing with Google Analytics
     * - DoubleClick Campaign Manager integration (for Google Analytics Premium)
     *
     * False by default, with "true" you enable this feature.
     */
    // 'enable_display_advertising' => false,

    /**
     * Set the container name to be used for the Google Analytics code
     */
    //'container_name' => 'InlineScript',
);

/**
 * You do not need to edit below this line
 */
return array('google_analytics' => $googleAnalytics);
