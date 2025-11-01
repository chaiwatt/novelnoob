<?php

namespace App\Models;

use App\Models\CreditTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OnChargeTransaction extends Model
{
    protected $fillable = [
        'source_id',
        'charge_id',
        'status',
        'paid_at',
    ];

        /**
     * ⭐️ [เพิ่มใหม่]
     * Get the credit transaction associated with this charge. (หนึ่ง Charge มีได้หนึ่ง Transaction)
     */
    public function creditTransaction(): HasOne
    {
        // ⭐️ เชื่อมโยงไปยัง CreditTransaction โดยใช้ foreign key 'on_charge_transaction_id'
        return $this->hasOne(CreditTransaction::class, 'on_charge_transaction_id');
    }
}

