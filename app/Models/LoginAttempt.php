<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = 'login_attempts';
    protected $primaryKey = 'id_attempt';

    const UPDATED_AT = null;

    protected $guarded = ['id_attempt'];
}