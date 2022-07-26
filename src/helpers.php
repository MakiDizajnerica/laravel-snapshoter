<?php

use MakiDizajnerica\Snapshoter\Contracts\Snapshotable;

if (! function_exists('snapshoter')) {
    /**
     * Get model snapshot manager instance.
     *
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable|null $model
     * @return \MakiDizajnerica\Snapshoter\SnapshotManager|\MakiDizajnerica\Snapshoter\Models\Snapshot
     */
    function snapshoter(?Snapshotable $model = null)
    {
        $manager = app('makidizajnerica-snapshoter');

        if ($model) {
            return $manager->makeSnapshot($model);
        }

        return $manager;
    }
}
