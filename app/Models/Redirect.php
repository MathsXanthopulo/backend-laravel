<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Redirect extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'destination_url',
        'query_params',
        'is_active',
        'last_accessed_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_accessed_at' => 'datetime',
    ];

    protected $appends = ['code'];

    /**
     * Gera o código Hashids baseado no ID do redirect
     * Converte o ID numérico em um código curto e legível
     */
    public function getCodeAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Relacionamento com os logs de acesso
     * Um redirect pode ter muitos logs de acesso
     */
    public function logs()
    {
        return $this->hasMany(RedirectLog::class);
    }

    /**
     * Filtra apenas redirects ativos
     * Útil para mostrar apenas links que estão funcionando
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Encontra um redirect pelo código Hashids
     * Converte o código de volta para ID e busca no banco
     */
    public static function findByCode($code)
    {
        $id = Hashids::decode($code);
        return $id ? static::find($id[0]) : null;
    }

    /**
     * Monta a URL final combinando a URL base com parâmetros
     * Mescla parâmetros salvos no redirect com parâmetros da requisição
     */
    public function getFinalUrl($requestQueryParams = [])
    {
        $url = $this->destination_url;
        
        // Parse existing query params from redirect
        $redirectParams = [];
        if ($this->query_params) {
            parse_str($this->query_params, $redirectParams);
        }
        
        // Combina parâmetros do redirect com os da requisição (requisição tem prioridade)
        $finalParams = array_merge($redirectParams, $requestQueryParams);
        
        // Remove valores vazios para manter apenas parâmetros válidos
        $finalParams = array_filter($finalParams, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Constrói a URL final com todos os parâmetros
        if (!empty($finalParams)) {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . http_build_query($finalParams);
        }
        
        return $url;
    }
}
