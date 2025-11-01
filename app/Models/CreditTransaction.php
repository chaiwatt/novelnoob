<?php

namespace App\Models;

use App\Models\User;
use App\Models\CreditPackage;
use App\Models\OnChargeTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'on_charge_transaction_id',
        'credit_package_id',
        'credits_added',
        'amount_paid',
        'status', // e.g., 'completed', 'pending', 'failed'
        'transaction_details', // Optional: Store payment gateway info, etc.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_details' => 'array',
        'amount_paid' => 'decimal:2', // Cast price to decimal with 2 places
    ];


    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package associated with the transaction.
     */
    public function creditPackage(): BelongsTo
    {
        return $this->belongsTo(CreditPackage::class);
    }

        /**
     * ⭐️ [เพิ่มใหม่]
     * Get the charge transaction that this credit transaction belongs to.
     */
    public function onChargeTransaction(): BelongsTo
    {
        // ⭐️ เชื่อมโยงกลับไปยัง OnChargeTransaction
        return $this->belongsTo(OnChargeTransaction::class, 'on_charge_transaction_id');
    }
}
