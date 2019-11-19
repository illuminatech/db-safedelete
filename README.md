<p align="center">
    <a href="https://github.com/illuminatech" target="_blank">
        <img src="https://avatars1.githubusercontent.com/u/47185924" height="100px">
    </a>
    <h1 align="center">Laravel Eloquent Safe Delete</h1>
    <br>
</p>

This extension provides "safe" deletion for the Eloquent model, which attempts to invoke force delete, and, if it fails - falls back to soft delete.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/illuminatech/db-safedelete.svg)](https://packagist.org/packages/illuminatech/db-safedelete)
[![Total Downloads](https://img.shields.io/packagist/dt/illuminatech/db-safedelete.svg)](https://packagist.org/packages/illuminatech/db-safedelete)
[![Build Status](https://travis-ci.org/illuminatech/db-safedelete.svg?branch=master)](https://travis-ci.org/illuminatech/db-safedelete)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist illuminatech/db-safedelete
```

or add

```json
"illuminatech/db-safedelete": "*"
```

to the require section of your composer.json.


Usage
-----

This extension provides "safe" deletion for the Eloquent model, which attempts to invoke force delete, and, if it fails - falls back to soft delete.
