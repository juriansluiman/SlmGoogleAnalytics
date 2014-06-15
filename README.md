SlmGoogleAnalytics
===

[![Build Status](https://secure.travis-ci.org/juriansluiman/SlmGoogleAnalytics.png?branch=master)](http://travis-ci.org/juriansluiman/SlmGoogleAnalytics)
[![Latest Stable Version](https://poser.pugx.org/slm/google-analytics/v/stable.png)](https://packagist.org/packages/slm/google-analytics)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/juriansluiman/SlmGoogleAnalytics/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/juriansluiman/SlmGoogleAnalytics/?branch=master)

Created by Jurian Sluiman

Introduction
---
SlmGoogleAnalytics is a Zend Framework 2 module that enables integration with
Google Analytics.

It helps to provide the logging of pages, events and ecommerce transactions to
Google Analytics in your application. It provides a small set of tools to
configure the logging and uses a view helper to convert the configation into
javascript code for the Google tracker.

Requirements
---

* PHP >=5.3
* [Zend Framework 2](https://github.com/zendframework/zf2) (>=2.2.0)

Installation
------------

Add `"slm/google-analytics"` to your `composer.json` file and update your
dependencies. Enable `SlmGoogleAnalytics` in your `application.config.php`.

If you do not have a `composer.json` file in the root of your project, copy the
contents below and put that into a file called `composer.json` and save it in
the root of your project:

```
{
    "require": {
        "slm/google-analytics": "~0.4"
    }
}
```

Then execute the following commands in a CLI:

```
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

Now you should have a `vendor` directory, including a `slm/google-analytics`. In
your bootstrap code, make sure you include the `vendor/autoload.php` file to
properly load the SlmGoogleAnalytics module.


Configuration
---

In the `/config` directory of this module (`/vendor/slm/google-analytics`) you
find a file `slmgoogleanalytics.global.php.dist`. Copy this file to your
`/config/autoload/` directory of your application.

Open the configuration file and modify the values to your need. The minimum
requirement is to set your tracker id in the config:

```php
/**
 * Web property ID (something like UA-xxxxx-x)
 */
'id' => '',
```

In the configuration, you can modify the following settings:

1. Your tracking ID
2. Disable page tracking completely (useful for your local environment)
3. Switch to the universal.js code instead of ga.js
4. Anonymize the IP address of visitors
5. Enable tracking across multiple domains
6. Enable display advertising features (DoubleClick)

Usage
---
After configuration, SlmGoogleAnalytics should work automatically. However, there
is a PHP API available to update all above settings and perform operations for
event tracking and e-commerce.

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
use SlmGoogleAnalytics\Analytics\Event;

$event = new Event('Videos', 'Play');
$ga->addEvent($event);
```

The constructor signature of the event is

    __construct($category, $action, $label = null, $value = null)

### Transactions
To track a transaction, you should use the
`SlmGoogleAnalytics\Analytics\Ecommerce\Transaction` and add one or more
`SlmGoogleAnalytics\Analytics\Ecommerce\Item` objects.

```php
use SlmGoogleAnalytics\Analytics\Ecommerce\Transaction;
use SlmGoogleAnalytics\Analytics\Ecommerce\Item;

$transaction = new Transaction;
$transaction->setId('1234');      // order ID
$transaction->setTotal('28.28');  // total

$item = new Item;
$item->setPrice('11.99');         // unit price
$item->setQuantity('2');          // quantity

$transaction->addItem($item);

$ga->addTransaction($transaction);
```

The transaction's constructor has the following signature:

    __construct($id, $total)

An item is as follows:

    __construct($sku, $price, $quantity = null, $product = null, $category = null)

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

Or, alternatively, you can set this flag inside the configuration:

```php
'google_analytics' => array(
    'anonymize_ip' => true,
),
```

More information about what to set in which scenario is available on the [Google Help](https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApi_gat#_gat._anonymizeIp) page.

### Tracking multiple domains
Google Analytics offers to track statistics from multiple domain names. In order
to do so, you can set the canonical domain name and optionally allow links
between the different domains:

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
use SlmGoogleAnalytics\Analytics\CustomVariable;

$index = 1;
$name  = 'Section';
$value = 'Life & Style';
$var   = new CustomVariable($index, $name, $value);

$ga->addCustomVariable($var);
```

You can, if required, set the scope of the variable:

```php
$scope = CustomVariable::SCOPE_SESSION;
$var   = new CustomVariable($index, $name, $value, $scope);
```

The scope can be `SCOPE_VISITOR`, `SCOPE_SESSION` or (the default) `SCOPE_PAGE_LEVEL`.

### Display Advertising
To enable Google Analytics [Display Advertising](https://support.google.com/analytics/answer/3450482) features simply call the appropiate method on the tracker.

```php
$ga->setEnableDisplayAdvertising(true);
```

Or, alternatively, you can set these variables inside the configuration:

```php
'google_analytics' => array(
    'enable_display_advertising' => true,
),
```

The Google Analytics Display Advertising features include the following:

- [Demographics and Interests reporting](https://support.google.com/analytics/answer/2799357)
- [Remarketing with Google Analytics](https://support.google.com/analytics/answer/2611268)
- DoubleClick Campaign Manager integration (for [Google Analytics Premium](https://www.google.com/intl/en_ALL/analytics/premium/index.html))
