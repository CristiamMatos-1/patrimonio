<?php

namespace App\Models;

use Core\Model;
use PDO;

class RoleModel extends Model
{
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
