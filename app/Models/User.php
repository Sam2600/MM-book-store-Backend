<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role_id',
        'password',
        'coins',
        'payment_method_id',
        'payment_account',
        'email_verified_at',
        'email_verification_token',
        'email_verification_token_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_token_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function views()
    {
        return $this->hasMany(NovelView::class);
    }

    public function novels()
    {
        return $this->hasMany(Novel::class, 'translator_id');
    }

    public function authorEarnings()
    {
        return $this->hasMany(AuthorEarning::class, 'translator_id');
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class, 'translator_id');
    }

    public function coinHistories()
    {
        return $this->hasMany(CoinHistory::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'user_id');
    }
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'user_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
