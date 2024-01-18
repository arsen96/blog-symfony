<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240116204414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment ADD status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('CREATE INDEX IDX_9474526C6BF700BD ON comment (status_id)');
        $this->addSql('ALTER TABLE status DROP FOREIGN KEY FK_7B00651CF8697D13');
        $this->addSql('DROP INDEX IDX_7B00651CF8697D13 ON status');
        $this->addSql('ALTER TABLE status DROP comment_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C6BF700BD');
        $this->addSql('DROP INDEX IDX_9474526C6BF700BD ON comment');
        $this->addSql('ALTER TABLE comment DROP status_id');
        $this->addSql('ALTER TABLE status ADD comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651CF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('CREATE INDEX IDX_7B00651CF8697D13 ON status (comment_id)');
    }
}
