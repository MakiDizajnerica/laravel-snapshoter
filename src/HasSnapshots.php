<?php

namespace MakiDizajnerica\Snapshoter;

use MakiDizajnerica\Snapshoter\Models\Snapshot;
use MakiDizajnerica\Snapshoter\Facades\Snapshoter;
use MakiDizajnerica\Snapshoter\Contracts\Snapshotable;

trait HasSnapshots
{
    /**
     * Get all snapshots for the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function snapshots()
    {
        return $this->morphMany(Snapshot::class, 'snapshotable');
    }

    /**
     * The "boot" method of the trait.
     *
     * @return void
     */
    public static function bootHasSnapshots()
    {
        static::deleted(fn ($model) => $model->snapshots()->delete());
    }

    /**
     * Make snapshot for the model.
     * 
     * @return \MakiDizajnerica\Snapshoter\Models\Snapshot
     */
    public function makeSnapshot(): Snapshot
    {
        return Snapshoter::makeSnapshot($this);
    }

    /**
     * Revert model's state to the snapshot.
     * 
     * @param  \MakiDizajnerica\Snapshoter\Models\Snapshot|string|int $snapshot
     * @return \MakiDizajnerica\Snapshoter\Contracts\Snapshotable
     */
    public function revertToSnapshot($snapshot): Snapshotable
    {
        return Snapshoter::revertToSnapshot($this, $snapshot);
    }

    /**
     * Revert model's state to the previous snapshot.
     * 
     * @param  int $step
     * @return \MakiDizajnerica\Snapshoter\Contracts\Snapshotable
     */
    public function revertToPreviousSnapshot(int $step = 1): Snapshotable
    {
        $snapshot = $this->relationLoaded('snapshots')
            ? $this->snapshots->sortByDesc('id')->skip(abs($step))->first()
            : $this->snapshots()->latest()->skip(abs($step))->first();

        return $this->revertToSnapshot($snapshot);
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
