<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190726162431 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, sku INT NOT NULL, status TINYINT(1) NOT NULL, individual_discount_percentage INT NOT NULL, base_price NUMERIC(10, 2) NOT NULL, special_price NUMERIC(10, 2) NOT NULL, global_discount_price NUMERIC(10, 2) NOT NULL, no_tax_special_price NUMERIC(10, 2) NOT NULL, no_tax_global_discount_price NUMERIC(10, 2) NOT NULL, tax_price NUMERIC(10, 2) NOT NULL, image_url LONGTEXT NOT NULL, description LONGTEXT NOT NULL, review_count INT DEFAULT NULL, review_sum INT DEFAULT NULL, review_average_score NUMERIC(10, 1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product');
    }
}
