<?php

namespace MakiDizajnerica\Snapshoter;

use Illuminate\Database\Eloquent\Collection;
use MakiDizajnerica\Snapshoter\Models\Snapshot;
use MakiDizajnerica\Snapshoter\Contracts\Snapshotable;

class SnapshotManager
{
    public function makeSnapshot(Snapshotable $model): Snapshot
    {
        $fields = $model->snapshotFields();
        $fields = $model->only($fields);

        foreach ($fields as $key => $value) {
            if ($value instanceof Collection) {
                $fields[$key] = $value->modelKeys();
            }
        }

        return $model->snapshots()->create(['data' => $fields]);
    }

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

    protected function retrieve(Snapshotable $model, $value, $field = 'id'): ?Snapshot
    {
        if ($model->relationLoaded('snapshots')) {
            return $model->snapshots->firstWhere($field, $value);
        }

        return $model->snapshots()->firstWhere($field, $value);
    }

    public function revertToSnapshot(Snapshotable $model, $snapshot): Snapshotable
    {
        $snapshot = $this->retrieveSnapshot($model, $snapshot);

        if (! $snapshot) {
            return $model;
        }

        $model->snapshots()
            ->where('id', '>', $snapshot->getKey())
            ->delete();

        return $this->revert($model, $snapshot->data);
    }

    protected function revert(Snapshotable $model, array $fields): Snapshotable
    {
        // @todo handle relations

        $model->forceFill($fields)->saveQuietly();

        return $model;
    }
}
