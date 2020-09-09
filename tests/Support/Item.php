<?php

namespace Illuminatech\DbSafeDelete\Test\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminatech\DbSafeDelete\SafeDeletes;

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

    public $allowForceDelete = true;

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * @see \Illuminatech\DbSafeDelete\SafeDeletes::forceDeleteAllowed()
     * @return bool whether force delete is allowed for this particular model.
     */
    public function forceDeleteAllowed(): bool
    {
        return $this->allowForceDelete;
    }
}
