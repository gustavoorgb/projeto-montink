<?php

namespace App\Config\Migration;

use App\Config\Database\Connection;
use App\Helpers\Strings;
use PDO;

class Migrate {

    const BASE_MIGRATION = '000000_000000_base';

    protected string $migrationTable = 'migrations';
    protected ?PDO $Db = null;
    public string $migrationPath;
    private string $templatePath = __DIR__ . '/templates/default.php';

    public function __construct() {
        $this->Db = Connection::getConnection();
        $this->migrationPath = dirname(__DIR__, 3) . '/migrations';
        $this->init();
    }

    public function create(string $migrateName): void {
        $timesTamp = date('Ymd_His');
        $fileName = "m_{$timesTamp}_{$migrateName}.php";
        $filePath = $this->migrationPath . '/' . $fileName;
        if (file_exists($filePath)) {
            echo "Migration '{$fileName}' exists.\n";
            return;
        }

        $className = 'm_' . date('Ymd_His') . '_' . $migrateName;
        if (preg_match('/^create_(.+)_table$/', $migrateName, $matches)) {
            $table = strtolower($matches[1]);
        }
        ob_start();
        include $this->templatePath;
        $templateContent = ob_get_clean();

        if (!file_put_contents($filePath, $templateContent)) {
            echo "Unable to create the migration file.\n";
            return;
        }

        echo "Migration created successfully: {$filePath}\n";
    }

    public function up() {
        $migrations = $this->getAppliedMigrations();
        $files = scandir($this->migrationPath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;

            if (!in_array($file, $migrations)) {
                require $this->migrationPath . '/' . $file;

                $className = pathinfo($file, PATHINFO_FILENAME);
                if (!class_exists($className)) {
                    echo "Migration class '{$className}' not found in {$file}\n";
                    continue;
                }

                $Instance = new $className();
                echo "Applying migration: {$file}\n";
                $Instance->up();
                $this->toUp($file);
            }
        }
    }

    public function down() {
        $lastMigration = $this->getLastMigration();
        if (!$lastMigration) {
            echo "No migration to rollback.\n";
            return;
        }

        require_once $this->migrationPath . '/' . $lastMigration;

        $className = pathinfo($lastMigration, PATHINFO_FILENAME);
        if (!class_exists($className)) {
            echo "Class {$className} not found in file {$lastMigration}\n";
            return;
        }

        $Instance = new $className();
        echo "Rolling back: {$lastMigration}\n";
        $Instance->down();
        $this->removeMigration($lastMigration);
    }

    private function init() {
        $this->Db->exec("CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
          `name` varchar(180) NOT NULL,
          `apply_time` int(11) DEFAULT NULL,
          PRIMARY KEY (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->toUp(self::BASE_MIGRATION);
    }

    private function toUp($name) {
        try {
            $stm = $this->Db->prepare("INSERT INTO {$this->migrationTable} set `name` = ?, `apply_time` = ?");
            $stm->execute([$name, time()]);
        } catch (\PDOException $e) {
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getAppliedMigrations(): array {
        $stm = $this->Db->query("SELECT `name` FROM {$this->migrationTable}");
        return $stm->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    private function getLastMigration(): ?string {
        $stm = $this->Db->query("SELECT `name` FROM {$this->migrationTable} ORDER BY `apply_time` DESC LIMIT 1");
        return $stm->fetchColumn() ?: null;
    }

    private function removeMigration(string $name): void {
        $stm = $this->Db->prepare("DELETE FROM {$this->migrationTable} WHERE `name` = ?");
        $stm->execute([$name]);
    }
}
