Imaging APIs for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 2.1.0 / 2016-05-29

* Merged PR #7: Add support for all traversables to `Json::write()`
  (@thekid)
* Merged PR #8: Add reset() operation. Enable calling the `elements()`,
  `pairs()` and `read()` methids  again after explicitly resetting the 
  stream. This operation may raise an exception if the input is not
  seekable, e.g. if the underlying stream is socket I/O.
  (@thekid)

## 2.0.1 / 2016-05-28

* Merged PR #6: Fix unicode surrogate pairs not being handled correctly
  (@thekid)

## 2.0.0 / 2016-02-21

* Added version compatibility with XP 7 - @thekid

## 1.0.1 / 2016-01-24

* Changed code base to no longer use deprecated FILE_MODE_* constants
  (@thekid)

## 1.0.0 / 2015-12-13

* Merged PR #4: Simplify JSON API - @thekid

## 0.9.0 / 2015-02-25

* Ensured I/O objects are closed after reading / writing has finished
  (@thekid)
* Changed text.json.FileOutput and text.json.FileInput to not close open
  files passed to its constructor.
  (@thekid)
* Changed text.json.FileOutput and text.json.FileInput to also accept
  file names - @thekid

## 0.8.5 / 2015-02-12

* Changed dependency to use XP ~6.0 (instead of dev-master) - @thekid

## 0.8.4 / 2014-12-31

* Made available via composer - @thekid

## 0.8.3 / 2014-09-23

* First public release - (@thekid)
