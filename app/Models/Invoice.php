<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'date',
        'invoice_no',
        'firm_id',
        'salesperson_id',
        'amount',
        'discount_percent',
        'discount_amount',
        'payable_amount',
        'status',
        'user_id',
    ];

    public function firm()
    {
        return $this->belongsTo(Customer::class, 'firm_id');
    }

    public function salesperson()
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
