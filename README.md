# JSON diff and rearrange tool for PHP

A PHP implementation for finding unordered diff between two `JSON` documents.

[![Build Status](https://travis-ci.org/swaggest/json-diff.svg?branch=master)](https://travis-ci.org/swaggest/json-diff)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/swaggest/json-diff/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/swaggest/json-diff/?branch=master)
[![Code Climate](https://codeclimate.com/github/swaggest/json-diff/badges/gpa.svg)](https://codeclimate.com/github/swaggest/json-diff)
[![Test Coverage](https://codeclimate.com/github/swaggest/json-diff/badges/coverage.svg)](https://codeclimate.com/github/swaggest/json-diff/coverage)

## Purpose

 * To simplify changes review between two `JSON` files you can use a standard `diff` tool on rearranged pretty-printed `JSON`.
 * To detect breaking changes by analyzing removals and changes from original `JSON`.
 * To keep original order of object sets (for example `swagger.json` [parameters](https://swagger.io/docs/specification/describing-parameters/) list).

## Installation

### Library

```bash
git clone https://github.com/swaggest/json-diff.git
```

### Composer

[Install PHP Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require swaggest/json-diff
```

## Library usage

Create `JsonDiff` object from two values (`original` and `new`).

```php
$r = new JsonDiff(json_decode($originalJson), json_decode($newJson));
```

On construction `JsonDiff` will build `rearranged` value of `new` recursively keeping `original` keys order where possible. 
Keys that are missing in `original` will be appended to the end of `rearranged` value in same order they had in `new` value.

If two values are arrays of objects, `JsonDiff` will try to find a common unique field in those objects and use it as criteria for rearranging. 
You can disable this behaviour with `JsonDiff::SKIP_REARRANGE_ARRAY` option:
```php
$r = new JsonDiff(
    json_decode($originalJson), 
    json_decode($newJson), 
    JsonDiff::SKIP_REARRANGE_ARRAY
);
```

On created object you have several handy methods.

### `getRearranged`
Returns new value, rearranged with original order.

### `getRemoved`
Returns removals as partial value of original.

### `getRemovedPaths`
Returns list of `JSON` paths that were removed from original.

### `getRemovedCnt`
Returns number of removals.

### `getAdded`
Returns additions as partial value of new.

### `getAddedPaths`
Returns list of `JSON` paths that were added to new.

### `getAddedCnt`
Returns number of additions.

### `getModifiedOriginal`
Returns modifications as partial value of original.

### `getModifiedNew`
Returns modifications as partial value of new.

### `getModifiedPaths`
Returns list of `JSON` paths that were modified from original to new.

### `getModifiedCnt`
Returns number of modifications.

## Example

```php
$originalJson = <<<'JSON'
{
    "key1": [4, 1, 2, 3],
    "key2": 2,
    "key3": {
        "sub0": 0,
        "sub1": "a",
        "sub2": "b"
    },
    "key4": [
        {"a":1, "b":true}, {"a":2, "b":false}, {"a":3}
    ]
}
JSON;

$newJson = <<<'JSON'
{
    "key5": "wat",
    "key1": [5, 1, 2, 3],
    "key4": [
        {"c":false, "a":2}, {"a":1, "b":true}, {"c":1, "a":3}
    ],
    "key3": {
        "sub3": 0,
        "sub2": false,
        "sub1": "c"
    }
}
JSON;

$expected = <<<'JSON'
{
    "key1": [5, 1, 2, 3],
    "key3": {
        "sub1": "c",
        "sub2": false,
        "sub3": 0
    },
    "key4": [
        {"a":1, "b":true}, {"a":2, "c":false}, {"a":3, "c":1}
    ],
    "key5": "wat"
}
JSON;

$r = new JsonDiff(json_decode($originalJson), json_decode($newJson));
$this->assertSame(
    json_encode(json_decode($expected), JSON_PRETTY_PRINT),
    json_encode($r->getRearranged(), JSON_PRETTY_PRINT)
);
$this->assertSame('{"key3":{"sub3":0},"key4":{"1":{"c":false},"2":{"c":1}},"key5":"wat"}',
    json_encode($r->getAdded()));
$this->assertSame(array(
    '#/key3/sub3',
    '#/key4/1/c',
    '#/key4/2/c',
    '#/key5',
), $r->getAddedPaths());
$this->assertSame('{"key2":2,"key3":{"sub0":0},"key4":{"1":{"b":false}}}',
    json_encode($r->getRemoved()));
$this->assertSame(array(
    '#/key2',
    '#/key3/sub0',
    '#/key4/1/b',
), $r->getRemovedPaths());

$this->assertSame(array(
    '#/key1/0',
    '#/key3/sub1',
    '#/key3/sub2',
), $r->getModifiedPaths());

$this->assertSame('{"key1":[4],"key3":{"sub1":"a","sub2":"b"}}', json_encode($r->getModifiedOriginal()));
$this->assertSame('{"key1":[5],"key3":{"sub1":"c","sub2":false}}', json_encode($r->getModifiedNew()));
```

## CLI tool

### Usage

```
json-diff --help
v1.0.0 json-diff
JSON diff and rearrange tool for PHP, https://github.com/swaggest/json-diff
Usage: 
   json-diff <action> <originalPath> <newPath>
   action         Action to perform                                                     
                  Allowed values: rearrange, changes, removals, additions, modifications
   originalPath   Path to old (original) json file                                      
   newPath        Path to new json file                                                 
   
Options: 
   --out <out>     Path to output result json file, STDOUT if not specified
   --show-paths    Show JSON paths                                         
   --show-json     Show JSON result                                        
   
Misc: 
   --help               Show usage information    
   --version            Show version              
   --bash-completion    Generate bash completion  
   --install            Install to /usr/local/bin/
```

### Examples

Using with standard `diff`

```
json-diff rearrange ./composer.json ./composer2.json --show-json | diff ./composer.json -
3c3
<     "description": "JSON diff and merge tool for PHP",
---
>     "description": "JSON diff and merge tool for PHPH",
5,11d4
<     "license": "MIT",
<     "authors": [
<         {
<             "name": "Viacheslav Poturaev",
<             "email": "vearutop@gmail.com"
<         }
<     ],
25,28c18
<     },
<     "bin": [
<         "bin/json-diff"
<     ]
---
>     }
```

Showing differences in `JSON` mode

```
bin/json-diff changes ./composer.json ./composer2.json --show-json --show-paths
#/license
#/authors
#/bin
#/description
{
    "removals": {
        "license": "MIT",
        "authors": [
            {
                "name": "Viacheslav Poturaev",
                "email": "vearutop@gmail.com"
            }
        ],
        "bin": [
            "bin/json-diff"
        ]
    },
    "additions": null,
    "modifiedOriginal": {
        "description": "JSON diff and merge tool for PHP"
    },
    "modifiedNew": {
        "description": "JSON diff and merge tool for PHPH"
    }
}
```