<?php

$form = (new CForm('post', 'zabbix.php'))
    ->addVar('action', 'grafanaconfig.save')
    ->setId('grafana-config-form');

// Detectar se a página atual é HTTPS
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
$currentProtocol = $isHttps ? 'https' : 'http';

// Campo para URL do Grafana
$grafanaUrlField = (new CTextBox('grafana_url', $data['grafana_url'], false, 255))
    ->setAttribute('placeholder', 'https://grafana.exemplo.com')
    ->setId('grafana_url')
    ->setAttribute('style', 'width: 400px;')
    ->setAttribute('onchange', 'validateGrafanaUrl()');



$table = new CTable();
$table->addRow([
    (new CCol((new CLabel(_('URL Grafana'), 'grafana_url'))->setAsteriskMark()))->setAttribute('style', 'width: 150px;'),
    new CCol($grafanaUrlField)
]);
$table->addRow([
    new CCol(_('Exemple:')),
    new CCol((new CSpan($isHttps ? _('https://grafana.exemplo.com (HTTPS Required)') : _('https://grafana.exemplo.com ou http://192.168.1.100:3000')))->setAttribute('style', 'color: #666;'))
]);

$form->addItem($table);



// Script para validação em tempo real
$validationScript = (new CTag('script', true))
    ->addItem("
        function validateGrafanaUrl() {
            var url = document.getElementById('grafana_url').value;
            var warning = document.getElementById('https-warning');
            var isCurrentHttps = " . ($isHttps ? 'true' : 'false') . ";
            
            if (warning && isCurrentHttps && url && url.toLowerCase().startsWith('http://')) {
                warning.style.display = 'block';
                warning.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else if (warning) {
                warning.style.display = 'none';
            }
        }
        
        // Validar na inicialização se já há URL configurada
        document.addEventListener('DOMContentLoaded', function() {
            validateGrafanaUrl();
        });
    ");

$form->addItem($validationScript);

// Botões de ação simples
$buttonsDiv = (new CDiv([
    (new CSubmit('save', _('Salvar')))->setAttribute('style', 'margin-right: 10px;'),
    (new CButton('cancel', _('Cancelar')))->setAttribute('onclick', 'window.location.href="zabbix.php?action=grafanaviewer.view"')
]))->setAttribute('style', 'margin-top: 20px;');

$form->addItem($buttonsDiv);

// Criando a página
(new CHtmlPage())
    ->setTitle(_('Configuração do Grafana'))
    ->addItem($form)
    ->show(); 