<?php

require_once 'vendor/autoload.php';

use App\Models\Redirect;

// Simular um redirect
$redirect = new Redirect();
$redirect->destination_url = 'https://google.com';
$redirect->query_params = 'utm_source=facebook&utm_campaign=ads';

echo "Testando getFinalUrl...\n";
echo "URL base: " . $redirect->destination_url . "\n";
echo "Query params do redirect: " . $redirect->query_params . "\n\n";

// Teste 1: Sem query params da request
echo "Teste 1 - Sem query params da request:\n";
$result = $redirect->getFinalUrl([]);
echo "Resultado: " . $result . "\n\n";

// Teste 2: Com query params da request (prioridade)
echo "Teste 2 - Com query params da request (prioridade):\n";
$requestParams = ['utm_source' => 'instagram'];
$result = $redirect->getFinalUrl($requestParams);
echo "Request params: " . http_build_query($requestParams) . "\n";
echo "Resultado: " . $result . "\n\n";

// Teste 3: Com query params vazios (devem ser ignorados)
echo "Teste 3 - Com query params vazios (devem ser ignorados):\n";
$requestParams = ['utm_source' => '', 'utm_campaign' => 'test'];
$result = $redirect->getFinalUrl($requestParams);
echo "Request params: " . http_build_query($requestParams) . "\n";
echo "Resultado: " . $result . "\n\n";

// Teste 4: Com query params nulos (devem ser ignorados)
echo "Teste 4 - Com query params nulos (devem ser ignorados):\n";
$requestParams = ['utm_source' => null, 'utm_campaign' => 'test'];
$result = $redirect->getFinalUrl($requestParams);
echo "Request params: " . http_build_query($requestParams) . "\n";
echo "Resultado: " . $result . "\n\n"; 