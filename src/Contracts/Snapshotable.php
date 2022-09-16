<?php

namespace MakiDizajnerica\Snapshoter\Contracts;

interface Snapshotable
{
    /**
     * Get attributes for the snapshot.
     *
     * @return array<string, mixed>
     */
    public function snapshotAttributes(): array;
}
