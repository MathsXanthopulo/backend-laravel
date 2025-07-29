<?php

// Teste simples da API de redirects
echo "Testando API de Redirects...\n";

// URL base
$baseUrl = 'http://localhost:8001';

// Teste 1: Listar redirects
echo "\n1. Testando listagem de redirects...\n";
$response = file_get_contents($baseUrl . '/api/redirects');
if ($response !== false) {
    echo "✅ Listagem funcionando\n";
    $data = json_decode($response, true);
    echo "Total de redirects: " . count($data['data'] ?? []) . "\n";
} else {
    echo "❌ Erro na listagem\n";
}

// Teste 2: Criar redirect
echo "\n2. Testando criação de redirect...\n";
$postData = json_encode([
    'destination_url' => 'https://httpbin.org/status/200'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'content' => $postData
    ]
]);

$response = file_get_contents($baseUrl . '/api/redirects', false, $context);
if ($response !== false) {
    echo "✅ Criação funcionando\n";
    $data = json_decode($response, true);
    if (isset($data['data']['code'])) {
        echo "Redirect criado com código: " . $data['data']['code'] . "\n";
        
        // Teste 3: Verificar redirect criado
        echo "\n3. Testando visualização do redirect...\n";
        $response = file_get_contents($baseUrl . '/api/redirects/' . $data['data']['code']);
        if ($response !== false) {
            echo "✅ Visualização funcionando\n";
        } else {
            echo "❌ Erro na visualização\n";
        }
        
        // Teste 4: Testar redirecionamento
        echo "\n4. Testando redirecionamento...\n";
        $headers = get_headers($baseUrl . '/r/' . $data['data']['code']);
        if ($headers && strpos($headers[0], '302') !== false) {
            echo "✅ Redirecionamento funcionando\n";
        } else {
            echo "❌ Erro no redirecionamento\n";
        }
        
        // Teste 5: Verificar estatísticas
        echo "\n5. Testando estatísticas...\n";
        $response = file_get_contents($baseUrl . '/api/redirects/' . $data['data']['code'] . '/stats');
        if ($response !== false) {
            echo "✅ Estatísticas funcionando\n";
        } else {
            echo "❌ Erro nas estatísticas\n";
        }
    }
} else {
    echo "❌ Erro na criação\n";
}

echo "\n🎉 Teste concluído!\n"; 