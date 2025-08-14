<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /**
     * The attributes that should be visible for serialization.
     * Tüm users tablosu kolonlarını buraya ekle.
     *
     * @var list<string>
     */
    protected $visible = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'profile_photo_path',
        'role_id',
        'role',
        'unvan',
        'proses',
        'tip',
        'aktif',
        'durum_tarih',
        'durum_yapan_id',
        'ismerkezi_id',
        'istasyon_id',
        'operasyon_id',
        'register_id',
        'co_id',
        'created_at',
        'updated_at',
        // eklemek istediğin başka kolonlar varsa buraya ekle
    ];
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * Spatie Permission guard name to use for this model.
     * Ensures role/permission lookups work when using Sanctum API guard.
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'profile_photo_path',
        'role_id',
        'role',
        'unvan',
        'proses',
        'tip',
        'aktif',
        'durum_tarih',
        'durum_yapan_id',
        'ismerkezi_id',
        'istasyon_id',
        'operasyon_id',
        'register_id',
        'co_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Build CASL ability rules from user's roles and permissions.
     * - Spatie permission names like `view_users` map to { action: 'read', subject: 'users' }
     * - `edit_*` maps to { action: 'update', ... }
     * - Bare permission like `montaj` or `genel` maps to { action: 'read', subject: <name> }
     * - `manage` (global) maps to { manage, all }
     * - Roles are checked case-insensitively for specials like `admin` and `dashboard`
     */
    public function getAbilityRules(): array
    {
        // Collect roles and permissions (lowercased) using Spatie helpers first
        $roles = $this->getRoleNames()->map(function ($r) {
            return strtolower($r);
        });
        $permissions = $this->getAllPermissions()->pluck('name')->map(function ($p) {
            return strtolower($p);
        });

        // Also merge with direct relations (ignoring guard filters) to be robust
        try {
            $relRoles = $this->roles()->pluck('name')->map(function ($r) {
                return strtolower($r);
            });
            $roles = $roles->merge($relRoles)->unique()->values();
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $directPerms = $this->permissions()->pluck('name')->map(function ($p) {
                return strtolower($p);
            });

            $rolePermIds = DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
                ->join(config('permission.table_names.model_has_roles', 'model_has_roles'), 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_id', $this->getKey())
                ->where('model_has_roles.model_type', $this->getMorphClass())
                ->pluck('permission_id');

            $rolePerms = \Spatie\Permission\Models\Permission::whereIn('id', $rolePermIds)
                ->pluck('name')
                ->map(function ($p) {
                    return strtolower($p);
                });

            $permissions = $permissions->merge($directPerms)->merge($rolePerms)->unique()->values();
        } catch (\Throwable $e) {
            // ignore
        }

        $mapAction = function (string $a) {
            if ($a === 'view') return 'read';
            if ($a === 'edit') return 'update';
            return $a;
        };

        $verbs = ['view', 'read', 'create', 'edit', 'update', 'delete'];

        // Build rules with de-duplication
        $unique = [];
        $push = function (string $action, string $subject) use (&$unique) {
            $key = $action . '|' . $subject;
            $unique[$key] = ['action' => $action, 'subject' => $subject];
        };

        foreach ($permissions as $perm) {
            $parts = explode('_', $perm, 2);
            if (count($parts) === 2) {
                [$act, $subj] = $parts;
                $push($mapAction($act), $subj);
            } else {
                if ($perm === 'manage') {
                    $push('manage', 'all');
                } elseif ($perm !== '' && !in_array($perm, $verbs, true)) {
                    // bare domain permission -> read/<subject>
                    $push('read', $perm);
                }
            }
        }

        // Special roles (admin no longer implies manage all automatically)
        // if ($roles->contains('dashboard')) {
        //     $push('read', 'dashboard');
        //     $push('read', 'genel');
        // }

        return array_values($unique);
    }
}
