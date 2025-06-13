<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613074543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child ADD parent_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child ADD CONSTRAINT FK_22B35429727ACA70 FOREIGN KEY (parent_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_22B35429727ACA70 ON child (parent_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child DROP FOREIGN KEY FK_22B35429727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_22B35429727ACA70 ON child
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child DROP parent_id
        SQL);
    }
}
