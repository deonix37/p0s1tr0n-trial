<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240115163856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, title VARCHAR(255) NOT NULL, isbn VARCHAR(255) NOT NULL, page_count INT NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, published_date DATE DEFAULT NULL, UNIQUE INDEX UNIQ_CBE5A331CC1CF4E6 (isbn), INDEX IDX_CBE5A3316BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_book_category (book_id INT NOT NULL, book_category_id INT NOT NULL, INDEX IDX_7A5A379416A2B381 (book_id), INDEX IDX_7A5A379440B1D29E (book_category_id), PRIMARY KEY(book_id, book_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_book_author (book_id INT NOT NULL, book_author_id INT NOT NULL, INDEX IDX_C68F9C3916A2B381 (book_id), INDEX IDX_C68F9C39E4DBE55D (book_author_id), PRIMARY KEY(book_id, book_author_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_author (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9478D3455E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_category (id INT AUTO_INCREMENT NOT NULL, parent_category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1FB30F982B36786B (title), INDEX IDX_1FB30F98796A8F92 (parent_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_status (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_52D76E912B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A3316BF700BD FOREIGN KEY (status_id) REFERENCES book_status (id)');
        $this->addSql('ALTER TABLE book_book_category ADD CONSTRAINT FK_7A5A379416A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_book_category ADD CONSTRAINT FK_7A5A379440B1D29E FOREIGN KEY (book_category_id) REFERENCES book_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_book_author ADD CONSTRAINT FK_C68F9C3916A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_book_author ADD CONSTRAINT FK_C68F9C39E4DBE55D FOREIGN KEY (book_author_id) REFERENCES book_author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_category ADD CONSTRAINT FK_1FB30F98796A8F92 FOREIGN KEY (parent_category_id) REFERENCES book_category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A3316BF700BD');
        $this->addSql('ALTER TABLE book_book_category DROP FOREIGN KEY FK_7A5A379416A2B381');
        $this->addSql('ALTER TABLE book_book_category DROP FOREIGN KEY FK_7A5A379440B1D29E');
        $this->addSql('ALTER TABLE book_book_author DROP FOREIGN KEY FK_C68F9C3916A2B381');
        $this->addSql('ALTER TABLE book_book_author DROP FOREIGN KEY FK_C68F9C39E4DBE55D');
        $this->addSql('ALTER TABLE book_category DROP FOREIGN KEY FK_1FB30F98796A8F92');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE book_book_category');
        $this->addSql('DROP TABLE book_book_author');
        $this->addSql('DROP TABLE book_author');
        $this->addSql('DROP TABLE book_category');
        $this->addSql('DROP TABLE book_status');
    }
}
