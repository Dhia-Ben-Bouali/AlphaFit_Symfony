<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305113553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, ADD address VARCHAR(255) NOT NULL, ADD phone_number VARCHAR(255) NOT NULL, ADD payment_type VARCHAR(255) NOT NULL, ADD is_valid TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE lignecommande ADD article_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lignecommande ADD CONSTRAINT FK_853B79397294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('CREATE INDEX IDX_853B79397294869C ON lignecommande (article_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP first_name, DROP last_name, DROP address, DROP phone_number, DROP payment_type, DROP is_valid');
        $this->addSql('ALTER TABLE lignecommande DROP FOREIGN KEY FK_853B79397294869C');
        $this->addSql('DROP INDEX IDX_853B79397294869C ON lignecommande');
        $this->addSql('ALTER TABLE lignecommande DROP article_id');
    }
}
