<?php
// Quick CLI probe for user abilities and roles/permissions
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$id = (int)($argv[1] ?? 1);
$user = User::find($id);
if (!$user) {
    fwrite(STDERR, "User $id not found\n");
    exit(1);
}

$rolesHelper = $user->getRoleNames();
$permsHelper = $user->getAllPermissions()->pluck('name');
$rolesRel = $user->roles()->pluck('name');
$permsDirect = $user->permissions()->pluck('name');
$rolePermIds = DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
    ->join(config('permission.table_names.model_has_roles', 'model_has_roles'), 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
    ->where('model_has_roles.model_id', $user->getKey())
    ->where('model_has_roles.model_type', $user->getMorphClass())
    ->pluck('permission_id');
$permsViaRoles = Spatie\Permission\Models\Permission::whereIn('id', $rolePermIds)->pluck('name');

try {
    $rules = $user->getAbilityRules();
} catch (Throwable $e) {
    $rules = ['error' => $e->getMessage()];
}

$result = [
    'user' => ['id' => $user->id, 'email' => $user->email],
    'roles_helper' => $rolesHelper,
    'perms_helper' => $permsHelper,
    'roles_rel' => $rolesRel,
    'perms_direct' => $permsDirect,
    'perms_via_roles' => $permsViaRoles,
    'ability_rules' => $rules,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
