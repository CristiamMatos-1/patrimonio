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
3. Faça o upload de todas as pastas do projeto (`app`, `config`, `Core`, `scripts`) para dentro de `patrimonio_app`.
4. Faça o upload **APENAS** do conteúdo da pasta `public` para dentro do seu diretório público do cPanel (geralmente `public_html` ou um subdomínio).

---

## Passo 2: Configuração de Caminhos e `.htaccess`

1. Abra o arquivo `index.php` que você colocou no `public_html`.
2. Edite as definições de caminho para apontarem para a pasta protegida que você criou no Passo 1:

```php
// Altere dirname(__DIR__) para o caminho absoluto da pasta protegida
define('BASE_PATH', '/home/seuusuario/patrimonio_app');

// Altere para o domínio real
define('APP_URL', 'https://seusistema.com.br'); 
```

3. Verifique se o arquivo `.htaccess` foi enviado corretamente para o `public_html`. Ele é responsável pelas URLs amigáveis:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

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

Abra o arquivo `patrimonio_app/config/database.php` e configure o acesso ao Master e a chave de segurança:

```php
return [
    'master' => [
        'host' => 'localhost',
        'dbname' => 'seuusuario_patrimonio_master',
        'user' => 'seuusuario_master',
        'password' => 'senha_forte_do_master',
        'charset' => 'utf8mb4'
    ],
    // ATENÇÃO: Esta chave criptografa as senhas dos bancos dos clientes!
    // Gere uma string aleatória de 32 caracteres e nunca a perca.
    'encryption_key' => 'SUA_CHAVE_SECRETA_MUITO_FORTE_AQUI'
];
```

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

---

## Pronto!
Acesse `https://seusistema.com.br` e faça o login inicial.
