<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const FACILITY_MANAGER = 'Pengelola Fasilitas';

    public const PUBLIC = 'Masyarakat';

    public const ADMIN = 'Admin';

    public const PROPOSAL_ROLES = [self::PUBLIC, self::ADMIN, self::FACILITY_MANAGER];

    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    public $timestamps = false;

    protected $guarded = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}
