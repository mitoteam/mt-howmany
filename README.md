# mt-howmany

[![GitHub Version](https://img.shields.io/github/v/release/mitoteam/mt-howmany?style=flat-square&logo=github)](https://github.com/mitoteam/mt-howmany)
[![Packagist Version](https://img.shields.io/packagist/v/mitoteam/mt-howmany?include_prereleases&style=flat-square&logo=packagist)](https://packagist.org/packages/mitoteam/mt-howmany)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/mitoteam/mt-howmany?style=flat-square&logo=php)](https://github.com/mitoteam/mt-howmany)

Command-line utility to measure project sources size, files count, lines count, characters count. You can exclude vendor libraries, binary files, generated code and so from scanning.

Our goal was to understand how many code was written by our own hands for different projects.

## Installation

Just add it as usual composer dependency:

```
composer require mitoteam/mt-howmany
```

Or you can add dependency manually to your `composer.json`.

## Usage

Tool installs standard composer binary to vendor/bin/mt-howmany. So you can run it by just calling it from shell:

```
vendor/bin/mt-howmany
```

or under Windows:
```
vendor\bin\mt-howmany.bat
```

It looks for config in current directory. By default it scans currenct directory recursively, but you can set specific paths to scan in config. There are also bunch of options in config.

You can import config from other files with `import` option (for example to have some common parts between projects).

Take a look at sample config for details: [mt-howmany.example.yml](mt-howmany.example.yml)

For each file that is not ignored in config tool calculates lines count, file size and characters count (using `symphony/string` to deal with Unicode and multi-byte characters).

After scanning it prints table with gathered data and final total numbers for whole project. 

You can add `-v` or `-vv` arguments to increase output verbosity.

`-v` add per-path statistics table (helps to understand what to exclude from scanning to left only code that is trully yours).

`-vv` additionally prints complete data for each file to understand even better where numbers are taken from. 

Notes, bugreports, proposals and pull requests are always welcomed.

## Output Example

```
mt-howmany by MiTo Team
=======================

Working directory: D:\Work\MiSHuTka\Projects\Web\binardo
Config file loaded: D:\Work\MiSHuTka\Projects\Web\binardo\mt-howmany.yml

Results by file extension
=========================

 ----------- -------- ------------ ------------- -------
  Type        Size     Characters   Files Count   Lines
 ----------- -------- ------------ ------------- -------
  php         777Kb    782756       174           32225
  twig        51.1Kb   51145        26            1471
  scss        30.4Kb   30464        47            1886
  js          17.1Kb   17276        11            637
  yml         14.9Kb   15157        17            597
  sh          9.59Kb   9816         11            348
  po          3.69Kb   2965         1             164
  json        3.29Kb   3346         4             127
  module      3.10Kb   3176         2             131
  theme       3.06Kb   3133         2             123
  gitignore   2.25Kb   2280         2             82
  md          2.08Kb   1323         6             38
  css         213      212          1             2
  txt         139      139          1             8
  htaccess    38       38           2             4
  empty       0        0            1             1
 ----------- -------- ------------ ------------- -------

Totals
======

Types count: 16
Paths count: 69
Files count: 308
Size: 918Kb
Characters: 923226
Lines: 37844
Pages by Characters: 257
Pages by Lines: 1052


 [OK] Done
```

**MiTo Team**: info@mito-team.com, https://www.mito-team.com
