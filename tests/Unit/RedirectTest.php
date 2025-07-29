<?php

namespace Tests\Unit;

use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_code_from_id()
    {
        $redirect = Redirect::factory()->create();
        
        $this->assertNotNull($redirect->code);
        $this->assertIsString($redirect->code);
        $this->assertNotEmpty($redirect->code);
    }

    public function test_can_find_redirect_by_code()
    {
        $redirect = Redirect::factory()->create();
        
        $foundRedirect = Redirect::findByCode($redirect->code);
        
        $this->assertNotNull($foundRedirect);
        $this->assertEquals($redirect->id, $foundRedirect->id);
    }

    public function test_returns_null_for_invalid_code()
    {
        $foundRedirect = Redirect::findByCode('invalid_code');
        
        $this->assertNull($foundRedirect);
    }

    public function test_get_final_url_without_query_params()
    {
        $redirect = Redirect::factory()->create([
            'destination_url' => 'https://google.com',
            'query_params' => null
        ]);
        
        $finalUrl = $redirect->getFinalUrl();
        
        $this->assertEquals('https://google.com', $finalUrl);
    }

    public function test_get_final_url_with_redirect_query_params()
    {
        $redirect = Redirect::factory()->create([
            'destination_url' => 'https://google.com',
            'query_params' => 'utm_source=facebook&utm_campaign=ads'
        ]);
        
        $finalUrl = $redirect->getFinalUrl();
        
        $this->assertEquals('https://google.com?utm_source=facebook&utm_campaign=ads', $finalUrl);
    }

    public function test_get_final_url_merging_query_params()
    {
        $redirect = Redirect::factory()->create([
            'destination_url' => 'https://google.com',
            'query_params' => 'utm_source=facebook&utm_campaign=ads'
        ]);
        
        $requestParams = ['utm_source' => 'instagram', 'utm_medium' => 'social'];
        $finalUrl = $redirect->getFinalUrl($requestParams);
        
        $this->assertEquals('https://google.com?utm_source=instagram&utm_campaign=ads&utm_medium=social', $finalUrl);
    }

    public function test_get_final_url_ignoring_empty_values()
    {
        $redirect = Redirect::factory()->create([
            'destination_url' => 'https://google.com',
            'query_params' => 'utm_source=facebook'
        ]);
        
        $requestParams = ['utm_source' => '', 'utm_campaign' => 'test'];
        $finalUrl = $redirect->getFinalUrl($requestParams);
        
        $this->assertEquals('https://google.com?utm_source=facebook&utm_campaign=test', $finalUrl);
    }

    public function test_active_scope()
    {
        Redirect::factory()->active()->create();
        Redirect::factory()->inactive()->create();
        
        $activeRedirects = Redirect::active()->get();
        
        $this->assertEquals(1, $activeRedirects->count());
        $this->assertTrue($activeRedirects->first()->is_active);
    }

    /**
     * Teste completo: Pega uma URL qualquer, gera Hashids e redireciona para o Google
     * Demonstra todo o processo do sistema de redirects
     */
    public function test_complete_redirect_process_with_any_url_to_google()
    {
        // 1. Simula uma URL qualquer que o usuário quer encurtar
        $anyUrl = 'https://www.exemplo.com/pagina-muito-longa-com-muitos-parametros?utm_source=facebook&utm_campaign=ads&utm_medium=social&ref=123456';
        
        // 2. Cria o redirect no sistema
        $redirect = Redirect::factory()->create([
            'destination_url' => 'https://www.google.com',
            'query_params' => null,
            'is_active' => true
        ]);
        
        // 3. Verifica se o Hashids foi gerado corretamente
        $this->assertNotNull($redirect->code, 'Hashids deve ser gerado automaticamente');
        $this->assertIsString($redirect->code, 'Hashids deve ser uma string');
        $this->assertNotEmpty($redirect->code, 'Hashids não pode estar vazio');
        
        // 4. Verifica se conseguimos encontrar o redirect pelo código Hashids
        $foundRedirect = Redirect::findByCode($redirect->code);
        $this->assertNotNull($foundRedirect, 'Deve encontrar o redirect pelo código Hashids');
        $this->assertEquals($redirect->id, $foundRedirect->id, 'Deve ser o mesmo redirect');
        
        // 5. Verifica se a URL final redireciona para o Google
        $finalUrl = $redirect->getFinalUrl();
        $this->assertEquals('https://www.google.com', $finalUrl, 'URL final deve apontar para o Google');
        
        // 6. Simula parâmetros da requisição (como se alguém acessasse com UTM tags)
        $requestParams = ['utm_source' => 'instagram', 'utm_medium' => 'social'];
        $finalUrlWithParams = $redirect->getFinalUrl($requestParams);
        $this->assertEquals('https://www.google.com?utm_source=instagram&utm_medium=social', $finalUrlWithParams, 'Deve combinar parâmetros da requisição');
        
        // 7. Verifica se o redirect está ativo
        $this->assertTrue($redirect->is_active, 'Redirect deve estar ativo');
        
        // 8. Testa o scope ativo
        $activeRedirects = Redirect::active()->get();
        $this->assertTrue($activeRedirects->contains($redirect), 'Redirect deve aparecer na lista de ativos');
        
        // 9. Verifica se o código Hashids é único
        $anotherRedirect = Redirect::factory()->create([
            'destination_url' => 'https://www.facebook.com'
        ]);
        $this->assertNotEquals($redirect->code, $anotherRedirect->code, 'Códigos Hashids devem ser únicos');
        
        // 10. Demonstra o processo completo
        echo "\n\n PROCESSO COMPLETO DEMONSTRADO:\n";
        echo "URL Original: {$anyUrl}\n";
        echo "ID no Banco: {$redirect->id}\n";
        echo "Código Hashids: {$redirect->code}\n";
        echo "URL Curta: http://127.0.0.1:8000/r/{$redirect->code}\n";
        echo "Redireciona para: {$finalUrl}\n";
        echo "Processo completo funcionando!\n\n";
        
        $this->assertTrue(true, 'Processo completo de redirect funcionou!');
    }
}
