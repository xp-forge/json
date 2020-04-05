JSON
====

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/json.svg)](http://travis-ci.org/xp-forge/json)
[![XP Framework Mdodule](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.6+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_6plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/json/version.png)](https://packagist.org/packages/xp-forge/json)

Reads and writes JSON to and from various input sources.

Examples
--------
Reading can be done from a string using one of the `Input` implementations:

```php
// Strings
$value= Json::read('"Test"');

// Input
$in= new FileInput(new File('input.json'));
$in= new StringInput('{"Hello": "World"}');
$in= new StreamInput(new SocketInputStream(...));

$value= Json::read($in);
```

Writing can be done to a string or using one of the `Output` implementations:

```php
// Strings
$json= Json::of('Test');

// Output
$out= new FileOutput(new File('output.json'));
$out= new StreamOutput(new SocketOuputStream(...));

Json::write($value, $out);
```

### Formatted output
To change the output format, pass a `Format` instance to the output's constructor. The formats available are:

* `DenseFormat($options)`: Best for network I/O, no unsignificant whitespace, default if nothing given and accessible via `Format::dense($options= ~Format::ESCAPE_SLASHES)`.
* `WrappedFormat($indent, $options)`: Wraps first-level arrays and all objects, uses whitespace after commas colons. An instance of this format using 4 spaces for indentation and per default leaving forward slashes unescaped is available via `Format::wrapped($indent= "    ", $options= ~Format::ESCAPE_SLASHES)`.

The available options that can be or'ed together are:

* `Format::ESCAPE_SLASHES`: Escape forward-slashes with "\" - default behavior.
* `Format::ESCAPE_UNICODE`: Escape unicode with "\uXXXX" - default behavior.
* `Format::ESCAPE_ENTITIES`: Escape XML entities `&`, `"`, `<` and `>`. Per default, these are represented in their literal form.

```php
$out= new FileOutput(new File('glue.json'), Format::wrapped());
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
    "name": "example/package",
    "version": "1.0.0'",
    "require": {
        "xp-forge/json": "~1.0",
        "xp-framework/core": "~6.0"
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
$in= new StreamInput($conn->get('/search?q=example&limit=1000')->in());
foreach ($in->elements() as $element) {
  // Process
}
```

If you get a huge object, you can also process it sequentially using the `pairs()` method. This will parse a single key/value pair at a time.

```php
$conn= new HttpConnection(...);
$in= new StreamInput($conn->get('/resources/4711?expand=*')->in());
foreach ($in->pairs() as $key => $value) {
  // Process
}
```

To detect the type of the data on the stream (again, without reading it completely), you can use the `type()` method.

```php
$conn= new HttpConnection(...);
$in= new StreamInput($conn->get($resource)->in());
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

$stream= (new StreamOutput(...))->begin(Types::$ARRAY);
while ($record= $query->next()) {
  $stream->element($record);
}
$stream->close();
```

As the `Stream` class implements the Closeable interface, it can be used in the `with` statement:

```php
$query= $conn->query('select * from person');

with ((new StreamOutput(...))->begin(Types::$ARRAY), function($stream) use($query) {
  while ($record= $query->next()) {
    $stream->element($record);
  }
});
```

Further reading
---------------
* [Performance figures](https://github.com/xp-forge/json/wiki/Performance-overview). TL;DR: While slower than the native functionality, the performance overhead is in low millisecond ranges. Using sequential processing we have an advantage both performance- and memory-wise.
* [Parsing JSON is a Minefield](http://seriot.ch/parsing_json.html). This library runs this test suite next to its own.