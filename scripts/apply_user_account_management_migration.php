<?php
require_once __DIR__ . '/../config/database.php';

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function migration_print($message = '') {
    echo $message . PHP_EOL;
}

function migration_fail($message) {
    if (defined('STDERR')) {
        fwrite(STDERR, $message . PHP_EOL);
    } else {
        echo $message . PHP_EOL;
    }
    exit(1);
}

function migration_table_exists(mysqli $conn, $dbName, $tableName) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = ?
          AND TABLE_NAME = ?
    ");
    $stmt->bind_param("ss", $dbName, $tableName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int) ($row['total'] ?? 0) > 0;
}

function migration_column_exists(mysqli $conn, $dbName, $tableName, $columnName) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ?
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->bind_param("sss", $dbName, $tableName, $columnName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int) ($row['total'] ?? 0) > 0;
}

function migration_index_exists(mysqli $conn, $dbName, $tableName, $indexName) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = ?
          AND TABLE_NAME = ?
          AND INDEX_NAME = ?
    ");
    $stmt->bind_param("sss", $dbName, $tableName, $indexName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int) ($row['total'] ?? 0) > 0;
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    $dbResult = $conn->query("SELECT DATABASE() AS db_name");
    $dbRow = $dbResult->fetch_assoc();
    $dbName = $dbRow['db_name'] ?? '';

    if ($dbName === '') {
        migration_fail('Khong xac dinh duoc database hien tai.');
    }

    migration_print('Database dang dung: ' . $dbName);

    if (!migration_table_exists($conn, $dbName, 'users')) {
        migration_fail("Bang users khong ton tai trong database {$dbName}. Dung migration.");
    }

    $columns = [
        [
            'name' => 'full_name',
            'definition' => 'VARCHAR(255) NULL',
            'after' => 'email',
        ],
        [
            'name' => 'is_active',
            'definition' => 'TINYINT(1) NOT NULL DEFAULT 1',
            'after' => 'is_verified',
        ],
        [
            'name' => 'reset_token_hash',
            'definition' => 'CHAR(64) NULL',
            'after' => 'otp_expires_at',
        ],
        [
            'name' => 'reset_token_expires_at',
            'definition' => 'DATETIME NULL',
            'after' => 'reset_token_hash',
        ],
        [
            'name' => 'reset_token_used_at',
            'definition' => 'DATETIME NULL',
            'after' => 'reset_token_expires_at',
        ],
    ];

    migration_print('');
    migration_print('Kiem tra cot users...');
    foreach ($columns as $column) {
        if (migration_column_exists($conn, $dbName, 'users', $column['name'])) {
            migration_print('[TON TAI] users.' . $column['name']);
            continue;
        }

        $afterSql = '';
        if (!empty($column['after']) && migration_column_exists($conn, $dbName, 'users', $column['after'])) {
            $afterSql = ' AFTER `' . $column['after'] . '`';
        }

        $sql = 'ALTER TABLE `users` ADD COLUMN `' . $column['name'] . '` ' . $column['definition'] . $afterSql;
        $conn->query($sql);
        migration_print('[DA THEM] users.' . $column['name']);
    }

    $indexes = [
        [
            'name' => 'idx_users_role_verified_active',
            'columns' => '`role`, `is_verified`, `is_active`',
        ],
        [
            'name' => 'idx_users_reset_token_hash',
            'columns' => '`reset_token_hash`',
        ],
    ];

    migration_print('');
    migration_print('Kiem tra index users...');
    foreach ($indexes as $index) {
        if (migration_index_exists($conn, $dbName, 'users', $index['name'])) {
            migration_print('[TON TAI] ' . $index['name']);
            continue;
        }

        $sql = 'CREATE INDEX `' . $index['name'] . '` ON `users` (' . $index['columns'] . ')';
        $conn->query($sql);
        migration_print('[DA THEM] ' . $index['name']);
    }

    migration_print('');
    migration_print('Xac nhan cot hien co trong users:');
    $result = $conn->query("SHOW COLUMNS FROM `users`");
    $existingColumns = [];
    while ($row = $result->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }
    migration_print(implode(', ', $existingColumns));

    migration_print('');
    migration_print('Kiem tra cot bat buoc:');
    foreach (array_column($columns, 'name') as $requiredColumn) {
        $status = in_array($requiredColumn, $existingColumns, true) ? 'OK' : 'THIEU';
        migration_print('- ' . $requiredColumn . ': ' . $status);
    }

    migration_print('');
    migration_print('Hoan tat migration user account management.');
} catch (Throwable $e) {
    migration_fail('Loi migration: ' . $e->getMessage());
}
