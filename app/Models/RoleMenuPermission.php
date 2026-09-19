<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenuPermission extends Model
{
    public $timestamps = false;

    protected $table = 'role_menu_permissions';

    protected $guarded = ['id'];
}
