<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedirectLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'redirect_id',
        'ip_address',
        'user_agent',
        'referer',
        'query_params',
        'action'
    ];

    protected $casts = [
        'query_params' => 'array',
    ];

    
    public function redirect()
    {
        return $this->belongsTo(Redirect::class);
    }

    /**
     * Filtra logs dos últimos N dias
     */
    public function scopeFromLastDays($query, $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
