<div align="center">

# 🚀 Painel Central

### Plataforma interna de licenciamento, CRM, faturamento e suporte da CCITI

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-Horizon-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://laravel.com/docs/horizon)
[![Status](https://img.shields.io/badge/Status-Produção-success?style=for-the-badge)](#)

*Um único painel pra gerir clientes, licenças, cobrança recorrente, nota fiscal e integrações — sem depender de planilha.*

</div>

---

## 📖 Sobre

O **Painel Central** é o sistema interno da **CCITI** que centraliza toda a operação comercial e técnica da empresa: cadastro de clientes, licenciamento de software (incluindo pacotes com múltiplos produtos sob um único serial), políticas de faturamento recorrente, emissão de Nota Fiscal de Serviço eletrônica (NFS-e), propostas comerciais, campanhas de teste grátis e integração com instâncias GLPI de clientes.

Não é um produto de prateleira — foi construído sob medida pra operação da CCITI, e cresce conforme a necessidade do negócio.

---

## ✨ Funcionalidades

### 💼 Comercial
- **Clientes** — cadastro completo com CNPJ/CPF, busca automática de dados via CNPJ (Receita Federal).
- **Propostas Comerciais** — criação, versionamento, geração de PDF e aceite digital, com geração automática de faturas (sinal + parcela final) ao aceitar.
- **Campanhas** — páginas públicas de captação com período de teste grátis (trial), emissão automática de licença temporária pro lead.
- **Contatos do Site** — funil de leads vindos do site institucional.

### 🔑 Licenciamento
- Licenças vinculadas a cliente, com chave única (`license_key`) gerada automaticamente.
- **Pacotes de licenciamento**: uma única chave pode liberar **vários produtos/plugins simultaneamente**, cada um com sua própria cota de instalações.
- Controle de instalações ativas por produto, com revogação manual de vagas.
- **API REST de validação** (`/api/licenses/validate`), pensada pra ser consultada em tempo real por plugins/produtos licenciados, com proteção contra uso cruzado entre produtos diferentes.
- Suspensão automática por inadimplência e reativação automática ao quitar a fatura.

### 💰 Financeiro
- **Políticas de Faturamento** recorrentes (única, mensal, semestral, anual), geração automática de faturas no dia certo do mês.
- **Serviços** com planos de preço por frequência (ex: mensal e anual com valores diferentes, sem duplicar cadastro).
- **Faturas** com link de pagamento via **Mercado Pago** (Checkout Pro) e baixa automática por webhook.
- **Emissão de NFS-e** (Nota Fiscal de Serviço eletrônica) direto no Ambiente Nacional, com certificado digital A1, geração de DANFSe em PDF e competência calculada automaticamente a partir do ciclo de cobrança.
- Cobrança automática de inadimplentes com régua de lembretes e prazo de tolerância.

### 🔌 Integrações
- **Instâncias GLPI** — cadastro de múltiplos servidores GLPI de clientes, conectados via API REST v2 (OAuth2).
- **Importação de Ativos (GLPI)** — upload de CSV padronizado (computadores, monitores, telefones, chips, equipamentos de rede), processado em fila (Horizon) com prévia antes de gravar, idempotente em reimportações.
- Guia de configuração da instância GLPI embutido na própria tela.

### 🛡️ Acessos
- Autenticação local e via **Microsoft Entra ID** (SSO corporativo).
- Controle de permissões granular por módulo (**Spatie Permission**), papéis (roles) configuráveis.
- Painel de **saúde do sistema**, com checagem ativa de cada integração crítica (banco, fila, storage, certificado NFS-e, Mercado Pago).

---

## 🛠️ Stack Técnica

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Banco de dados | PostgreSQL 16 |
| Fila / Jobs | Laravel Horizon + Redis |
| Frontend | Blade + Bootstrap 5 + Vite |
| Tabelas | Yajra DataTables |
| Permissões | Spatie Laravel Permission |
| Mídia/Storage | Spatie Media Library + Azure Blob Storage |
| Pagamentos | Mercado Pago SDK |
| NFS-e | `hadder/nfse-nacional` (Ambiente Nacional / SEFIN) |
| SSO | Laravel Socialite + Microsoft provider |
| Repositórios | Prettus L5 Repository |

---

## 🏗️ Padrão de Arquitetura

Cada módulo administrativo segue, de ponta a ponta, a mesma convenção:

```
Model → Migration → Policy → FormRequests → Repository → Controller
   → DataTable → Views → Routes → RoleSeeder → AuthServiceProvider → Sidebar
```

Isso mantém qualquer módulo novo (por menor que seja) previsível de navegar e consistente com o resto do sistema.

---

## ⚙️ Rodando localmente

### Requisitos
- PHP 8.2+, Composer
- PostgreSQL
- Redis
- Node.js (pra build dos assets)

### Passos

```bash
# Dependências
composer install
npm install

# Ambiente
cp .env.example .env
php artisan key:generate

# Configure DB_*, REDIS_* no .env, depois:
php artisan migrate --seed

# Assets
npm run build

# Fila (necessária pra importações GLPI e e-mails)
php artisan horizon
```

As integrações de negócio (Mercado Pago, certificado NFS-e, Azure Blob Storage) **não ficam no `.env`** — são configuradas direto pela tela **Configurações** do próprio painel, já logado, com teste de conexão em cada uma.

---

## 🔐 Licenciamento de produtos — API de validação

Produtos licenciados pela CCITI (plugins, integrações) validam sua licença consultando:

```
POST /api/licenses/validate
{
  "license_key": "XXXXX-XXXXX-XXXXX",
  "service_slug": "identificador-fixo-do-produto",
  "instance_identifier": "uuid-fixo-desta-instalação"
}
```

Uma mesma `license_key` pode cobrir **vários produtos** (pacote) — a cota de instalações (`quantity`) vale por produto dentro do pacote, não é compartilhada entre eles.

---

<div align="center">

**CCITI** · Sistema de uso interno

</div>
