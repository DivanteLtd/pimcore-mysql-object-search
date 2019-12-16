<?php

declare(strict_types=1);

namespace DivanteLtd\AdvancedSearchBundle;

use DivanteLtd\AdvancedSearchBundle\Model\SavedSearch\Dao;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Config;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;

class Installer extends MigrationInstaller
{
    const QUEUE_TABLE_NAME = 'bundle_advancedsearch_update_queue';

    /** {@inheritdoc} */
    public function migrateInstall(Schema $schema, Version $version)
    {
        $this->installDatabase();

        return $this->isInstalled();
    }

    /** {@inheritdoc} */
    public function migrateUninstall(Schema $schema, Version $version): void
    {
    }

    private function installDatabase(): void
    {
        //create tables
        \Pimcore\Db::get()->query(
            'CREATE TABLE IF NOT EXISTS `' . self::QUEUE_TABLE_NAME . "` (
                  `o_id` bigint(10) NOT NULL DEFAULT '0',
                  `classId` int(11) DEFAULT NULL,
                  `in_queue` tinyint(1) DEFAULT NULL,
                  `worker_timestamp` int(20) DEFAULT NULL,
                  `worker_id` varchar(20) DEFAULT NULL,
                  PRIMARY KEY (`o_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        \Pimcore\Db::get()->query(
            'CREATE TABLE IF NOT EXISTS `' . Dao::TABLE_NAME . '` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) DEFAULT NULL,
                  `description` varchar(255) DEFAULT NULL,
                  `category` varchar(255) DEFAULT NULL,
                  `ownerId` int(20) DEFAULT NULL,
                  `config` text CHARACTER SET latin1,
                  `sharedUserIds` varchar(1000) DEFAULT NULL,
                  `shortCutUserIds` text CHARACTER SET latin1,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );

        //insert permission
        $key = 'bundle_advancedsearch_search';
        $permission = new \Pimcore\Model\User\Permission\Definition();
        $permission->setKey($key);

        $res = new \Pimcore\Model\User\Permission\Definition\Dao();
        $res->configure(\Pimcore\Db::get());
        $res->setModel($permission);
        $res->save();
    }

    public function isInstalled()
    {
        $result = null;

        try {
            if (Config::getSystemConfig()) {
                $result = \Pimcore\Db::get()->fetchAll("SHOW TABLES LIKE '" . self::QUEUE_TABLE_NAME . "';");
            }
        } catch (\Exception $e) {
            return false;
        }

        return !empty($result);
    }
}
