<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use SecureRouteBinding;
}
