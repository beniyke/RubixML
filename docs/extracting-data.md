# Extracting Data
Data needs to be loaded into your project before it can become useful. Data can be stored in many formats, but the most common formats are either structured plain-text such as CSV or NDJSON or in a database such as MySQL or MongoDB. More advanced online systems will have an ETL (*extract transform load*) pipeline set up to deliver the dataset in real-time or at regular intervals. The way in which your data is delivered makes no difference to Rubix ML. You have the freedom and flexibility to implement the data source to fit the scale of the problem and current infrastructure. In addition, the library provides [Extractor](extractors/api.md) objects to help automate more common use cases.

## CSV
One of the most common formats that you'll find smaller datasets in is [comma-separated values](https://en.wikipedia.org/wiki/Comma-separated_values) (CSV) files. A CSV file is a plain-text file that contains a table with samples indicated by rows and the values of the features as columns. The columns are separated by a *delimiter* such as the `,` or `;` character and may be enclosed on both ends with an *enclosure* such as `"`. The file can sometimes contain a header as the first row which gives names to the feature columns. Rubix ML provides the [CSV](extractors/csv.md) extractor to help import data from the CSV format and can be used in conjunction with a Dataset's `fromIterator()` method to new up a dataset object. The disadvantage of CSV is that type information cannot be inferred from the format and thus all data is imported as categorical (strings) by default. However, we also provide the [Numeric String Converter](transformers/numeric-string-converter.md) to handle transforming the data into the proper format after the dataset has been extracted from the CSV format.

**Example**

```php
use Rubix\ML\Extractors\CSV;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Transformers\NumericStringConverter;

$dataset = Labeled::fromIterator(new CSV('example.csv'))
    ->apply(new NumericStringConverter());
```

## JSON
Javascript Object Notation (JSON) is a standardized lightweight plain-text format that is widely used to represent structured data such as objects and arrays. Since it is possible to derive the original data type from the JSON format, JSON files have the advantage of importing data in their proper type - foregoing the need for conversion. The [JSON](extractors/json.md) extractor handles files containing both objects with string keys and arrays with numeric keys.

**Example**

```php
use Rubix\ML\Extractors\JSON;
use Rubix\ML\Datasets\Labeled;

$dataset = Labeled::fromIterator(new JSON('example.json'));
```

## NDJSON
Another popular plain-text format is a hybrid of CSV and JSON called [NDJSON](http://ndjson.org/) or *Newline Delimited* Javascript Object Notation (JSON). It contains rows of JSON arrays or objects delineated by a newline character. It has the advantage of retaining type information as well as being cursorable (able to be read line by line) like CSV. The [NDJSON](extractors/ndjson.md) extractor can be used to instantiate a new dataset object from a NDJSON file.

**Example**

```php
use Rubix\ML\Extractors\NDJSON;
use Rubix\ML\Datasets\Unlabeled;

$extractor = new NDJSON('example.ndjson');

$extractor->setOffset(5)->setLimit(1000); // Set the cursor

$dataset = Unlabeled::fromIterator($extractor);
```

## SQL
Medium and large datasets will often be stored in an RDBMS (relational database management system) like [MySQL](https://www.mysql.com), [PostgreSQL](https://www.postgresql.org), or [SQLite](https://www.sqlite.org). Relational databases allow you to query large amounts of data on-the-fly. PHP comes with great relational database support through its [PDO](https://www.php.net/manual/en/book.pdo.php) interface. In addition, the PHP community has developed a healthy ecosystem of DBALs (Database Abstraction Layers) such as [Doctrine DBAL](https://www.doctrine-project.org/projects/dbal.html) and ORMs (Object Relational Mappers) such as [Eloquent](https://laravel.com/docs/5.8/eloquent) that help you get the data you want. The following example uses PDO and the `fetchAll()` method to return 1000 samples from the `patients` table.

**Example**

```php
use Rubix\ML\Datasets\Unlabeled;

$pdo = new PDO('mysql:dbname=example;host=127.0.0.1');

$query = $pdo->prepare('SELECT age, gender, height, diagnosis FROM patients LIMIT 1000');

$query->execute();

$samples = $query->fetchAll();

$dataset = new Unlabeled($samples);
```

## Images
The PHP language offers a number of functions to import images as PHP resources including `imagecreatefromjpeg()` and `imagecreatefrompng()` that come with the [GD](https://www.php.net/manual/en/book.image.php) extension. The example below loops over all the `.png` files in the `train` folder, imports the images as PHP resource types and labels them with the part of their filename after the underscore.

**Example**

```php
use Rubix\ML\Datasets\Labeled;

$samples = $labels = [];

foreach (glob('train/*.png') as $file) {
    $samples[] = [imagecreatefrompng($file)];
    $labels[] = preg_replace('/[0-9]+_(.*).png/', '$1', basename($file));
}

$dataset = new Labeled($samples, $labels);
```

> **Note:** Images as they come are not compatible with any learner, but they can be converted into compatible raw color channel data once they have been imported into your project using the [Image Vectorizer](transformers/image-vectorizer.md).