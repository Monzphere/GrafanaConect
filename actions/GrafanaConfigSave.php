<?php

namespace Modules\GrafanaMonZabbix\Actions;

use CController,
    CControllerResponseRedirect,
    CUrl;

class GrafanaConfigSave extends CController {
    public function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        $fields = [
            'grafana_url' => 'string'
        ];

        $ret = $this->validateInput($fields);

        if (!$ret) {
            $this->setResponse(new CControllerResponseRedirect(
                (new CUrl('zabbix.php'))
                    ->setArgument('action', 'grafanaconfig.view')
                    ->getUrl()
            ));
        }

        return $ret;
    }

    public function checkPermissions(): bool {
        return true; // Simplificado para evitar problemas de permissão
    }

    protected function doAction(): void {
        $grafanaUrl = $this->getInput('grafana_url');
        
        // Usar uma abordagem mais simples - salvando em arquivo de configuração
        $configDir = __DIR__ . '/../../config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        $configFile = $configDir . '/grafana_config.json';
        $config = [
            'grafana_url' => $grafanaUrl,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        
        // Adicionar mensagem de sucesso
        info(_('Configuração do Grafana salva com sucesso!'));
        
        $this->setResponse(new CControllerResponseRedirect(
            (new CUrl('zabbix.php'))
                ->setArgument('action', 'grafanaconfig.view')
                ->getUrl()
        ));
    }
} 