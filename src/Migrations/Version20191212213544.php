<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191212213544 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE articles (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, user_id INT NOT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, image VARCHAR(100) DEFAULT NULL, update_date DATETIME NOT NULL, create_date DATETIME NOT NULL, go_on_public DATETIME DEFAULT NULL, is_visible TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_BFDD3168989D9B62 (slug), INDEX IDX_BFDD316812469DE2 (category_id), INDEX IDX_BFDD3168A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE articles_tags (articles_id INT NOT NULL, tags_id INT NOT NULL, INDEX IDX_354053611EBAF6CC (articles_id), INDEX IDX_354053618D7B4FB4 (tags_id), PRIMARY KEY(articles_id, tags_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, login VARCHAR(100) NOT NULL, password VARBINARY(255) NOT NULL, name VARCHAR(100) DEFAULT NULL, registration_date DATETIME NOT NULL, target TINYINT(1) NOT NULL, gender VARCHAR(10) DEFAULT NULL, birth_date DATE DEFAULT NULL, INDEX IDX_1483A5E96BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_articles (users_id INT NOT NULL, articles_id INT NOT NULL, INDEX IDX_C49C1AB267B3B43D (users_id), INDEX IDX_C49C1AB21EBAF6CC (articles_id), PRIMARY KEY(users_id, articles_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_users (users_source INT NOT NULL, users_target INT NOT NULL, INDEX IDX_F3F401A0506DF1E3 (users_source), INDEX IDX_F3F401A04988A16C (users_target), PRIMARY KEY(users_source, users_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statuses (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_visible TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, is_visible TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_6FBC9426989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(255) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD316812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD3168A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE articles_tags ADD CONSTRAINT FK_354053611EBAF6CC FOREIGN KEY (articles_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE articles_tags ADD CONSTRAINT FK_354053618D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E96BF700BD FOREIGN KEY (status_id) REFERENCES statuses (id)');
        $this->addSql('ALTER TABLE users_articles ADD CONSTRAINT FK_C49C1AB267B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_articles ADD CONSTRAINT FK_C49C1AB21EBAF6CC FOREIGN KEY (articles_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_users ADD CONSTRAINT FK_F3F401A0506DF1E3 FOREIGN KEY (users_source) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_users ADD CONSTRAINT FK_F3F401A04988A16C FOREIGN KEY (users_target) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE articles_tags DROP FOREIGN KEY FK_354053611EBAF6CC');
        $this->addSql('ALTER TABLE users_articles DROP FOREIGN KEY FK_C49C1AB21EBAF6CC');
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD3168A76ED395');
        $this->addSql('ALTER TABLE users_articles DROP FOREIGN KEY FK_C49C1AB267B3B43D');
        $this->addSql('ALTER TABLE users_users DROP FOREIGN KEY FK_F3F401A0506DF1E3');
        $this->addSql('ALTER TABLE users_users DROP FOREIGN KEY FK_F3F401A04988A16C');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E96BF700BD');
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD316812469DE2');
        $this->addSql('ALTER TABLE articles_tags DROP FOREIGN KEY FK_354053618D7B4FB4');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE articles_tags');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE users_articles');
        $this->addSql('DROP TABLE users_users');
        $this->addSql('DROP TABLE statuses');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE ext_log_entries');
    }
}
