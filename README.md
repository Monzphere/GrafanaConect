# Módulo Grafana MonZphere Zabbix

Este módulo permite a integração do Grafana dentro do Zabbix, proporcionando uma experiência unificada para monitoramento e visualização de dados com interface que ocupa todo o espaço disponível.

![image](https://github.com/user-attachments/assets/2e679b97-8006-4e72-a4aa-c4c8c135ca79)


## 🚀 Funcionalidades

### 1. Configuração do Grafana
- Interface para configurar a URL do servidor Grafana
- Validação da URL inserida com suporte HTTPS/HTTP
- Configuração persistente salva em arquivo JSON
- Detecção automática de problemas de Mixed Content

### 2. Visualização do Grafana
- Acesso ao Grafana diretamente do menu do Zabbix
- Interface em iframe que ocupa todo o espaço disponível do menu
- Suporte a login e todas as funcionalidades do Grafana
- Fallback automático para nova janela quando iframe falha
- Detecção inteligente de problemas de carregamento

## 📋 Requisitos

### Sistema Operacional
- Linux (testado em Ubuntu/CentOS/RHEL)
- Acesso root/sudo para configuração HTTPS

### Software Necessário
- **Zabbix** 6.0+ (frontend)
- **Grafana** 8.0+ (servidor)
- **OpenSSL** (para certificados HTTPS)
- **PHP** 7.4+ com extensões:
  - json
  - curl
  - fileinfo

### Rede
- Conectividade entre servidor Zabbix e Grafana
- Portas abertas:
  - **3000** (Grafana padrão)
  - **443** (HTTPS recomendado)

## 🛠️ Instalação

### 1. Instalar o Módulo
```bash
# Copie os arquivos para o diretório de módulos do Zabbix
cp -r treinamento-php /usr/share/zabbix/modules/
chown -R www-data:www-data /usr/share/zabbix/modules/GrafanaConect
```

### 2. Configurar HTTPS no Grafana (Recomendado)
Execute o script automatizado:
```bash
sudo ./setup_grafana_https.sh
```

**O que o script `setup_grafana_https.sh` faz:**
- 🔒 **Cria certificado SSL auto-assinado** válido por 1 ano
- 📁 **Configura diretório SSL** em `/etc/grafana/ssl/`
- ⚙️ **Modifica grafana.ini** para habilitar HTTPS na porta 3000
- 🔧 **Habilita embedding** (`allow_embedding = true`)
- 🔄 **Reinicia o serviço** Grafana automaticamente
- ✅ **Testa a conexão** HTTPS após configuração
- 💾 **Backup automático** da configuração original

### 3. Configurar no Zabbix
1. **Acesse o menu "Grafana"** no Zabbix
2. **Clique em "Configurar Grafana"**
3. **Insira a URL HTTPS**, por exemplo:
   - `https://192.168.1.100:3000`
   - `https://grafana.empresa.com`
4. **Clique em "Salvar"**

## 📁 Estrutura do Módulo

```
treinamento-php/
├── actions/                     # 🎯 Controladores PHP
│   ├── GrafanaConfig.php       # Exibir formulário de configuração
│   ├── GrafanaConfigSave.php   # Salvar configuração
│   └── GrafanaViewer.php       # Visualizar Grafana
├── views/                       # 🖼️ Templates de visualização  
│   ├── grafanaconfig.view.php  # Página de configuração
│   └── grafanaviewer.view.php  # Página de visualização (tela cheia)
├── assets/                      # 🎨 Recursos estáticos
│   └── css/
│       └── grafanamonzabbix.css # Estilos CSS
├── config/                      # ⚙️ (Criado automaticamente)
│   └── grafana_config.json     # Configuração salva
├── setup_grafana_https.sh      # 🔧 Script de configuração HTTPS
├── manifest.json               # 📦 Configuração do módulo
├── Module.php                  # 🏗️ Classe principal do módulo
└── README.md                   # 📚 Esta documentação
```

## ⚙️ Configuração Avançada

### Arquivo de Configuração
A configuração é salva em `config/grafana_config.json`:
```json
{
    "grafana_url": "https://192.168.1.100:3000",
    "updated_at": "2024-01-15 10:30:00"
}
```

### Configuração Manual do Grafana (Alternativa ao Script)
Se preferir configurar manualmente, edite `/etc/grafana/grafana.ini`:
```ini
[server]
protocol = https
cert_file = /etc/grafana/ssl/grafana.crt
cert_key = /etc/grafana/ssl/grafana.key

[security]
allow_embedding = true
```

## 🚨 Resolução de Problemas

### ❌ Grafana não carrega no iframe
- **Cause**: X-Frame-Options ou CORS
- **Solução**: Use o botão "Abrir em Nova Janela" que aparece automaticamente

### 🔒 Aviso de Certificado
- **Cause**: Certificado auto-assinado
- **Solução**: Aceite o certificado clicando em "Avançado" → "Prosseguir"

### 🌐 Mixed Content (HTTPS/HTTP)
- **Cause**: Zabbix em HTTPS e Grafana em HTTP
- **Solução**: Use o script `setup_grafana_https.sh` para configurar HTTPS

### 📱 Menu não aparece
- Verifique permissões do usuário no Zabbix
- Confirme se o módulo está no diretório correto
- Recarregue a página completamente

## 🔍 Logs e Debug

### Logs do Grafana
```bash
sudo journalctl -u grafana-server -f
```

### Verificar Certificado
```bash
openssl x509 -in /etc/grafana/ssl/grafana.crt -text -noout
```

### Testar HTTPS
```bash
curl -k https://localhost:3000/api/health
```

## 📊 Características Técnicas

- ✅ **Interface Responsiva**: Ocupa todo o espaço disponível
- ✅ **Detecção Automática**: Mixed Content e problemas de carregamento  
- ✅ **Fallback Inteligente**: Nova janela quando iframe falha
- ✅ **Certificados Auto-assinados**: Configuração automática
- ✅ **Backup Automático**: Configurações originais preservadas

## 🔮 Melhorias Futuras

- 🔐 Integração com SSO/LDAP
- 📊 Embedding de dashboards específicos
- ⚡ Cache de configurações
- 📈 Métricas de uso do módulo
- 🔔 Notificações de status

## 🆘 Suporte

- 📖 **Documentação Adicional**: Consulte os arquivos `.md` inclusos
- 🐛 **Problemas**: Verifique o guia de troubleshooting
- 💡 **Sugestões**: Entre em contato com a equipe de desenvolvimento 
