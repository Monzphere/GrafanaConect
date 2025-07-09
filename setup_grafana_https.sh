#!/bin/bash

# Script para configurar HTTPS no Grafana
# Executa no mesmo servidor do Zabbix

echo "=============================================="
echo "  Configurando HTTPS para Grafana na porta 3000"
echo "=============================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Verificar se é root ou sudo
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}Este script precisa ser executado como root (sudo)${NC}"
   exit 1
fi

# Detectar IP do servidor
SERVER_IP=$(hostname -I | awk '{print $1}')
echo -e "${BLUE}IP do servidor detectado: ${SERVER_IP}${NC}"

# Criar diretório para certificados do Grafana
GRAFANA_CERT_DIR="/etc/grafana/ssl"
mkdir -p $GRAFANA_CERT_DIR
chown grafana:grafana $GRAFANA_CERT_DIR
chmod 755 $GRAFANA_CERT_DIR

echo -e "${YELLOW}Criando certificado SSL auto-assinado...${NC}"

# Gerar certificado auto-assinado válido por 1 ano
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout $GRAFANA_CERT_DIR/grafana.key \
    -out $GRAFANA_CERT_DIR/grafana.crt \
    -subj "/C=BR/ST=State/L=City/O=Organization/OU=IT/CN=${SERVER_IP}" \
    -addext "subjectAltName=IP:${SERVER_IP},IP:127.0.0.1,DNS:localhost"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Certificado criado com sucesso!${NC}"
else
    echo -e "${RED}✗ Erro ao criar certificado${NC}"
    exit 1
fi

# Definir permissões corretas
chown grafana:grafana $GRAFANA_CERT_DIR/grafana.crt
chown grafana:grafana $GRAFANA_CERT_DIR/grafana.key
chmod 644 $GRAFANA_CERT_DIR/grafana.crt
chmod 600 $GRAFANA_CERT_DIR/grafana.key

echo -e "${YELLOW}Configurando o Grafana para usar HTTPS...${NC}"

# Backup da configuração original
cp /etc/grafana/grafana.ini /etc/grafana/grafana.ini.backup.$(date +%Y%m%d_%H%M%S)

# Configurar o grafana.ini
cat >> /etc/grafana/grafana.ini << EOF

# =================================================
# Configuração HTTPS adicionada automaticamente
# =================================================

[server]
# Protocolo (http, https, h2, socket)
protocol = https

# HTTPS configurações
cert_file = /etc/grafana/ssl/grafana.crt
cert_key = /etc/grafana/ssl/grafana.key

# Permitir embedding em iframes
[security]
allow_embedding = true


EOF

echo -e "${YELLOW}Reiniciando o serviço Grafana...${NC}"

# Reiniciar Grafana
systemctl restart grafana-server

# Verificar se o serviço está rodando
sleep 5
if systemctl is-active --quiet grafana-server; then
    echo -e "${GREEN}✓ Grafana reiniciado com sucesso!${NC}"
else
    echo -e "${RED}✗ Erro ao reiniciar Grafana${NC}"
    echo -e "${YELLOW}Verificando logs...${NC}"
    systemctl status grafana-server --no-pager -l
    exit 1
fi

echo -e "${YELLOW}Testando conexão HTTPS...${NC}"

# Testar se HTTPS está funcionando
if curl -k -s https://localhost:3000/api/health > /dev/null; then
    echo -e "${GREEN}✓ HTTPS funcionando corretamente!${NC}"
else
    echo -e "${RED}✗ Problema com HTTPS${NC}"
    echo -e "${YELLOW}Verificando se o Grafana está respondendo...${NC}"
    curl -k -v https://localhost:3000/api/health
fi

echo ""
echo "=============================================="
echo -e "${GREEN}✓ CONFIGURAÇÃO CONCLUÍDA!${NC}"
echo "=============================================="
echo ""
echo -e "${BLUE}Informações importantes:${NC}"
echo -e "• URL do Grafana: ${GREEN}https://${SERVER_IP}:3000${NC}"
echo -e "• Certificado localizado em: ${YELLOW}/etc/grafana/ssl/${NC}"
echo -e "• Backup da configuração: ${YELLOW}/etc/grafana/grafana.ini.backup.*${NC}"
echo ""
echo -e "${BLUE}Próximos passos:${NC}"
echo "1. Acesse o Zabbix e vá em 'Configurar Grafana'"
echo -e "2. Configure a URL como: ${GREEN}https://${SERVER_IP}:3000${NC}"
echo "3. Teste a integração no menu 'Grafana Dashboard'"
echo ""
echo -e "${YELLOW}Nota:${NC} Como é um certificado auto-assinado, o navegador mostrará"
echo "um aviso de segurança na primeira vez. Clique em 'Avançado' e"
echo "'Prosseguir para ${SERVER_IP}' para aceitar o certificado."
echo ""
echo -e "${BLUE}Para logs do Grafana:${NC} ${YELLOW}sudo journalctl -u grafana-server -f${NC}"
echo "" 