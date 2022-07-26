<?php

namespace MakiDizajnerica\Snapshoter;

use MakiDizajnerica\Snapshoter\Models\Snapshot;
use MakiDizajnerica\Snapshoter\Contracts\Snapshotable;
use MakiDizajnerica\Snapshoter\Facades\Snapshoter as SnapshoterFacade;

trait HasSnapshots
{
    public function snapshots()
    {
        return $this->morphMany(Snapshot::class, 'snapshotable');
    }

    public static function bootHasSnapshots()
    {
        static::deleting(fn ($model) => $model->snapshots()->delete());
    }

    public function makeSnapshot(): Snapshot
    {
        return SnapshoterFacade::makeSnapshot($this);
    }

    public function revertToPreviousSnapshot(): Snapshotable
    {
        $snapshot = $this->snapshots()
            ->latest()
            ->skip(1)
            ->first();

        return $this->revertToSnapshot($snapshot);
    }

    public function revertBackSnapshots(int $skip = 1): Snapshotable
    {
        if ($skip <= 1) {
            return $this->revertToPreviousSnapshot();
        }

        $snapshot = $this->snapshots()
            ->latest()
            ->skip($skip)
            ->first();

        return $this->revertToSnapshot($snapshot);
    }

    public function revertToSnapshot($snapshot): Snapshotable
    {
        return SnapshoterFacade::revertToSnapshot($this, $snapshot);
    }

    public static function createWithSnapshot(array $attributes = []): static
    {
        $model = new static($attributes);

        $model->saveWithSnapshot();

        return $model;
    }

    public function updateWithSnapshot(array $attributes = [], array $options = []): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->saveWithSnapshot($options);
    }

    public function saveWithSnapshot(array $options = []): bool
    {
        $save = $this->save($options);

        $this->makeSnapshot();

        return $save;
    }
}
