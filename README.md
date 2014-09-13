JSON
====

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/json.svg)](http://travis-ci.org/xp-forge/json)
[![XP Framework Mdodule](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)

Reads and writes JSON to and from various input source.

Examples
--------
Reading is done using one of the `Input` implementations:

```php
$in= new FileInput(new File('input.json'));
$in= new StringInput('{"Hello": "World"}');
$in= new StreamInput(new SocketInputStream(...));

$value= $in->read();
```

Writing is done using one of the `Output` implementations:

```php
$out= new FileOutput(new File('output.json'));
$out= new StreamOutput(new SocketOuputStream(...));

$out->write($value);
```

Writing to strings works a bit differently, the result needs to be fetched via the `bytes()` method.

```php
$out= new StringOutput();
$out->write('"Hello", he said.');

$json= $out->bytes();   // "\"Hello\", he said."
```

Sequential processing
---------------------
Reading elements sequentially doesn't load the entire source into memory before prcoessing it. You can
use the `elements()` method to receive an iterator over a JSON array.

```php
$conn= new HttpConnection(...);
$json= new StreamInput($conn->get('/search?q=example&limit=1000')->getInputStream());
foreach ($json->elements() as $element) {
  // Process
}
```

If you get a huge object, you can also process it sequentially using the `pairs()` method:

```php
$conn= new HttpConnection(...);
$json= new StreamInput($conn->get('/resources/4711?expand=*')->getInputStream());
foreach ($json->pairs() as $key => $value) {
  // Process
}
```

To detect the type of the data on the stream (again, without reading it completely), you can use the `type()` method.

```php
$conn= new HttpConnection(...);
$json= new StreamInput($conn->get($resource)->getInputStream());
$type= $json->type();
if ($type->isArray()) {
  // Handle arrays
} else if ($type->isObject()) {
  // Handle objects
} else {
  // Handle primitives
}
```

Performance
-----------
The JSON reader's performance is roughly 8-9 times that of the implementation in xp-framework/webservices, while it also uses less memory. On the other side, PHP's native `json_decode()` function is 7-8 times faster (using current PHP 5.5).

### Raw calls
Given a test data size of 158791 bytes (inside a file on the local file system) and running parsing for 100 iterations, here is an overview of the results:

| *Implementation*  | *Time*          | *Per iteration* | *Memory usage / peak* | *Overhead* |
| ----------------- | --------------: | --------------: | --------------------: | ---------: |
| PHP Native        | 0.239 seconds   | 2.4 ms          | 867.8 kB / 1616.4 kB  |            |
| This (sequential) | 1.869 seconds   | 18.7 ms         | 857.3 kB / 893.6 kB   | 16.3 ms    |
| This (serial)     | 1.923 seconds   | 19.2 ms         | 848.1 kB / 1253.1 kB  | 16.8 ms    |
| XP Webservices    | 16.854 seconds  | 168.5 ms        | 1026.7 kB / 1510.7 kB | 166.1 ms   |

The overhead for parsing a single 150 Kilobyte JSON file is around 17 milliseconds, which should be mostly acceptable.

### Network reads
The performance benefit vanishes when reading from a network socket and parsing the elements sequentially.

```php
$c= new HttpConnection('https://api.github.com/orgs/xp-framework/repos');
$r= $c->get([], ['User-Agent' => 'xp-forge/json']);

// Native solution
$elements= json_decode(Streams::readAll($r->getInputStream()), true);
foreach ($elements as $element) {
  
}

// Solution using this implementation's sequential processing
$json= new StreamInput($r->getInputStream());
foreach ($json->elements() as $element) {
  
}

// Solution using this implementation's serial processing
$json= new StreamInput($r->getInputStream());
foreach ($json->read() as $element) {
  
}

// Solution using XP Webservices
$elements= (new JsonDecoder())->decodeFrom($r->getInputStream());
foreach ($elements as $element) {
  
}
```

The test data is the same size as above (158791 bytes).

| *Implementation*  | *Time to 1st element* | *Time for all elements* | *Memory usage / peak* |
| ----------------- | --------------------: | ----------------------: | --------------------: |
| PHP Native        | 0.718 seconds         | 0.719 seconds           | 1046.8 kB / 1752.6 kB |
| This (sequential) | 0.143 seconds         | 0.712 seconds           | 1036.5 kB / 1078.8 kB |
| This (serial)     | 0.715 seconds         | 0.717 seconds           | 1027.0 kB / 1383.6 kB |
| XP Webservice     | 0.752 seconds         | 0.752 seconds           | 1210.5 kB / 1635.6 kB |

### Writing

Using the test data from above, written to a file 100 times:

| *Implementation*  | *Time*          | *Per iteration* | *Memory usage / peak* | *Overhead* |
| ----------------- | --------------: | --------------: | --------------------: | ---------: |
| PHP Native        | 0.390 seconds   | 3.9 ms          | 1324.4 kB / 1521.9 kB |            |
| This              | 0.714 seconds   | 7.1 ms          | 1346.5 kB / 1362.9 kB | 3.2 ms     |

The overhead for writing a structure which results in a 150 Kilobyte JSON file is around 3 milliseconds, which should be mostly acceptable.
