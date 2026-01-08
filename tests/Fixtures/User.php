<?php

namespace JomiGomes\LaravelSettings\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use JomiGomes\LaravelSettings\Traits\HasSettings;

class User extends Model
{
    use HasSettings;

    protected $fillable = ['name', 'email'];
}
