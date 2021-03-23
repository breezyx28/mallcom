<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    // protected $appends = ['position'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getThumbnailAttribute($value)
    {
        // replace http://localhost to by htpp://127.0.0.1
        $base_url = str_replace('localhost', env('DB_HOST'), env('APP_URL'));

        return $base_url . ':' . $_SERVER['SERVER_PORT'] . "/storage/" . $value;
    }
}