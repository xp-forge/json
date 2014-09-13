JSON
====

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/json.svg)](http://travis-ci.org/xp-forge/json)
[![XP Framework Mdodule](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)

Reads JSON from various input source.

Examples
--------
Reading from a file:

```php
$json= new JsonFile(new File('input.json'));
$value= $json->read();
```

Reading from a string:

```php
$json= new JsonString('{"Hello": "World"}');
$value= $json->read();
```

Reading from a stream:

```php
$json= new JsonStream(new SocketInputStream(...));
$value= $json->read();
```

Sequential processing
---------------------
Reading elements sequentially doesn't load the entire source into memory before prcoessing it. You can
use the `elements()` method to receive an iterator over a JSON array.

```php
$conn= new HttpConnection(...);
$json= new JsonStream($conn->get('/search?q=example&limit=1000')->getInputStream());
foreach ($json->elements() as $element) {
  // Process
}
```

If you get a huge object, you can also process it sequentially using the `pairs()` method:

```php
$conn= new HttpConnection(...);
$json= new JsonStream($conn->get('/resources/4711?expand=*')->getInputStream());
foreach ($json->pairs() as $key => $value) {
  // Process
}
```


Performance
-----------
The JSON reader's performance is roughly 8-9 times that of the implementation in xp-framework/webservices, while it also uses less memory. On the other side, PHP's native `json_decode()` function is 7-8 times faster (using current PHP 5.5).

Given a test data size of 158791 bytes (inside a file on the local file system) and running parsing for 100 iterations, here is an overview of the results:

| *Implementation*                 | *Time*          | *Per iteration* | *Memory usage*                 |
| -------------------------------- | --------------: | --------------: | -----------------------------: | 
| PHP Native                       | 0.239 seconds   | 2.3 ms          | 867.801 kB / 1616.422 kB peak  |
| This implementation (sequential) | 1.905 seconds   | 19.1 ms         | 852.512 kB / 883.558 kB peak   |
| This implementation (serial)     | 1.940 seconds   | 19.4 ms         | 843.496 kB / 1166.999 kB peak  |
| XP Webservices                   | 16.854 seconds  | 168.5 ms        | 1026.703 kB / 1510.680 kB peak |

The performance overhead the native `json_decode()` function vanishes when reading from a network socket and parsing the elements sequentially.

```php
$c= new HttpConnection('https://api.github.com/orgs/xp-framework/repos');
$r= $c->get([], ['User-Agent' => 'xp-forge/json']);

// Native solution
$elements= json_decode(Streams::readAll($r->getInputStream()), true);
foreach ($elements as $element) {
  
}

// Solution using this implementation's sequential processing
$j= new JsonStream($r->getInputStream());
foreach ($json->elements() as $element) {
  
}

// Solution using this implementation's serial processing
$j= new JsonStream($r->getInputStream());
foreach ($json->read() as $element) {
  
}
```

The test data is the same size as above (158791 bytes).

| *Implementation*                 | *Time to 1st element* | *Time for all elements* | *Memory usage*                 |
| -------------------------------- | --------------------: | ----------------------: | -----------------------------: |
| PHP Native                       | 0.718 seconds         | 0.719 seconds           | 1046.843 kB / 1752.016 kB peak |
| This implementation (sequential) | 0.143 seconds         | 0.709 seconds           | 1025.486 kB / 1067.980 kB peak |
| This implementation (serial)     | 0.749 seconds         | 0.750 seconds           | 1019.370 kB / 1376.242 kB peak |
