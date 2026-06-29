<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'description',
    'customer_name',
    'customer_email',
    'amount',
    'currency',
    'status',
    'stripe_session_id',
    'stripe_payment_intent',
    'checkout_url',
    'paid_at',
])]
class PagosPayment extends Model
{
    protected $table = 'pagos_payments';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }
}
