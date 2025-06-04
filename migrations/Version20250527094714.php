<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527094714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, beginning_hour TIME NOT NULL, ending_hour TIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE activity_group (activity_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_73C2727681C06096 (activity_id), INDEX IDX_73C27276FE54D947 (group_id), PRIMARY KEY(activity_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_group (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_8F02BF9DA76ED395 (user_id), INDEX IDX_8F02BF9DFE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_group ADD CONSTRAINT FK_73C2727681C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_group ADD CONSTRAINT FK_73C27276FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9DFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance ADD child_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D91DD62C21B FOREIGN KEY (child_id) REFERENCES child (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6DE30D91DD62C21B ON attendance (child_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_group DROP FOREIGN KEY FK_73C2727681C06096
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_group DROP FOREIGN KEY FK_73C27276FE54D947
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group DROP FOREIGN KEY FK_8F02BF9DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group DROP FOREIGN KEY FK_8F02BF9DFE54D947
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE activity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE activity_group
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_group
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D91DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6DE30D91DD62C21B ON attendance
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance DROP child_id
        SQL);
    }
}
