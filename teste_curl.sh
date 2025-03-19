#!/bin/bash
# Script para testar as rotas do sistema

# Definir cores para saída
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Iniciando testes de rotas ===${NC}"

# Testar rota raiz - deve exibir prelanding ou black page
echo -e "\n${YELLOW}Testando rota raiz (/) - esperado: preland1 ou site4 ${NC}"
curl -s http://localhost:8003/ | grep -i "<title>" | head -n 1
sleep 1

# Testar admin index
echo -e "\n${YELLOW}Testando rota admin index (/admin/index.php?password=12345) ${NC}"
curl -s "http://localhost:8003/admin/index.php?password=12345" | grep -i "cloaker" | head -n 1
sleep 1

# Testar admin statistics
echo -e "\n${YELLOW}Testando rota admin statistics (/admin/statistics.php?password=12345) ${NC}"
curl -s "http://localhost:8003/admin/statistics.php?password=12345" | grep -i "statistics" | head -n 1
sleep 1

# Testar admin leads
echo -e "\n${YELLOW}Testando rota admin leads (/admin/index.php?filter=leads&password=12345) ${NC}"
curl -s "http://localhost:8003/admin/index.php?filter=leads&password=12345" | grep -i "lead" | head -n 1
sleep 1

# Simular clique em botão
echo -e "\n${YELLOW}Simulando clique em botão (POST /buttonlog.php) ${NC}"
RESPONSE=$(curl -s -X POST "http://localhost:8003/buttonlog.php" \
  -H "Content-Type: application/json" \
  -d '{"event":"lead_click","prelanding":"preland1","timestamp":"2023-03-19T12:00:00.000Z"}')
echo $RESPONSE
sleep 1

# Verificar dados no IP client
echo -e "\n${YELLOW}Verificando IP do cliente ${NC}"
curl -s "http://localhost:8003/tds_diagnose.php" | grep -i "ip"
sleep 1

echo -e "\n${GREEN}=== Testes concluídos ===${NC}" 