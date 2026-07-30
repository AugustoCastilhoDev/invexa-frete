<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use BelongsToEmpresa;
}
