<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427194853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE route (id INT AUTO_INCREMENT NOT NULL, start_location VARCHAR(255) NOT NULL, end_location VARCHAR(255) NOT NULL, waypoints LONGTEXT DEFAULT NULL, estimation_duration INT NOT NULL, vehicule_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE events ADD route_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE events ADD CONSTRAINT FK_5387574A34ECB4E6 FOREIGN KEY (route_id) REFERENCES route (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5387574A34ECB4E6 ON events (route_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE events DROP FOREIGN KEY FK_5387574A34ECB4E6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE route
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5387574A34ECB4E6 ON events
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE events DROP route_id
        SQL);
    }
}
