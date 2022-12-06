<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'token',
        'country_id'
    ];

    public function authorProjects()
    {
        return $this->hasMany(Project::class, 'user_id', 'id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function country()
    {
        return $this->belongsTo(Country::class)->first();
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }
}
