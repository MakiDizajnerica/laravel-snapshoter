<?php

namespace MakiDizajnerica\Snapshoter\Contracts;

interface Snapshotable
{
    /**
     * Get fields for the snapshot.
     *
     * @return array
     */
    public function snapshotFields(): array;
}
