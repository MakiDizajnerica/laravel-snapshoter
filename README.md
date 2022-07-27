# Laravel Snapshoter

Laravel model snapshoter. Make snapshots for the models, revert model state to previous snapshot.

## Installation

```bash
composer require makidizajnerica/laravel-snapshoter
```

After that be sure to run migrations to generate `snapshots` table:

```bash
php artisan migrate
```

## Usage

Your model needs to implement `MakiDizajnerica\Snapshoter\Contracts\Snapshotable` and also to use `MakiDizajnerica\Snapshoter\HasSnapshots` trait.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MakiDizajnerica\Snapshoter\HasSnapshots;
use MakiDizajnerica\Snapshoter\Contracts\Snapshotable as SnapshotableContract;

class Project extends Model implements SnapshotableContract
{
    use HasSnapshots;

    // ...
}
```

You will then have some methods available on your model:

```php
<?php

use App\Models\Project;
use MakiDizajnerica\Snapshoter\Models\Snapshot;

// Make snapshot for the model
$project = Project::first();
$project->makeSnapshot();

// Revert model's state to previous snapshot
$project->revertToPreviousSnapshot();

// Revert model's state to the snapshot from a few steps back
$project->revertBackSnapshots(3);

// You can also pass Snapshot instance
$snapshot = Snapshot::first();
$project->revertToSnapshot($snapshot);
```

If you want to *create*, *update* or *save* your model with the snapshot, you can use:

```php
<?php

use App\Models\Project;

// Create model with initial snapshot
$project = Project::createWithSnapshot([/* ... */]);

// Update model and make snapshot
$project->updateWithSnapshot([/* ... */]);

// Save model and make snapshot
$project->fill([/* ... */])->saveWithSnapshot();
```

Another option would be to use observer and automatically make snapshot on model *create* or *update*: 

```php
<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     *
     * @param  \App\Models\Project $project
     * @return void
     */
    public function created(Project $project)
    {
        $project->makeSnapshot();
    }

    /**
     * Handle the Project "updated" event.
     *
     * @param  \App\Models\Project $project
     * @return void
     */
    public function updated(Project $project)
    {
        $project->makeSnapshot();
    }
}
```

## Author

**Nemanja Marijanovic** (<n.marijanovic@hotmail.com>) 

## Licence

Copyright © 2021, Nemanja Marijanovic <n.marijanovic@hotmail.com>

All rights reserved.

For the full copyright and license information, please view the LICENSE 
file that was distributed within the source root of this package.