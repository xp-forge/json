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
Processing elements sequentially can save you memory and give a better performance in certain situations.

### Reading
You can use the `elements()` method to receive an iterator over a JSON array. Instead of loading the entire source into memory and then returning the parsed array, it will parse one array element at a time, yielding them while going.

```php
$conn= new HttpConnection(...);
$in= new StreamInput($conn->get('/search?q=example&limit=1000')->getInputStream());
foreach ($in->elements() as $element) {
  // Process
}
```

If you get a huge object, you can also process it sequentially using the `pairs()` method. This will parse a single key/value pair at a time.

```php
$conn= new HttpConnection(...);
$in= new StreamInput($conn->get('/resources/4711?expand=*')->getInputStream());
foreach ($in->pairs() as $key => $value) {
  // Process
}
```

To detect the type of the data on the stream (again, without reading it completely), you can use the `type()` method.

```php
$conn= new HttpConnection(...);
$in= new StreamInput($conn->get($resource)->getInputStream());
$type= $in->type();
if ($type->isArray()) {
  // Handle arrays
} else if ($type->isObject()) {
  // Handle objects
} else {
  // Handle primitives
}
```

### Writing
To write data sequentially, you can use the `begin()` method and the stream it returns. This makes sense when the source offers a way to read data sequentially, if you already have the entire data in memory, using `write()` has the same effect.

```php
$query= $conn->query('select * from person');

$stream= new StreamOutput(...)->begin(Types::$ARRAY);
while ($record= $query->next()) {
  $stream->element($record);
}
$stream->close();
```

Performance
-----------
The JSON reader's performance is roughly 8-9 times that of the implementation in [xp-framework/webservices](https://github.com/xp-framework/webservices), while it also uses less memory. On the other side, PHP's native `json_decode()` function is 7-8 times faster (using current PHP 5.5). The figures for writing are 7-8 times better than xp-framework/webservices, and around twice as slow as PHP's native `json_encode()`.

### Reading
Given a test data size of 158791 bytes (inside a file on the local file system) and running parsing for 100 iterations, here is an overview of the results:

| *Implementation*  | *Time*          | *Per iteration* | *Memory usage / peak* | *Overhead* |
| ----------------- | --------------: | --------------: | --------------------: | ---------: |
| PHP Native        | 0.239 seconds   | 2.4 ms          | 867.8 kB / 1616.4 kB  |            |
| This (sequential) | 1.869 seconds   | 18.7 ms         | 857.3 kB / 893.6 kB   | 16.3 ms    |
| This (serial)     | 1.923 seconds   | 19.2 ms         | 848.1 kB / 1253.1 kB  | 16.8 ms    |
| XP Webservices    | 16.854 seconds  | 168.5 ms        | 1026.7 kB / 1510.7 kB | 166.1 ms   |

*The overhead for parsing a single 150 Kilobyte JSON file is around 17 milliseconds, which should be mostly acceptable.*

### Writing
Using the test data from above, written to a file on the local file system 100 times:

| *Implementation*  | *Time*          | *Per iteration* | *Memory usage / peak* | *Overhead* |
| ----------------- | --------------: | --------------: | --------------------: | ---------: |
| PHP Native        | 0.390 seconds   | 3.9 ms          | 1324.4 kB / 1521.9 kB |            |
| This (sequential) | 0.709 seconds   | 7.1 ms          | 1384.7 kB / 1401.0 kB | 3.2 ms     |
| This (serial)     | 0.705 seconds   | 7.0 ms          | 1353.9 kB / 1370.4 kB | 3.1 ms     |
| XP Webservices    | 5.318 seconds   | 53.2 ms         | 1523.3 kB / 1544.6 kB | 49.3 ms    |

*The overhead is around 3 milliseconds, which is near to nothing.*

### Network I/O
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
| XP Webservices    | 0.752 seconds         | 0.752 seconds           | 1210.5 kB / 1635.6 kB |

### Sequential writing
The memory saving when writing sequentially is most visible when working with streamed input data:

```php
$c= new HttpConnection('http://real-chart.finance.yahoo.com/table.csv?s=UTDI.DE');
$r= $c->get([], ['User-Agent' => 'xp-forge/json']);

$csv= new CsvMapReader(new TextReader($r->getInputStream(), null), [], CsvFormat::$COMMAS);
$csv->setKeys($csv->getHeaders());

// Native solution
$struct= [];
while ($record= $csv->read()) {
  $struct[]= $record;
}
FileUtil::setContents(new File('finance.json'), json_encode($struct, true));

// Solution using this implementation's sequential processing
$f= new FileOutput(new File('finance.json'));
with ($f->begin(Types::$ARRAY), function($stream) use($csv) {
  while ($record= $csv->read()) {
    $stream->element($record);
  }
});

// Solution using this implementation's serial processing
$struct= [];
while ($record= $csv->read()) {
  $struct[]= $record;
}
(new FileOutput(new File('finance.json')))->write($struct);
```

The test data downloaded is 169602 bytes, and results in a roughly 500 kB large JSON file.

| *Implementation*  | *Time to process*     | *Generated file size* | *Memory usage / peak* |
| ----------------- | --------------------: | --------------------: | --------------------: |
| PHP Native        | 0.947 seconds         | 438121 bytes          | 1259.1 kB / 5367.9 kB |
| This (sequential) | 0.946 seconds         | 516450 bytes          | 1299.6 kB / 1421.5 kB |
| This (serial)     | 0.941 seconds         | 516450 bytes          | 1284.2 kB / 4729.2 kB |
| XP Webservices    | 1.129 seconds         | 550021 bytes          | 1459.7 kB / 5014.5 kB |
