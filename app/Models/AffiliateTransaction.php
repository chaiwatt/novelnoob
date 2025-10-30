<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateTransaction extends Model
{
    protected $fillable = [
        'referrer_user_id', 
        'credit_package_id',
        'referrer_masked_email'
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function package()
    {
        return $this->belongsTo(CreditPackage::class, 'credit_package_id');
    }
}
