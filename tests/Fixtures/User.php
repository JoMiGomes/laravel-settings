<?php

namespace YellowParadox\LaravelSettings\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use YellowParadox\LaravelSettings\Traits\HasSettings;

class User extends Model
{
    use HasSettings;

    protected $fillable = ['name', 'email'];
}
