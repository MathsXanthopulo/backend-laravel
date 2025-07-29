<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use App\Models\RedirectLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class RedirectActionController extends Controller
{
    /**
     * Executa o redirecionamento quando alguém acessa um link curto
     * Registra o acesso nos logs e redireciona para a URL de destino
     *
     * @param  string  $code
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(string $code, Request $request): RedirectResponse
    {
        $redirect = Redirect::findByCode($code);
        
        if (!$redirect || !$redirect->is_active) {
            abort(404, 'Redirect não encontrado ou inativo');
        }

        // Captura parâmetros da requisição (como ?utm_source=google)
        $requestQueryParams = $request->query();
        
        // Remove valores vazios para manter apenas parâmetros válidos
        $requestQueryParams = array_filter($requestQueryParams, function($value) {
            return $value !== '' && $value !== null;
        });

        // Monta a URL final combinando parâmetros do redirect com os da requisição
        $finalUrl = $redirect->getFinalUrl($requestQueryParams);

        // Registra o acesso nos logs para estatísticas e visualização
        RedirectLog::create([
            'redirect_id' => $redirect->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'query_params' => $requestQueryParams,
            'action' => 'access'
        ]);

        // Atualiza o último acesso
        $redirect->update(['last_accessed_at' => now()]);

        return redirect()->away($finalUrl);
    }

    /**
     * Gera estatísticas detalhadas de um link curto
     *
     * @param  string  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(string $code): JsonResponse
    {
        $redirect = Redirect::findByCode($code);
        
        if (!$redirect) {
            return response()->json(['message' => 'Redirect não encontrado'], 404);
        }

        // Calcula total de acessos
        $totalAccesses = $redirect->logs()->count();
        
        // Conta IPs únicos (usuários diferentes)
        $uniqueAccesses = $redirect->logs()->distinct('ip_address')->count();
        
        // Lista os principais sites que direcionaram tráfego para este link
        $topReferrers = $redirect->logs()
            ->whereNotNull('referer')
            ->select('referer', DB::raw('count(*) as count'))
            ->groupBy('referer')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'referer' => $item->referer,
                    'count' => $item->count
                ];
            });

        // Gera estatísticas dos últimos 10 dias para gráficos
        $last10Days = collect();
        for ($i = 9; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            
            $dayStats = $redirect->logs()
                ->whereDate('created_at', $date)
                ->select(
                    DB::raw('count(*) as total'),
                    DB::raw('count(distinct ip_address) as unique_ips')
                )
                ->first();

            $last10Days->push([
                'date' => $date,
                'total' => $dayStats ? $dayStats->total : 0,
                'unique' => $dayStats ? $dayStats->unique_ips : 0,
            ]);
        }

        return response()->json([
            'data' => [
                'total_accesses' => $totalAccesses,
                'unique_accesses' => $uniqueAccesses,
                'top_referrers' => $topReferrers,
                'last_10_days' => $last10Days,
            ]
        ]);
    }

    /**
     * Lista todos os logs de acesso de um link curto
     *
     * @param  string  $code
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logs(string $code, Request $request): JsonResponse
    {
        $redirect = Redirect::findByCode($code);
        
        if (!$redirect) {
            return response()->json(['message' => 'Redirect não encontrado'], 404);
        }

        $perPage = $request->get('per_page', 15);
        
        $logs = $redirect->logs()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ]
        ]);
    }
}
