<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'subscription_id',
        'subscription_type',
        'status',
        'real_status',
        'start_date',
        'expire_date',
        'renewal_date',
        'package',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'expire_date' => 'datetime',
        'renewal_date' => 'datetime'
    ];

    CONST EVENT_TYPE_CANCEL = 'cancel';
    CONST EVENT_TYPE_RENEWAL = 'renewal';

    /**
     * @param array $account
     * @param array $subs
     * @return void
     */
    public function prepareAndSave(array $account, array $subs)
    {
        $data = [
            'account_id'=> $account['id'],
            'subscription_id' => $subs['result']['profile']['subscriptionId'],
            'subscription_type' => $subs['result']['profile']['subscriptionType'],
            'status' => $subs['result']['profile']['status'],
            'real_status' => $subs['result']['profile']['realStatus'],
            'start_date' => $subs['result']['profile']['startDate'],
            'expire_date' => $subs['result']['profile']['expireDate'],
            'renewal_date' => $subs['result']['profile']['renewalDate'],
            'package' => $subs['result']['profile']['package']
        ];
        $this->fill($data);
        $this->save();
    }

}
