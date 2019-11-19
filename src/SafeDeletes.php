<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DbSafeDelete;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SafeDeletes
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait SafeDeletes
{
    use SoftDeletes;

    /**
     * @var bool whether the model is currently safe deleting.
     */
    protected $safeDeleting = true;

    /**
     * Perform the actual delete query on this model instance.
     * @see \Illuminate\Database\Eloquent\Model::performDeleteOnModel()
     * @see \Illuminate\Database\Eloquent\SoftDeletes::performDeleteOnModel()
     *
     * @return mixed
     */
    protected function performDeleteOnModel()
    {
        if ($this->forceDeleting) {
            $this->exists = false;

            return $this->setKeysForSaveQuery($this->newModelQuery())->forceDelete();
        }

        if (! $this->safeDeleting) {
            return $this->runSoftDelete();
        }

        try {
            $result = $this->setKeysForSaveQuery($this->newModelQuery())->forceDelete();
            $this->exists = false;

            return $result;
        } catch (\Throwable $e) {
            return $this->runSoftDelete();
        }
    }

    public function softDelete()
    {
        // @todo
    }
}
