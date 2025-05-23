<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250523092317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE monthly_budget DROP FOREIGN KEY FK_905264B09D86650F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_905264B09D86650F ON monthly_budget
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE monthly_budget ADD user_id INT NOT NULL, DROP user_id_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE monthly_budget ADD CONSTRAINT FK_905264B0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_905264B0A76ED395 ON monthly_budget (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_723705D1A76ED395 ON transaction (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE monthly_budget DROP FOREIGN KEY FK_905264B0A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_905264B0A76ED395 ON monthly_budget
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE monthly_budget ADD user_id_id INT DEFAULT NULL, DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE monthly_budget ADD CONSTRAINT FK_905264B09D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_905264B09D86650F ON monthly_budget (user_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_723705D1A76ED395 ON transaction
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP user_id
        SQL);
    }
}
