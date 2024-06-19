<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303213902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, star1 TINYINT(1) DEFAULT NULL, star2 TINYINT(1) DEFAULT NULL, star3 TINYINT(1) DEFAULT NULL, star4 TINYINT(1) DEFAULT NULL, star5 TINYINT(1) DEFAULT NULL, commentaire VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8F91ABF0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE abonnement ADD coach_id INT DEFAULT NULL, ADD nutri_id INT DEFAULT NULL, ADD client_id INT NOT NULL');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BB3C105691 FOREIGN KEY (coach_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BBD29CCABF FOREIGN KEY (nutri_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BB19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_351268BB3C105691 ON abonnement (coach_id)');
        $this->addSql('CREATE INDEX IDX_351268BBD29CCABF ON abonnement (nutri_id)');
        $this->addSql('CREATE INDEX IDX_351268BB19EB6921 ON abonnement (client_id)');
        $this->addSql('ALTER TABLE suivi ADD user_id INT NOT NULL, ADD title VARCHAR(100) NOT NULL, ADD start DATETIME NOT NULL, ADD end DATETIME NOT NULL, ADD description LONGTEXT NOT NULL, ADD all_day TINYINT(1) NOT NULL, ADD background_colar VARCHAR(7) NOT NULL, ADD border_color VARCHAR(7) NOT NULL, ADD text_color VARCHAR(7) NOT NULL');
        $this->addSql('ALTER TABLE suivi ADD CONSTRAINT FK_2EBCCA8FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2EBCCA8FA76ED395 ON suivi (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0A76ED395');
        $this->addSql('DROP TABLE avis');
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BB3C105691');
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BBD29CCABF');
        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BB19EB6921');
        $this->addSql('DROP INDEX IDX_351268BB3C105691 ON abonnement');
        $this->addSql('DROP INDEX IDX_351268BBD29CCABF ON abonnement');
        $this->addSql('DROP INDEX IDX_351268BB19EB6921 ON abonnement');
        $this->addSql('ALTER TABLE abonnement DROP coach_id, DROP nutri_id, DROP client_id');
        $this->addSql('ALTER TABLE suivi DROP FOREIGN KEY FK_2EBCCA8FA76ED395');
        $this->addSql('DROP INDEX IDX_2EBCCA8FA76ED395 ON suivi');
        $this->addSql('ALTER TABLE suivi DROP user_id, DROP title, DROP start, DROP end, DROP description, DROP all_day, DROP background_colar, DROP border_color, DROP text_color');
    }
}
