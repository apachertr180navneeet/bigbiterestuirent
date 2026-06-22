<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;


    // ✅ Explicit table name
    protected $table = 'customers';

    protected $fillable = [
        'firm_name',
        'name',
        'phone',
        'gst_no',
        'address_1',
        'address_2',
        'city',
        'state',
        'discount',
        'status',
        'user_id',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active customers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive customers
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}