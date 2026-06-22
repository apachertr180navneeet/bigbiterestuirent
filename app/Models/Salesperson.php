<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Salesperson extends Authenticatable
{
    use HasFactory, SoftDeletes; // ✅ Added


    // ✅ Explicit table name
    protected $table = 'salespersons';

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'address',
        'dob',
        'alternative_phone',
        'status',
        'salesperson_code',
        'user_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}