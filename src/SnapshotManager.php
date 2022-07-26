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
        $fields = Arr::map(
            $model->snapshotFields(),
            fn ($value) => $value instanceof Collection
                ? $value->modelKeys()
                : $value
        );

        return $model->snapshots()->create(['data' => $fields]);
    }

    /**
     * Retrieve snapshot for the model.
     * 
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable $model
     * @param  \MakiDizajnerica\Snapshoter\Models\Snapshot|int|string $snapshot
     * @return \MakiDizajnerica\Snapshoter\Models\Snapshot|null
     */
    public function retrieveSnapshot(Snapshotable $model, $snapshot): ?Snapshot
    {
        if (! $snapshot) {
            return null;
        }

        switch (true) {
            case ($snapshot instanceof Snapshot):
                return $snapshot;
            case is_string($snapshot):
                return $this->retrieve($model, $snapshot, 'uuid');
            default:
                return $this->retrieve($model, $snapshot);
        }
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
        if ($model->relationLoaded('snapshots')) {
            return $model->snapshots->firstWhere($field, $value);
        }

        return $model->snapshots()->firstWhere($field, $value);
    }

    /**
     * Revert model state from the snapshot.
     * 
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable $model
     * @param  \MakiDizajnerica\Snapshoter\Models\Snapshot|int|string $snapshot
     * @return \MakiDizajnerica\Snapshoter\Contracts\Snapshotable
     * 
     * @todo Maybe define config property for deleting snapshots newer that current "$snapshot".
     */
    public function revertToSnapshot(Snapshotable $model, $snapshot): Snapshotable
    {
        $snapshot = $this->retrieveSnapshot($model, $snapshot);

        if (! $snapshot) {
            return $model;
        }

        // We are going to delete all snapshots that are newer that the current "$snapshot",
        // because we don't want to run back and forth between all saved snapshots.
        $model->snapshots()
            ->where('id', '>', $snapshot->getKey())
            ->delete();

        return $this->revert($model, $snapshot->data);
    }

    /**
     * @param  \MakiDizajnerica\Snapshoter\Contracts\Snapshotable $model
     * @param  array $fields
     * @return \MakiDizajnerica\Snapshoter\Contracts\Snapshotable
     * 
     * @todo Handle model relations.
     */
    protected function revert(Snapshotable $model, array $fields): Snapshotable
    {
        // Here we use "saveQuietly" because technically we are not updating the model,
        // we are just returning it to the previous state and we don't want any events to fire.
        $model->forceFill($fields)->saveQuietly();

        return $model;
    }
}
