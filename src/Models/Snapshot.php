<?php

namespace MakiDizajnerica\Snapshoter\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use MakiDizajnerica\Snapshoter\Collections\SnapshotCollection;

class Snapshot extends Model
{
    protected $table = 'snapshots';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'data',
    ];

    protected $hidden = [
        //
    ];

    protected $appends = [
        //
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(fn ($snapshot) => $snapshot->forceFill([
            'uuid' => Str::uuid()->toString(),
            'created_at' => now(),
        ]));
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array $models
     * @return \MakiDizajnerica\Snapshoter\Collections\SnapshotCollection
     */
    public function newCollection(array $models = [])
    {
        return new SnapshotCollection($models);
    }

    /**
     * Get snapshotable model instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function snapshotable()
    {
        return $this->morphTo();
    }
}
