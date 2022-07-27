<?php

namespace MakiDizajnerica\Snapshoter\Contracts;

interface Snapshotable
{
    /**
     * Get fields for the snapshot.
     *
     * @return array<string, mixed>
     */
    public function snapshotFields(): array;
}
