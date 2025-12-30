# Hyperf + Swoole Project

Este projeto é uma aplicação prática utilizando o **Hyperf Framework** com **Swoole**, desenvolvida para demonstrar conceitos avançados como:

- Criação de usuários em massa com geração de dados falsos.
- Monitoramento de progresso em tempo real via WebSocket.
- Exportação de usuários para CSV.
- Organização da lógica usando serviços e actions no WebSocket.

---

## Funcionalidades

1. **Factory Users**  
   - Cria usuários em batches para otimizar performance.
   - Envia progresso em tempo real para o frontend via WebSocket.
   - Suporta envio de informações complementares (`users_info`).

2. **Export CSV**  
   - Exporta usuários cadastrados para CSV.
   - Envia progresso da exportação via WebSocket.
   - Retorna link para download quando concluído.

3. **WebSocket Actions**  
   - Organiza diferentes ações em classes separadas.
   - Permite fácil expansão para novas funcionalidades.

---

## Pré-requisitos

- Docker (recomendado) ou ambiente Linux/Mac com:
  - PHP >= 8.1
  - Extensão Swoole >= 5.0
  - PDO, JSON, PCNTL, OpenSSL (opcional)
- MySQL ou outro banco compatível com PDO
- Redis (opcional, caso utilize caching ou filas)

---

## Rodando o projeto

A forma mais simples de rodar a aplicação é utilizando Docker Compose:

```bash
docker-compose up -d --build
