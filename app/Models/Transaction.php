<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'reference',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transaction types
     */
    const TYPE_TOP_UP = 'top_up';
    const TYPE_CHARGE = 'charge';

    /**
     * Response messages
     */
    const MSG_INSUFFICIENT_BALANCE = 'Insufficient balance';
    const MSG_TOP_UP_SUCCESSFUL = 'Top-up successful';
    const MSG_CHARGE_SUCCESSFUL = 'Charge successful';
    const MSG_ACCOUNT_CREATED = 'Account created successfully';
}
