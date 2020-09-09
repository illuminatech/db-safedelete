<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DbSafeDelete;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;

/**
 * SafeDeletes is an enhanced version of {@see \Illuminate\Database\Eloquent\SoftDeletes}.
 *
 * It changes `delete()` method in the way it attempts to invoke force delete, and, if it fails - falls back to soft delete.
 *
 * @see \Illuminate\Database\Eloquent\SoftDeletes
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

        if (! $this->safeDeleting || ! $this->forceDeleteAllowed()) {
            return $this->runSoftDelete();
        }

        $this->getConnection()->beginTransaction();

        try {
            $result = $this->setKeysForSaveQuery($this->newModelQuery())->forceDelete();
            $this->exists = false;

            $this->getConnection()->commit();

            return $result;
        } catch (QueryException $e) {
            $this->getConnection()->rollBack();

            return $this->runSoftDelete();
        } catch (\Throwable $e) {
            $this->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * Indicates whether this particular model is allowed to be force deleted or not.
     *
     * @return bool whether force delete is allowed for this particular model.
     */
    public function forceDeleteAllowed(): bool
    {
        return true;
    }

    /**
     * Marks this model as "deleted" without actual record removal.
     *
     * @return bool|null
     */
    public function softDelete()
    {
        $originSafeDeleting = $this->safeDeleting;

        $this->safeDeleting = false;

        return tap($this->delete(), function () use ($originSafeDeleting) {
            $this->safeDeleting = $originSafeDeleting;
        });
    }

    /**
     * Attempts to invoke force delete, and, if it fails - falls back to soft delete.
     *
     * @return bool|null
     */
    public function safeDelete()
    {
        $originSafeDeleting = $this->safeDeleting;

        $this->safeDeleting = true;

        return tap($this->delete(), function () use ($originSafeDeleting) {
            $this->safeDeleting = $originSafeDeleting;
        });
    }
}
