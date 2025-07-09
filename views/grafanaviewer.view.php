<?php

if ($data['configured'] && !empty($data['grafana_url'])) {
    // Detectar problemas de segurança
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $grafanaIsHttp = strpos($data['grafana_url'], 'http://') === 0;
    $hasMixedContent = $isHttps && $grafanaIsHttp;
    
    // Se o Grafana está configurado, exibir o iframe com fallback
    $content = [
        
        // Container do iframe com detecção de erro (só mostrar se não for mixed content)
        !$hasMixedContent ? (new CDiv())
            ->addClass('grafana-container')
            ->setAttribute('style', 'width: 100%; height: calc(100vh - 120px); margin: 0; padding: 0;')
            ->addItem([
                (new CTag('iframe', true))
                    ->setAttribute('src', $data['grafana_url'])
                    ->setAttribute('width', '100%')
                    ->setAttribute('height', '100%')
                    ->setAttribute('frameborder', '0')
                    ->setAttribute('scrolling', 'auto')
                    ->addClass('grafana-iframe')
                    ->setAttribute('style', 'border: none; margin: 0; padding: 0; display: block;')
                    ->setAttribute('onerror', 'showIframeError()'),
                
                // Mensagem de erro se iframe falhar
                (new CDiv())
                    ->setId('iframe-error')
                    ->setAttribute('style', 'display: none; text-align: center; padding: 40px; background-color: #f8f9fa; border: 1px solid #dee2e6;')
                    ->addItem([
                        (new CDiv(_('❌ Não foi possível carregar o Grafana no iframe')))
                            ->setAttribute('style', 'font-size: 18px; margin-bottom: 15px; color: #dc3545;'),
                        (new CDiv(_('Possíveis causas: X-Frame-Options, CORS, ou problemas de conectividade.')))
                            ->setAttribute('style', 'margin-bottom: 15px; color: #666;'),
                        (new CButton('open_new_window_error', _('Abrir Grafana em Nova Janela')))
                            ->setAttribute('onclick', "window.open('" . $data['grafana_url'] . "', '_blank')")
                            ->setAttribute('style', 'padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;')
                    ])
            ]) : 
            // Placeholder quando há mixed content
            (new CDiv())
                ->setAttribute('style', 'width: 100%; height: calc(100vh - 120px); text-align: center; padding: 60px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px dashed #dee2e6; border-radius: 8px; display: flex; flex-direction: column; justify-content: center; align-items: center; margin: 0;')
                ->addItem([
                    (new CDiv(_('🔒 Iframe Bloqueado por Segurança')))
                        ->setAttribute('style', 'font-size: 24px; margin-bottom: 15px; color: #6c757d;'),
                    (new CDiv(_('Mixed Content detectado. Use o botão acima para abrir em nova janela.')))
                        ->setAttribute('style', 'color: #6c757d;')
                ]),
        
        // Script para detecção melhorada de problemas
        (new CTag('script', true))
            ->addItem("
                function showIframeError() {
                    document.querySelector('.grafana-iframe').style.display = 'none';
                    document.getElementById('iframe-error').style.display = 'block';
                }
                
                // Detecção melhorada de problemas de carregamento
                setTimeout(function() {
                    var iframe = document.querySelector('.grafana-iframe');
                    if (iframe) {
                        try {
                            // Tentar acessar o conteúdo do iframe
                            var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            if (!iframeDoc || iframeDoc.readyState !== 'complete') {
                                console.log('Iframe pode não ter carregado completamente');
                            }
                        } catch(e) {
                            // Erro de CORS é normal e esperado
                            if (e.name === 'SecurityError') {
                                console.log('CORS presente - iframe provavelmente funcionando');
                            } else {
                                console.log('Possível problema com iframe:', e.message);
                            }
                        }
                    }
                }, 3000);
                
                // Detecção de mixed content via console
                var hasMixedContent = " . ($hasMixedContent ? 'true' : 'false') . ";
                if (hasMixedContent) {
                    console.warn('Mixed Content detectado: Zabbix em HTTPS mas Grafana em HTTP');
                }
            "),
        
        // Informações sobre a configuração
        (new CDiv())
            ->addClass('grafana-info')
            ->addItem([
                (new CSpan(_('URL Grafana: ')))->setAttribute('style', 'color: #666;'),
                (new CLink($data['grafana_url'], $data['grafana_url'], '_blank')),
                $hasMixedContent ? (new CSpan(_(' (⚠️ HTTP detectado!)')))
                    ->setAttribute('style', 'color: #dc3545; font-weight: bold;') : ''
            ]),
    ];
} else {
    $content = [
        (new CDiv(_('🚫 Configuração do Grafana não encontrada')))
            ->setAttribute('style', 'width: 100%; height: calc(100vh - 120px); text-align: center; padding: 60px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px dashed #dee2e6; border-radius: 8px; display: flex; flex-direction: column; justify-content: center; align-items: center; margin: 0;')
            ->addItem([
                (new CDiv(_('🔒 Configuração do Grafana não encontrada')))
                    ->setAttribute('style', 'font-size: 24px; margin-bottom: 15px; color: #6c757d;'),
                (new CDiv(_('Verifique se o Grafana está configurado corretamente.')))
                    ->setAttribute('style', 'color: #6c757d;')
            ])
    ];
}

(new CHtmlPage())
    ->setTitle(_('Grafana Dashboard'))
    ->addItem($content)
    ->show(); 