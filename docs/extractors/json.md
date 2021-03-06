<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/Datasets/Extractors/JSON.php">[source]</a></span>

# JSON
Javascript Object Notation is a standardized lightweight plain-text representation that is widely used. JSON has the advantage of retaining type information, however since the entire JSON blob is read on load, it is not cursorable like CSV or NDJSON.

## Parameters
| # | Param | Default | Type | Description |
|---|---|---|---|---|
| 1 | path |  | string | The path to the JSON file. |

## Additional Methods
This extractor does not have any additional methods.

## Example
```php
use Rubix\ML\Extractors\JSON;

$extractor = new JSON('example.json');
```