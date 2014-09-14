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

### Formatted output
To change the output format, pass a `Format` instance to the output's constructor. The formats available are:

* `DenseFormat($options)`: Best for network I/O, no unsignificant whitespace.
* `DefaultFormat($options)`: Like above, but with whitespace after commas and around colons
* `WrappedFormat($indent, $options)`: Wraps objects and first-level arrays, whitespace after commas and around colons.

The available options that can be or'ed together are:

* `Format::ESCAPE_SLASHES`: Escape forward-slashes with "\" - default behavior.
* `Format::ESCAPE_UNICODE`: Escape unicode with "\uXXXX" - default behavior.
* `Format::ESCAPE_ENTITIES`: Escape XML entities `&`, `"`, `<` and `>`. Per default, these are represented in their literal form.

```php
$out= new FileOutput(new File('glue.json'), new WrappedFormat('   ', ~Format::ESCAPE_SLASHES));
$out->write([
  'name'    => 'example/package',
  'version' => '1.0.0',
  'require' => [
    'xp-forge/json'     => '~1.0',
    'xp-framework/core' => '~6.0'
  ]
]);
```

The above code will yield the following output:

```json
{
    "name" : "example/package",
    "version" : "1.0.0'",
    "require" : {
        "xp-forge/json" : "~1.0",
        "xp-framework/core" : "~6.0"
    }
}
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

Further reading
---------------
* [Performance figures](https://github.com/xp-forge/json/wiki/Performance-overview)