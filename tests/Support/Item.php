<?php

namespace Illuminatech\DbSafeDelete\Test\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminatech\DbSafeDelete\SafeDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Illuminatech\DbSafeDelete\Test\Support\Purchase[] $purchases
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class Item extends Model
{
    use SafeDeletes;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'price',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
