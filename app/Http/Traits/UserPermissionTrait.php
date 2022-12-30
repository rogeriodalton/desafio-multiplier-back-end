<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;

trait UserPermissionTrait{

    public function getPermission($loggedIn, int $groupId = 0)
    {
        $hasPermission = (DB::table('users')
                           ->join('user_groups', 'user_groups.user_id', 'users.id')
                           ->select('users.id')
                           ->where('users.active', true)
                           ->where('users.id', $loggedIn->id)
                           ->where('user_groups.group_id', $groupId)
                           ->first()) <> null;

        return $hasPermission;
    }

    public function getPermissionEmail(string $email = '', int $groupId = 0)
    {
        $hasPermission = (DB::table('users')
                           ->join('user_groups', 'user_groups.user_id', 'users.id')
                           ->select('users.id')
                           ->where('users.active', true)
                           ->where('users.email', $email)
                           ->where('user_groups.group_id', $groupId)
                           ->first()) <> null;

        return $hasPermission;
    }


}

?>
