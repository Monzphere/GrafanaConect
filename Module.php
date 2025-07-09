<?php

namespace Modules\GrafanaMonZabbix;

use Zabbix\Core\CModule,
    APP,
    CMenuItem;

class Module extends CModule {
    public function init(): void {
        $menu = APP::Component()->get('menu.main');
        
        // Adicionar item para visualizar o Grafana
        $menu->add(
            (new CMenuItem(_('Grafana Dashboard')))
                ->setAction('grafanaviewer.view')
                ->setIcon('icon-dashboard')
        );
        
        // Adicionar item para configurar o Grafana
        $menu->add(
            (new CMenuItem(_('Configurar Grafana')))
                ->setAction('grafanaconfig.view')
                ->setIcon('icon-settings')
        );
    }
}