<?php

namespace MakiDizajnerica\Snapshoter;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;
use MakiDizajnerica\Snapshoter\Models\Snapshot;
use MakiDizajnerica\Snapshoter\Contracts\Snapshotable;

class SnapshotManager
{
    /**
     * Make snapshot for the model.
     * 
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable $model
     * @return \MakiDizajnerica\Snapshoter\Models\Snapshot
     */
    public function makeSnapshot(Snapshotable $model): Snapshot
    {
        $state = Arr::map(
            $model->snapshotAttributes(),
            fn ($value) => $value instanceof Collection
                ? $value->modelKeys()
                : $value
        );

        return $model->snapshots()->create(['state' => $state]);
    }

    /**
     * Retrieve snapshot for the model.
     * 
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable $model
     * @param  \MakiDizajnerica\Snapshoter\Models\Snapshot|string|int $snapshot
     * @return \MakiDizajnerica\Snapshoter\Models\Snapshot|null
     */
    public function retrieveSnapshot(Snapshotable $model, $snapshot): ?Snapshot
    {
        if (! $snapshot) {
            return null;
        }

        if ($snapshot instanceof Snapshot) {
            return $snapshot;
        }

        if (is_string($snapshot)) {
            return $this->retrieve($model, $snapshot, 'uuid');
        }

        return $this->retrieve($model, $snapshot, 'id');
    }

    /**
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable $model
     * @param  mixed $value
     * @param  string $field
     * @return \MakiDizajnerica\Snapshoter\Models\Snapshot|null
     */
    protected function retrieve(Snapshotable $model, $value, $field = 'id'): ?Snapshot
    {
        // We are first going to check if model relation "snapshots" is loaded
        // and we are going to take the snapshot from there. *optimisation*
        return $model->relationLoaded('snapshots')
            ? $model->snapshots->firstWhere($field, $value)
            : $model->snapshots()->firstWhere($field, $value);
    }

    /**
     * Revert model's state to the snapshot.
     * 
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable $model
     * @param  \MakiDizajnerica\Snapshoter\Models\Snapshot|string|int $snapshot
     * @return \MakiDizajnerica\Snapshoter\Contracts\Snapshotable
     */
    public function revertToSnapshot(Snapshotable $model, $snapshot): Snapshotable
    {
        $snapshot = $this->retrieveSnapshot($model, $snapshot);

        if (! $snapshot) {
            return $model;
        }

        if (config('snapshoter.delete_newer_snapshots_on_revert')) {
            $model->snapshots()->where('id', '>', $snapshot->getKey())->delete();
        }

        return $this->revert($model, $snapshot);
    }

    /**
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable $model
     * @param  \MakiDizajnerica\Snapshoter\Models\Snapshot $snapshot
     * @return \MakiDizajnerica\Snapshoter\Contracts\Snapshotable
     * 
     * @todo Handle model relations.
     */
    protected function revert(Snapshotable $model, Snapshot $snapshot): Snapshotable
    {
        $state = $snapshot->state;

        // Here we use "saveQuietly" because technically we are not updating the model,
        // we are just returning it to the previous state and we don't want any events to fire.
        $model->forceFill($state)->saveQuietly();

        return $model;
    }
}
