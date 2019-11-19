<?php

namespace Illuminatech\DbSafeDelete\Test\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $item_id
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminatech\DbSafeDelete\Test\Support\Item $item
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class Purchase extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'item_id',
        'invoice_number',
    ];

    public function item(): HasOne
    {
        return $this->hasOne(Item::class);
    }
}
