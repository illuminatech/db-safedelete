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
It works on top of regular Laravel [model soft deleting](https://laravel.com/docs/eloquent#soft-deleting) feature.

In case of usage of the relational database, which supports foreign keys, like MySQL, PostgreSQL etc., "soft" deletion
is widely used for keeping foreign keys consistence. For example: if user performs a purchase at the online shop, information
about this purchase should remain in the system for the future bookkeeping. The DDL for such data structure may look like the
following one:

```sql
CREATE TABLE `сustomers`
(
   `id` integer NOT NULL AUTO_INCREMENT,
   `name` varchar(64) NOT NULL,
   `address` varchar(64) NOT NULL,
   `phone` varchar(20) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE InnoDB;

CREATE TABLE `purchases`
(
   `id` integer NOT NULL AUTO_INCREMENT,
   `customer_id` integer NOT NULL,
   `item_id` integer NOT NULL,
   `amount` integer NOT NULL,
    PRIMARY KEY (`id`)
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
) ENGINE InnoDB;
```

Thus, while set up a foreign key from 'purchase' to 'user', 'ON DELETE RESTRICT' mode is used. So on attempt to delete
a user record, which have at least one purchase, a database error will occur. However, if user record have no external
reference, it can be deleted.

This extension introduces `Illuminatech\DbSafeDelete\SafeDeletes` trait, which serves as an enhanced version of standard
`Illuminate\Database\Eloquent\SoftDeletes`, allowing handing foreign key constraints and custom delete allowing logic.
Being attached to the model `Illuminatech\DbSafeDelete\SafeDeletes` changes model's regular `delete()` method in the way
it attempts to invoke force delete, and, if it fails - falls back to soft delete. For example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminatech\DbSafeDelete\SafeDeletes;

class Customer extends Model
{
    use SafeDeletes;

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    // ...
}

// if there is a foreign key reference :
$customerWithReference = Customer::query()
    ->whereHas('purchases')
    ->first();

$customerWithReference->delete(); // performs "soft" delete!

// if there is NO foreign key reference :
$customerWithoutReference = Customer::query()
    ->whereDoesntHave('purchases')
    ->first();

$customerWithoutReference->delete(); // performs actual delete!
```

**Heads up!** Make sure you do not attach both `Illuminate\Database\Eloquent\SoftDeletes` and `Illuminatech\DbSafeDelete\SafeDeletes`
in the same model class. It will cause PHP naming conflict error since `Illuminate\Database\Eloquent\SoftDeletes` is already
included into `Illuminatech\DbSafeDelete\SafeDeletes`.


### Smart deletion <span id="smart-deletion"></span>

Usually "soft" deleting feature is used to prevent the database history loss, ensuring data, which has been in use and
perhaps has a references or dependencies, is kept in the system. However, sometimes actual deleting is allowed for
such data as well.
For example: usually user account records should not be deleted but only marked as "trashed", however if you browse
through users list and found accounts, which has been registered long ago, but don't have at least single log-in in the
system, these records have no value for the history and can be removed from database to save a disk space.

You can make "soft" deletion to be "smart" and detect, if the record can be removed from the database or only marked as "trashed".
This can be done via `Illuminatech\DbSafeDelete\SafeDeletes::forceDeleteAllowed()`. For example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminatech\DbSafeDelete\SafeDeletes;

class User extends Model
{
    use SafeDeletes;

    public function forceDeleteAllowed(): bool
    {
        return $this->last_login_at === null;
    }

    // ...
}

$user = User::query()->whereNull('last_login_at')->first();
$user->delete(); // removes the record!!!

$user = User::query()->whereNotNull('last_login_at')->first();
$user->delete(); // marks record as "trashed"
```


### Manual delete flow control <span id="manual-delete-flow-control"></span>

Using `Illuminatech\DbSafeDelete\SafeDeletes` you can still manually "soft" delete or "force" delete a particular record, using
following methods:

- `softDelete()` - always performs "soft" deletion.
- `forceDelete()` - always performs actual deletion.
- `safeDelete()` - attempts to perform actual deletion, if it fails - applies "soft" one.

For example:

```php
<?php

// if there is a foreign key reference :
$customerWithReference = Customer::query()
    ->whereHas('purchases')
    ->first();

$customerWithReference->forceDelete(); // performs actual delete (triggers a database error actually)!

// if there is NO foreign key reference :
$customerWithoutReference = Customer::query()
    ->whereDoesntHave('purchases')
    ->first();

$customerWithoutReference->softDelete(); // performs "soft" delete!

// if there is a foreign key reference :
$customerWithReference = Customer::query()
    ->whereHas('purchases')
    ->first();

$customerWithReference->safeDelete(); // performs "soft" delete!

// if there is NO foreign key reference :
$customerWithoutReference = Customer::query()
    ->whereDoesntHave('purchases')
    ->first();

$customerWithoutReference->safeDelete(); // performs actual delete!
```
