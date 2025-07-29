<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Criador de Links Curtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .step-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        .step-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .example-box {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .link-preview {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            display: none;
        }
        .copy-btn {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .copy-btn:hover {
            transform: scale(1.05);
        }
        .stats-badge {
            background: #ff9800;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-link"></i> Criador de Links Curtos
                    </h1>
                    <p class="lead">Transforme URLs longas em links curtos e fáceis de compartilhar!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4">Como Funciona?</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card step-card h-100 text-center">
                            <div class="card-body">
                                <div class="step-number mx-auto">1</div>
                                <h5>Cole sua URL</h5>
                                <p class="text-muted">Cole qualquer link longo que você quer encurtar</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card step-card h-100 text-center">
                            <div class="card-body">
                                <div class="step-number mx-auto">2</div>
                                <h5>Clique em Criar</h5>
                                <p class="text-muted">Nosso sistema gera um link curto único</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card step-card h-100 text-center">
                            <div class="card-body">
                                <div class="step-number mx-auto">3</div>
                                <h5>Compartilhe!</h5>
                                <p class="text-muted">Use o link curto para compartilhar facilmente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus-circle"></i> Criar Novo Link Curto</h5>
                    </div>
                    <div class="card-body">
                        <form id="createRedirectForm">
                            <div class="mb-3">
                                <label for="destination_url" class="form-label">
                                    <strong>Cole aqui a URL que você quer encurtar:</strong>
                                </label>
                                <input type="url" class="form-control form-control-lg" id="destination_url" name="destination_url" 
                                       placeholder="https://www.exemplo.com/pagina-muito-longa" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> 
                                    Cole qualquer URL válida (deve começar com http:// ou https://)
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-magic"></i> Criar Link Curto
                            </button>
                        </form>

                        <!-- Preview do Link Criado -->
                        <div id="linkPreview" class="link-preview">
                            <h6><i class="fas fa-check-circle text-success"></i> Link criado com sucesso!</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <strong>Seu link curto:</strong><br>
                                    <code id="shortLink" class="fs-5"></code>
                                </div>
                                <button class="btn btn-outline-primary copy-btn" onclick="copyToClipboard()">
                                    <i class="fas fa-copy"></i> Copiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Seus Links Curtos</h5>
                    </div>
                    <div class="card-body">
                        <div id="redirectsList">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                <p class="text-muted mt-2">Carregando seus links...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function loadRedirects() {
            try {
                const response = await fetch('/api/redirects');
                const data = await response.json();
                
                const redirectsList = document.getElementById('redirectsList');
                
                if (data.data && data.data.length > 0) {
                    let html = `
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Link Curto</th>
                                        <th>Vai Para</th>
                                        <th>Status</th>
                                        <th>Usos</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.data.forEach(redirect => {
                        const statusClass = redirect.status === 'ativo' ? 'success' : 'secondary';
                        const shortUrl = window.location.origin + '/r/' + redirect.code;
                        
                        html += `
                            <tr>
                                <td>
                                    <code class="text-primary">${shortUrl}</code>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('${shortUrl}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                                <td>
                                    <small class="text-muted">${redirect.destination_url}</small>
                                </td>
                                <td>
                                    <span class="badge bg-${statusClass}">${redirect.status}</span>
                                </td>
                                <td>
                                    <span class="stats-badge">
                                        <i class="fas fa-chart-line"></i> ${redirect.access_count || 0}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/r/${redirect.code}" target="_blank" class="btn btn-sm btn-outline-primary" title="Testar link">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <button onclick="showLogs('${redirect.code}')" class="btn btn-sm btn-outline-info" title="Ver logs">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <button onclick="deleteRedirect('${redirect.code}')" class="btn btn-sm btn-outline-danger" title="Deletar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    redirectsList.innerHTML = html;
                } else {
                    redirectsList.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-link fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum link criado ainda</h5>
                            <p class="text-muted">Crie seu primeiro link curto usando o formulário acima!</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erro ao carregar redirects:', error);
                document.getElementById('redirectsList').innerHTML = 
                    '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Erro ao carregar seus links.</div>';
            }
        }

        document.getElementById('createRedirectForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const destinationUrl = document.getElementById('destination_url').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando...';
            
            try {
                const response = await fetch('/api/redirects', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        destination_url: destinationUrl
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Mostrar preview do link criado
                    const shortUrl = window.location.origin + '/r/' + data.data.code;
                    document.getElementById('shortLink').textContent = shortUrl;
                    document.getElementById('linkPreview').style.display = 'block';
                    
                    // Limpar formulário
                    document.getElementById('destination_url').value = '';
                    
                    // Recarregar lista
                    loadRedirects();
                    
                    // Scroll para o preview
                    document.getElementById('linkPreview').scrollIntoView({ behavior: 'smooth' });
                } else {
                    alert('Erro ao criar link: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao criar link.');
            } finally {
                // Restaurar botão
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        function copyToClipboard(text = null) {
            const textToCopy = text || document.getElementById('shortLink').textContent;
            
            navigator.clipboard.writeText(textToCopy).then(function() {
                // Mostrar feedback visual
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                btn.classList.remove('btn-outline-secondary', 'btn-outline-primary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add(text ? 'btn-outline-secondary' : 'btn-outline-primary');
                }, 2000);
            });
        }

        async function deleteRedirect(code) {
            if (!confirm('Tem certeza que deseja deletar este link?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/redirects/${code}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (response.ok) {
                    alert('Link deletado com sucesso!');
                    loadRedirects();
                } else {
                    const data = await response.json();
                    alert('Erro ao deletar link: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao deletar link.');
            }
        }

        async function showLogs(code) {
            try {
                const response = await fetch(`/api/redirects/${code}/logs`);
                const data = await response.json();
                
                if (response.ok) {
                    displayLogsModal(code, data.data);
                } else {
                    alert('Erro ao buscar logs: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro ao buscar logs:', error);
                alert('Erro ao buscar logs.');
            }
        }

        function displayLogsModal(code, logs) {
            let html = `
                <div class="modal fade" id="logsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Logs do Link: ${code}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
            `;
            
            if (logs && logs.length > 0) {
                html += `
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Ação</th>
                                    <th>IP</th>
                                    <th>User Agent</th>
                                    <th>Referer</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                logs.forEach(log => {
                    const date = new Date(log.created_at).toLocaleString('pt-BR');
                    const userAgent = log.user_agent ? log.user_agent.substring(0, 50) + '...' : 'N/A';
                    const referer = log.referer || 'Direto';
                    const action = log.action === 'created' ? 
                        '<span class="badge bg-success">Criado</span>' : 
                        '<span class="badge bg-primary">Acesso</span>';
                    
                    html += `
                        <tr>
                            <td><small>${date}</small></td>
                            <td>${action}</td>
                            <td><code>${log.ip_address}</code></td>
                            <td><small title="${log.user_agent || 'N/A'}">${userAgent}</small></td>
                            <td><small>${referer}</small></td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += `
                    <div class="text-center py-4">
                        <p class="text-muted">Nenhum log encontrado para este link.</p>
                    </div>
                `;
            }
            
            html += `
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('logsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            document.body.insertAdjacentHTML('beforeend', html);
            
            const modal = new bootstrap.Modal(document.getElementById('logsModal'));
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', loadRedirects);
    </script>
</body>
</html> 