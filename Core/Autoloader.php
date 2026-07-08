<?php

namespace Core;

/**
 * Autoloader PSR-4 Nativo (Implementação de Referência Oficial PHP-FIG)
 * https://www.php-fig.org/psr/psr-4/examples/
 */
class Autoloader
{
    /**
     * @var array Um array associativo onde a chave é um prefixo de namespace 
     * e o valor é um array de diretórios base para as classes daquele namespace.
     */
    protected array $prefixes = [];

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        // Normaliza o prefixo do namespace
        $prefix = trim($prefix, '\\') . '\\';

        // Normaliza o diretório base com um separador no final
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        // Inicializa o array para o prefixo do namespace, se necessário
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = [];
        }

        // Retém o diretório base para aquele prefixo de namespace
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $baseDir);
        } else {
            array_push($this->prefixes[$prefix], $baseDir);
        }
    }

    public function loadClass(string $class): string|bool
    {
        // O prefixo do namespace atual
        $prefix = $class;

        // Trabalha de trás para frente no nome da classe qualificado
        // para achar um prefixo de namespace mapeado
        while (false !== $pos = strrpos($prefix, '\\')) {

            // Retém o prefixo de namespace (com a barra invertida)
            $prefix = substr($class, 0, $pos + 1);

            // Retém a classe relativa
            $relativeClass = substr($class, $pos + 1);

            // Tenta carregar o arquivo mapeado para este prefixo e classe relativa
            $mappedFile = $this->loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }

            // Remove a última barra invertida para a próxima iteração
            $prefix = rtrim($prefix, '\\');
        }

        // --- INÍCIO DO FALLBACK DE FORÇA BRUTA ---
        // Se a classe não foi encontrada pelos prefixos registrados (ex: problemas de case no array),
        // vamos tentar forçar a busca na pasta app/ se a classe começar com App\ ou Core\
        if (strpos($class, 'App\\') === 0 || strpos($class, 'app\\') === 0) {
            $relativeClass = substr($class, 4); // Remove 'App\'
            $baseDir = dirname(__DIR__) . '/app/';
            $result = $this->bruteForceSearch($baseDir, $relativeClass);
            if ($result) return $result;
        }

        if (strpos($class, 'Core\\') === 0 || strpos($class, 'core\\') === 0) {
            $relativeClass = substr($class, 5); // Remove 'Core\'
            $baseDir = dirname(__DIR__) . '/Core/';
            $result = $this->bruteForceSearch($baseDir, $relativeClass);
            if ($result) return $result;
        }
        // --- FIM DO FALLBACK DE FORÇA BRUTA ---

        // Nenhum arquivo mapeado foi encontrado
        return false;
    }

    /**
     * Tenta buscar o arquivo fisicamente no disco com variações de case
     */
    private function bruteForceSearch(string $baseDir, string $relativeClass): string|bool
    {
        // 1. Caminho exato
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if ($this->requireFile($file)) return $file;

        $parts = explode('\\', $relativeClass);
        if (count($parts) > 0) {
            $className = array_pop($parts); 
            
            // 2. Tudo minúsculo
            $lowerDirs = array_map('strtolower', $parts);
            $pathLower = !empty($lowerDirs) ? implode('/', $lowerDirs) . '/' : '';
            $fileLower = $baseDir . $pathLower . $className . '.php';
            if ($this->requireFile($fileLower)) return $fileLower;

            // 3. Primeira maiúscula
            $ucfirstDirs = array_map('ucfirst', $lowerDirs);
            $pathUcfirst = !empty($ucfirstDirs) ? implode('/', $ucfirstDirs) . '/' : '';
            $fileUcfirst = $baseDir . $pathUcfirst . $className . '.php';
            if ($this->requireFile($fileUcfirst)) return $fileUcfirst;
        }

        return false;
    }

    protected function loadMappedFile(string $prefix, string $relativeClass): string|bool
    {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        foreach ($this->prefixes[$prefix] as $baseDir) {

            // Substitui os separadores de namespace por separadores de diretório
            // na classe relativa, anexa ao diretório base com a extensão .php
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if ($this->requireFile($file)) {
                return $file;
            }

            // --- INÍCIO DO FALLBACK DE LINUX/CPANEL (CASE SENSITIVITY) ---
            $parts = explode('\\', $relativeClass);
            if (count($parts) > 0) {
                $className = array_pop($parts); 
                
                // Tenta tudo minúsculo
                $lowerDirs = array_map('strtolower', $parts);
                $pathLower = !empty($lowerDirs) ? implode('/', $lowerDirs) . '/' : '';
                $fileLower = $baseDir . $pathLower . $className . '.php';
                if ($this->requireFile($fileLower)) {
                    return $fileLower;
                }

                // Tenta ucfirst em cada diretório
                $ucfirstDirs = array_map('ucfirst', $lowerDirs);
                $pathUcfirst = !empty($ucfirstDirs) ? implode('/', $ucfirstDirs) . '/' : '';
                $fileUcfirst = $baseDir . $pathUcfirst . $className . '.php';
                if ($this->requireFile($fileUcfirst)) {
                    return $fileUcfirst;
                }
            }
            // --- FIM DO FALLBACK ---
        }

        return false;
    }

    protected function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}
