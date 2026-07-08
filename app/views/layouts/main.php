<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Sistema de Patrimônio') ?></title>
    <!-- Tailwind CSS (CDN para dev, depois otimizamos) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Variáveis CSS baseadas nas suas preferências (Clean, Acessível, Alto Contraste) */
        :root {
            --bg-body: #F8F9FA;
            --surface: #FFFFFF;
            --text-main: #212529;
            --primary: #0F172A; /* Slate 900 */
        }
        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .surface {
            background-color: var(--surface);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">
    <header class="surface py-4 px-6 mb-6">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-tight">Sistema Patrimonial</h1>
            <a href="<?= APP_URL ?>/login" class="text-sm font-semibold text-blue-600 hover:underline">Login</a>
        </div>
    </header>

    <main class="flex-grow max-w-4xl mx-auto w-full px-4">
        <?= $content ?? '' ?>
    </main>

    <footer class="mt-10 py-6 text-center text-sm text-gray-500">
        &copy; <?= date('Y') ?> Controle Patrimonial SaaS. Todos os direitos reservados.
    </footer>
</body>
</html>