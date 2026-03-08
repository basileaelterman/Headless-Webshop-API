<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308092320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shopping_cart_shopping_cart_item DROP FOREIGN KEY `FK_F57785813B3A089F`');
        $this->addSql('ALTER TABLE shopping_cart_shopping_cart_item DROP FOREIGN KEY `FK_F577858145F80CD`');
        $this->addSql('DROP TABLE shopping_cart_shopping_cart_item');
        $this->addSql('ALTER TABLE shopping_cart_items ADD shopping_cart_id INT NOT NULL');
        $this->addSql('ALTER TABLE shopping_cart_items ADD CONSTRAINT FK_A13B631345F80CD FOREIGN KEY (shopping_cart_id) REFERENCES shopping_carts (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A13B631345F80CD ON shopping_cart_items (shopping_cart_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shopping_cart_shopping_cart_item (shopping_cart_id INT NOT NULL, shopping_cart_item_id INT NOT NULL, INDEX IDX_F57785813B3A089F (shopping_cart_item_id), INDEX IDX_F577858145F80CD (shopping_cart_id), PRIMARY KEY (shopping_cart_id, shopping_cart_item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE shopping_cart_shopping_cart_item ADD CONSTRAINT `FK_F57785813B3A089F` FOREIGN KEY (shopping_cart_item_id) REFERENCES shopping_cart_items (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shopping_cart_shopping_cart_item ADD CONSTRAINT `FK_F577858145F80CD` FOREIGN KEY (shopping_cart_id) REFERENCES shopping_carts (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shopping_cart_items DROP FOREIGN KEY FK_A13B631345F80CD');
        $this->addSql('DROP INDEX IDX_A13B631345F80CD ON shopping_cart_items');
        $this->addSql('ALTER TABLE shopping_cart_items DROP shopping_cart_id');
    }
}
