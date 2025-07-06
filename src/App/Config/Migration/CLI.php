<?php

namespace App\Config\Migration;

use App\Config\Migration\Migrate;

class CLI {

    private Migrate $migrate;

    public function __construct() {
        $this->migrate = new Migrate();
    }

    public function run(array $argv): void {
        $command = $argv[1] ?? null;
        $argument = $argv[2] ?? null;

        if (!$command) {
            echo "Use: php migration.php create name_of_migration\n";
            return;
        }

        match ($command) {
            'create' => $this->handleCreate($argument),
            'up'     => $this->migrate->up(),
            'down'   => $this->migrate->down(),
            default  => print("Command '{$command}' doesn't recognized.\n"),
        };
    }

    private function handleCreate(?string $argument): void {
        if (!$argument) {
            echo "You should name your migration!\n";
            return;
        }

        $this->migrate->create($argument);
    }
}
