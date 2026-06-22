<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $table = 'receipts';

    protected $fillable = [
        'date',
        'receipt_no',
        'firm_id',
        'invoice_id',
        'amount',
        'given_amount',
        'discount_type',
        'discount',
        'final_amount',
        'sales_person',
        'mode',
        'manager_status',
        'status',
        'remark',
        'approval_remark',
        'user_id',
    ];

    public function firm()
    {
        return $this->belongsTo(Customer::class, 'firm_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

