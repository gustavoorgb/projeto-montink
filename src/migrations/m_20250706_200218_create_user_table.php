<?php

use App\Config\Migration\Migrate;

class m_20250706_200218_create_user_table extends Migrate {

    public function up() {

        $this->Db->exec("CREATE TABLE IF NOT EXISTS `user` (
            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(60) NOT NULL,
            `email` VARCHAR(60) NOT NULL,
            `descripton` TEXT NULL,
            `status` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`))
            ENGINE = InnoDB
            DEFAULT CHARACTER SET = utf8");
    }

    public function down() {
        $this->Db->exec("DROP TABLE `user`");
    }
}
