<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Db;
use PDO;

abstract class BaseController
{
    protected array $config;
    protected PDO $pdo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pdo = Db::pdo($config);
    }
}

