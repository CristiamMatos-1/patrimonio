# Manual de Instalação e Deploy (cPanel)

Este documento detalha o processo de implantação do **SaaS de Controle Patrimonial** em ambientes de hospedagem compartilhada como HostGator, Locaweb ou qualquer servidor com cPanel.

## Requisitos do Servidor
* PHP 8.1 ou superior.
* Extensões PHP ativas: `pdo_mysql`, `openssl`, `json`, `mbstring`.
* MySQL 5.7+ ou MariaDB 10.3+.

---

## Passo 1: Preparação dos Arquivos

A arquitetura foi projetada para segurança máxima, separando os arquivos de sistema da pasta pública.

1. No Gerenciador de Arquivos do cPanel, vá até a raiz da sua hospedagem (geralmente `/home/seuusuario/`).
2. Crie uma pasta chamada `patrimonio_app` (FORA do `public_html`).
3. Faça o upload de todas as pastas do projeto (`app`, `config`, `Core`, `database`, `scripts`) para dentro de `patrimonio_app`.
4. Faça o upload **APENAS** do conteúdo da pasta `public` para dentro do seu diretório público do cPanel (por exemplo `public_html/patrimonio`).

---

## Passo 2: Configuração de Caminhos e `.htaccess`

1. Use o `public/index.php` como arquivo inicial da pasta pública.
2. Defina a variável de ambiente `APP_SOURCE_PATH` apontando para a pasta protegida criada no Passo 1, por exemplo `/home/seuusuario/patrimonio_app`.
3. Se a aplicação estiver em `https://coninfoms.com.br/patrimonio`, defina também `BASE_URL` com esse endereço e `APP_BASE_PATH` com `/patrimonio`.

4. Verifique se o arquivo `.htaccess` da pasta `public` foi enviado corretamente para `public_html/patrimonio`. Ele é responsável pelas URLs amigáveis:
```apache
DirectoryIndex index.php
RewriteEngine On

# Se a aplicação estiver em subpasta, ajuste a base:
# RewriteBase /patrimonio/

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php?url=$0 [QSA,L]
```

5. Se aparecer `Server unable to read htaccess file, denying access to be safe`, o problema é de publicação/permissão no Apache, antes do PHP executar. No cPanel, confirme:
   - `.htaccess` com permissão `644`;
   - pastas públicas (`public_html` e `public_html/patrimonio`) com permissão `755`;
   - `index.php` com permissão `644`;
   - que não existe outro `.htaccess` corrompido ou vazio em uma pasta pai;
   - que o `mod_rewrite` está habilitado e `AllowOverride` permite ler `.htaccess`.

6. Se você publicar o repositório inteiro diretamente em `public_html/patrimonio`, use o `index.php` e o `.htaccess` da raiz do projeto. Se publicar apenas a pasta `public`, use os arquivos dentro de `public/`.

---

## Passo 3: Configuração do Banco de Dados Master

O sistema utiliza a abordagem Multi-Tenant *Database per Tenant*. O banco master armazenará as credenciais dos bancos das empresas clientes.

1. No cPanel, vá em **Bancos de Dados MySQL**.
2. Crie um banco chamado `seuusuario_patrimonio_master`.
3. Crie um usuário `seuusuario_master` com uma senha forte.
4. Adicione o usuário ao banco com **Todos os Privilégios**.
5. Abra o **phpMyAdmin**, selecione o banco master e importe o arquivo `scripts/01_master_db.sql`.

---

## Passo 4: Configuração de Segurança (Chave de Criptografia)

Defina uma chave forte para `ENCRYPTION_KEY` no ambiente do cPanel.

Gere uma chave forte:

```bash
openssl rand -hex 32
```

Configure no cPanel (Apache Environment Variables, `.htaccess` privado do servidor ou equivalente):

```apache
SetEnv ENCRYPTION_KEY "cole_aqui_o_valor_gerado"
SetEnv BASE_URL "https://coninfoms.com.br/patrimonio"
SetEnv APP_BASE_PATH "/patrimonio"
SetEnv APP_SOURCE_PATH "/home/seuusuario/patrimonio_app"
SetEnv APP_PUBLIC_PATH "/home/seuusuario/public_html/patrimonio"
```

Opcionalmente, para não expor segredo em variável direta, você pode usar arquivo seguro fora do `public_html`:

```apache
SetEnv ENCRYPTION_KEY_FILE "/home/seuusuario/.secrets/patrimonio_encryption_key"
```

> A aplicação bloqueia a inicialização quando `ENCRYPTION_KEY` está ausente ou fraca.
> Em deploy com pasta pública separada, `APP_PUBLIC_PATH` garante que uploads e arquivos públicos sejam gravados no diretório web correto.

---

## Passo 5: Provisionamento Automático no cPanel (Importante)

Para que o PHP consiga rodar o comando `CREATE DATABASE` dinamicamente quando um novo cliente for cadastrado pelo Superadmin, o usuário do banco Master (`seuusuario_master`) **precisa ter privilégios de criação de banco de dados globais**.

Em hospedagens compartilhadas (cPanel) estritas, o usuário não tem essa permissão via script. Se for o seu caso, o fluxo será:
1. Você (Superadmin) cria manualmente um banco e um usuário vazio no painel do cPanel (Ex: `seuusuario_tenant1`).
2. No painel do Superadmin do SaaS, ao invés de o sistema rodar o `CREATE DATABASE`, ele apenas salvará as credenciais e rodará o script de tabelas (`02_tenant_db.sql`) dentro deste banco já criado.

*(Nota: O código atual tenta o `CREATE DATABASE`. Caso sua hospedagem bloqueie, remova a linha `$masterDb->exec("CREATE DATABASE...")` do arquivo `TenantProvisioningService.php` e siga o fluxo manual acima).*

---

## Passo 6: Arquivos e tabelas para atualizar / substituir

Se você já tem uma instalação anterior deste sistema, substitua os seguintes arquivos para ativar os recursos de QR Code, impressão de PDF, empréstimos, conferência patrimonial e auditoria:

- `index.php`: adiciona rotas de inventário e auditoria no front controller público.
- `scripts/02_tenant_db.sql`: adiciona as tabelas `asset_inventory_sessions` e `asset_inventory_items` para conferência de patrimônio.
- `app/controllers/Tenant/AssetController.php`: adiciona upload de foto do patrimônio e empréstimo com data de saída, localização e responsável.
- `app/controllers/Tenant/InventoryController.php` e `app/models/AssetInventoryModel.php`: adiciona o fluxo de conferência patrimonial e validação de finalização pelo administrador.
- `app/views/tenant/assets/scan.php`: permite ler QR Codes pela câmera ou enviar imagem.
- `app/views/tenant/assets/index.php` e `app/views/tenant/assets/view.php`: exibe QR Code do patrimônio e fornece acesso rápido ao ciclo de empréstimos.

Após substituir esses arquivos, execute novamente a importação de `scripts/02_tenant_db.sql` no banco do tenant para criar as tabelas de inventário se ainda não existirem.

Se o banco master já existe em produção, execute também:

- `database/upgrade_2026_07_09_tenants_blocked_status.sql` (habilita status `blocked` para bloqueio total pelo superadmin).

> **Importante:** baixe o arquivo `.sql` bruto do repositório. Se o conteúdo começar com `<!DOCTYPE html>`, você salvou a página HTML do GitHub em vez do script SQL.

Conteúdo esperado do upgrade:

```sql
ALTER TABLE tenants
    MODIFY COLUMN status ENUM('active', 'suspended', 'blocked') DEFAULT 'active';
```

---

## Pronto!
Acesse `https://seusistema.com.br` e faça o login inicial.
