<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;

class UpdateRedirectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'destination_url' => [
                'sometimes',
                'url',
                'regex:/^https:\/\//',
                function ($attribute, $value, $fail) {
                    // Check if URL points to own application
                    $host = parse_url($value, PHP_URL_HOST);
                    $ownHost = parse_url(config('app.url'), PHP_URL_HOST);
                    
                    if ($host === $ownHost) {
                        $fail('A URL de destino não pode apontar para a própria aplicação.');
                    }
                },
                function ($attribute, $value, $fail) {
                    // Check if URL returns valid status
                    try {
                        $response = Http::timeout(10)->head($value);
                        if ($response->status() !== 200 && $response->status() !== 201) {
                            $fail('A URL de destino deve retornar status 200 ou 201.');
                        }
                    } catch (\Exception $e) {
                        $fail('Não foi possível acessar a URL de destino. Verifique se a URL está correta e acessível.');
                    }
                }
            ],
            'is_active' => 'sometimes|boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'destination_url.url' => 'A URL de destino deve ser uma URL válida.',
            'destination_url.regex' => 'A URL de destino deve usar HTTPS.',
            'is_active.boolean' => 'O status deve ser verdadeiro ou falso.',
        ];
    }
}
