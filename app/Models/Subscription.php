<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'subscriptions';
    protected $name = 'SUBSCRIPTION';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'plan_name',
        'status',
        'starts_at',
        'ends_at',
        'strip_subscription_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
