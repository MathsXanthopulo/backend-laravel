<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use App\Http\Requests\StoreRedirectRequest;
use App\Http\Requests\UpdateRedirectRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RedirectController extends Controller
{
    /**
     * Mostra todos os links curtos cadastrados no sistema
     * Retorna uma lista com código, status, URL de destino e contador de acessos
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $redirects = Redirect::select([
            'id',
            'destination_url',
            'query_params',
            'is_active',
            'last_accessed_at',
            'created_at',
            'updated_at'
        ])->withCount('logs as access_count')->get();

        return response()->json([
            'data' => $redirects->map(function ($redirect) {
                return [
                    'code' => $redirect->code,
                    'status' => $redirect->is_active ? 'ativo' : 'inativo',
                    'destination_url' => $redirect->destination_url,
                    'last_accessed_at' => $redirect->last_accessed_at,
                    'created_at' => $redirect->created_at,
                    'updated_at' => $redirect->updated_at,
                    'access_count' => $redirect->access_count,
                ];
            })
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRedirectRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRedirectRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Separa os parâmetros da URL (como ?utm_source=google) da URL base
        $urlParts = parse_url($data['destination_url']);
        $queryParams = null;
        
        if (isset($urlParts['query'])) {
            $queryParams = $urlParts['query'];
            // Remove os parâmetros da URL de destino para armazenar separadamente
            $data['destination_url'] = $urlParts['scheme'] . '://' . $urlParts['host'];
            if (isset($urlParts['port'])) {
                $data['destination_url'] .= ':' . $urlParts['port'];
            }
            if (isset($urlParts['path'])) {
                $data['destination_url'] .= $urlParts['path'];
            }
        }
        
        $data['query_params'] = $queryParams;
        
        $redirect = Redirect::create($data);

        // Registra a criação do link nos logs para visualização
        \App\Models\RedirectLog::create([
            'redirect_id' => $redirect->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'query_params' => [],
            'action' => 'created'
        ]);

        return response()->json([
            'message' => 'Redirect criado com sucesso',
            'data' => [
                'code' => $redirect->code,
                'destination_url' => $redirect->destination_url,
                'query_params' => $redirect->query_params,
                'is_active' => $redirect->is_active,
            ]
        ], 201);
    }

    /**
     * Mostra os detalhes de um link curto específico
     * Usa o código Hashids para encontrar o redirect no banco
     *
     * @param  string  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $code): JsonResponse
    {
        $redirect = Redirect::findByCode($code);
        
        if (!$redirect) {
            return response()->json(['message' => 'Redirect não encontrado'], 404);
        }

        return response()->json([
            'data' => [
                'code' => $redirect->code,
                'destination_url' => $redirect->destination_url,
                'query_params' => $redirect->query_params,
                'is_active' => $redirect->is_active,
                'last_accessed_at' => $redirect->last_accessed_at,
                'created_at' => $redirect->created_at,
                'updated_at' => $redirect->updated_at,
            ]
        ]);
    }

    /**
     * Atualiza as informações de um link curto existente
     * Permite modificar a URL de destino e outros parâmetros
     *
     * @param  \App\Http\Requests\UpdateRedirectRequest  $request
     * @param  string  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRedirectRequest $request, string $code): JsonResponse
    {
        $redirect = Redirect::findByCode($code);
        
        if (!$redirect) {
            return response()->json(['message' => 'Redirect não encontrado'], 404);
        }

        $data = $request->validated();
        
        // Processa parâmetros da URL da mesma forma que na criação
        if (isset($data['destination_url'])) {
            $urlParts = parse_url($data['destination_url']);
            $queryParams = null;
            
            if (isset($urlParts['query'])) {
                $queryParams = $urlParts['query'];
                $data['destination_url'] = $urlParts['scheme'] . '://' . $urlParts['host'];
                if (isset($urlParts['port'])) {
                    $data['destination_url'] .= ':' . $urlParts['port'];
                }
                if (isset($urlParts['path'])) {
                    $data['destination_url'] .= $urlParts['path'];
                }
            }
            
            $data['query_params'] = $queryParams;
        }
        
        $redirect->update($data);

        return response()->json([
            'message' => 'Redirect atualizado com sucesso',
            'data' => [
                'code' => $redirect->code,
                'destination_url' => $redirect->destination_url,
                'query_params' => $redirect->query_params,
                'is_active' => $redirect->is_active,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $code): JsonResponse
    {
        $redirect = Redirect::findByCode($code);
        
        if (!$redirect) {
            return response()->json(['message' => 'Redirect não encontrado'], 404);
        }

        $redirect->delete();

        return response()->json([
            'message' => 'Redirect removido com sucesso'
        ]);
    }
}
