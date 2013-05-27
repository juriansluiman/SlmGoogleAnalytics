SlmGoogleAnalytics
===
Version 0.3.0 Created by Jurian Sluiman

[![Build Status](https://secure.travis-ci.org/juriansluiman/SlmGoogleAnalytics.png?branch=master)](http://travis-ci.org/juriansluiman/SlmGoogleAnalytics)

**NOTE**: This library recently is renamed to `slm/google-analytics`. If you use
SlmGoogleAnalytics via composer and/or [packagist.org](http://packagist.org),
please update your composer.json file!

Introduction
---
SlmGoogleAnalytics is a Zend Framework 2 module to provide the logging of pages, events and
ecommerce transactions to Google Analytics. It provides a small set of tools to
configure the logging and also a view helper to convert the configation into
javascript code for the Google tracker.

Requirements
---
* [Zend Framework 2](https://github.com/zendframework/zf2) (2.2.0 minimal<sup>\*</sup>)

<sup>\*</sup>) 2.2.0 is required since version 0.3.0 of SlmGoogleAnalytics. If you
use 0.2.0 or earlier, any stable of Zend Framework 2 is allowed.

Installation
---
Add "slm/google-analytics" to your composer.json, run an update with
composer and enable it in your `application.config.php`. Copy the
`./vendors/SlmGoogleAnalytics/config/slmgoogleanalytics.global.php.dist`
to your `./config/autoload/slmgoogleanalytics.global.php`
and update your web property id there.

Usage
---
As tracking for Google Analytics is done with javascript, a view helper is
available to generate the required code based on some configuration. The
generated code is pushed into a `Zend\View\Helper\HeadScript` helper, by default
the `Zend\View\Helper\InlineScript` is used, but this can be modified into
`HeadScript` or any other helper extending the `HeadScript` helper class.

The `SlmGoogleAnalytics\Analytics\Tracker` is aliased to `google-analytics` in
the Service Manager configuration. This object is used to configure the Google
Analytics tracking. You can access this object inside a controller using the locator:

```php
public function fooAction ()
{
    $ga = $this->getServiceLocator()->get('google-analytics');
}
```

You can disable the tracking completely. This will result in no javascript code rendered at all:

```php
$ga->setEnableTracking(false);
```

If you want to track events and/or ecommerce transactions, but no page tracking,
you can turn off the page tracking only too:

```php
$ga->setEnablePageTracking(false);
```

### Events
To track an event, you must instantiate a `SlmGoogleAnalytics\Analytics\Event`
and add it to the tracker:

```php
$event = new SlmGoogleAnalytics\Analytics\Event;
$event->setCategory('Videos');
$event->setAction('Play');
$event->setLabel('Gone With the Wind');  // optionally
$event->setValue(5);                     // optionally

$ga->addEvent($event);
```

### Transactions
To track a transaction, you should use the
`SlmGoogleAnalytics\Analytics\Ecommerce\Transaction` and add one or more
`SlmGoogleAnalytics\Analytics\Ecommerce\Item` objects.

```php
$transaction = new SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;
$transaction->setId('1234');      // order ID
$transaction->setTotal('28.28');  // total

$item = new SlmGoogleAnalytics\Analytics\Ecommerce\Item;
$item->setPrice('11.99');         // unit price
$item->setQuantity('2');          // quantity

$transaction->addItem($item);

$ga->addTransaction($transaction);
```

The `Transaction` and `Item` have accessors and mutators for every property
Google is able to track (like `getTax()`, `getShipping()` and `getSku()`) but
left out in this example for the sake of clarity.

### Anonymize IP address
Some webapplications require the tracker to collect data anonymously. Google
Analytics will remove the last octet of the IP address prior to its storage.
This will reduce the accuracy of the geographic reporting, so understand the
consequences of this feature.

To collect data anonymously, set the flag in the tracker:

```php
$ga->setAnonymizeIp(true);
```

### Tracking multiple domains
Google Analytics offers to track statistics from multiple domain names. In 
order to do so, you can set the canonical domain name and optionally allow
links between the different domains:

```php
$ga->setDomainName('example.com');
$ga->setAllowLinker(true);
```

Or, alternatively, you can set these variables inside the configuration:

```php
'google_analytics' => array(
    'domain_name'  => 'example.com',
    'allow_linker' => true,
),
```

More information about what to set in which scenario is available on the
[Google Help](https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingSite) page.

### Custom variables
The tracker is capable to track custom variables. This feature differs from events,
so check the [Google Help](https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingCustomVariables)
for more information about custom variables.

To track a variable, instantiate a `SlmGoogleAnalytics\Analytics\CustomVariable` and
add it to the tracker:

```php
$index = 1;
$name  = 'Section';
$value = 'Life & Style';
$var   = new SlmGoogleAnalytics\Analytics\CustomVariable($index, $name, $value);

$ga->addCustomVariable($var);
```

You can, if required, set the scope of the variable:

```php
$scope = CustomVariable::SCOPE_SESSION;
$var   = new SlmGoogleAnalytics\Analytics\CustomVariable($index, $name, $value, $scope);
```

The scope can be `SCOPE_VISITOR`, `SCOPE_SESSION` or (the default) `SCOPE_PAGE_LEVEL`.
