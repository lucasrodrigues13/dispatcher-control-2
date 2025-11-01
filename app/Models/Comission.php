<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comission extends Model
{
    use HasFactory;

    protected $table = "commissions";

    protected $fillable = [
        'dispatcher_id',
        'deal_id',
        'employee_id',
        'value',
    ];

    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
