# Streaming writer benchmark

`bench.php` compares the standard `Writer\Xlsx` engine against
`Writer\Xlsx\Streaming\StreamingWriter`. It writes rows of 8 mixed-type
cells (string, int, float, bool, `DateTimeImmutable`, string, float, bool)
and prints one JSON line with wall time, peak memory, and output file
size.

The scripts are not PHPUnit test classes. The default PHPUnit suite scans
`tests/PhpSpreadsheetTests`; a regression test there verifies that both engines
produce the same values, booleans, numeric dates and date formats on a small
sample. Both engines use the same sheet name and preserve false values.

The JSON output records the PHP and ZipStream versions alongside measurements.
The memory regression test invokes the streaming benchmark in separate PHP
processes, avoiding allocator and cache state shared between measurements.

## Usage

```
php tests/Benchmark/bench.php <standard|streaming> [rows]
```

`rows` defaults to 200000.

Run each engine in its own process, so peak memory and wall time are never
shared between engines or between runs of the same engine. For a stable
median, run each engine 3 times:

```
php -d memory_limit=4G tests/Benchmark/bench.php standard 200000
php -d memory_limit=4G tests/Benchmark/bench.php standard 200000
php -d memory_limit=4G tests/Benchmark/bench.php standard 200000
php -d memory_limit=4G tests/Benchmark/bench.php streaming 200000
php -d memory_limit=4G tests/Benchmark/bench.php streaming 200000
php -d memory_limit=4G tests/Benchmark/bench.php streaming 200000
```

Take the median `elapsed_ms` and median `peak_memory_bytes` of each set of
3 runs.

## Recorded results

The earlier measurements used different output types for dates and omitted false
values in the standard writer. Rerun the commands above with the corrected
workload before comparing performance. Results depend on PHP, ZipStream, storage
and the proportions of unique and repeated strings.
