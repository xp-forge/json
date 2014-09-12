JSON
====

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/json.svg)](http://travis-ci.org/xp-forge/json)
[![XP Framework Mdodule](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)

Reads JSON from input streams.

Examples
--------
Reading a JSON file:

```php
$json= new JsonFile(new File('input.json'));
$value= $json->read();
```

Reading elements sequentially doesn't load the entire file into memory. You can
use the `elements()` method to receive an iterator.

```php
$conn= new HttpConnection(...);
$json= new JsonStream($conn->get('/search?q=example&limit=1000')->getInputStream());
foreach ($json->elements() as $element) {
  // Process
}
```

Performance
-----------
The JSON reader's performance is roughly 8 times that of the implementation in xp-framework/webservices, while it also uses less memory.