<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240220105703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649857C9F24');
        $this->addSql('DROP INDEX IDX_8D93D649857C9F24 ON user');
        $this->addSql('ALTER TABLE user CHANGE subscription_id_id subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499A1887DC FOREIGN KEY (subscription_id) REFERENCES `subscription` (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6499A1887DC ON user (subscription_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6499A1887DC');
        $this->addSql('DROP INDEX IDX_8D93D6499A1887DC ON `user`');
        $this->addSql('ALTER TABLE `user` CHANGE subscription_id subscription_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649857C9F24 FOREIGN KEY (subscription_id_id) REFERENCES subscription (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649857C9F24 ON `user` (subscription_id_id)');
    }
}
