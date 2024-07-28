<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Tests\Models;

use Hennest\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Hennest\TwoFactor\Tests\database\factories\UserFactory;
use Hennest\TwoFactor\Traits\HasTwoFactorAuthentication;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as BaseUser;

final class User extends BaseUser implements Authenticatable, TwoFactorAuthenticatable
{
    use HasFactory;
    use HasTwoFactorAuthentication;
    use HasUlids;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory(): UserFactory
    {
        return new UserFactory;
    }
}
