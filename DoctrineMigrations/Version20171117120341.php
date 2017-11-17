<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171117120341 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE keyword (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, from_place VARCHAR(4096) DEFAULT NULL, search_engine_request_limit INT NOT NULL, position_last_check DATETIME DEFAULT NULL, position_locked_at DATETIME DEFAULT NULL, name VARCHAR(4096) NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, INDEX IDX_5A93713BF6BD1646 (site_id), INDEX IDX_5A93713BB03A8386 (created_by_id), INDEX IDX_5A93713B99049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keyword_page (keyword_id INT NOT NULL, page_id INT NOT NULL, INDEX IDX_F35C3B22115D4552 (keyword_id), INDEX IDX_F35C3B22C4663E4 (page_id), PRIMARY KEY(keyword_id, page_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keyword_search_engine (keyword_id INT NOT NULL, search_engine_id INT NOT NULL, INDEX IDX_9CC63AA8115D4552 (keyword_id), INDEX IDX_9CC63AA85C978CA2 (search_engine_id), PRIMARY KEY(keyword_id, search_engine_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keyword_competitor (id INT AUTO_INCREMENT NOT NULL, keyword_id INT NOT NULL, search_engine_id INT NOT NULL, position INT NOT NULL, url TEXT DEFAULT NULL, domain TEXT DEFAULT NULL, document_modification_time INT DEFAULT NULL, document_charset VARCHAR(50) DEFAULT NULL, document_lang VARCHAR(50) DEFAULT NULL, document_mime_type VARCHAR(50) DEFAULT NULL, document_title TEXT DEFAULT NULL, document_headline TEXT DEFAULT NULL, document_passages_type VARCHAR(50) DEFAULT NULL, document_passages LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', saved_copy_url TEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_A6A078D9115D4552 (keyword_id), INDEX IDX_A6A078D95C978CA2 (search_engine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keyword_position (id INT AUTO_INCREMENT NOT NULL, keyword_id INT NOT NULL, search_engine_id INT NOT NULL, position INT NOT NULL, url VARCHAR(4096) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_D74729A4115D4552 (keyword_id), INDEX IDX_D74729A45C978CA2 (search_engine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keyword_position_log (id INT AUTO_INCREMENT NOT NULL, keyword_position_id INT DEFAULT NULL, status INT NOT NULL, errors LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', requests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', responses LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_904E1CB714CE7B83 (keyword_position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, name VARCHAR(4096) NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, INDEX IDX_140AB620F6BD1646 (site_id), INDEX IDX_140AB620B03A8386 (created_by_id), INDEX IDX_140AB62099049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_search_engine (page_id INT NOT NULL, search_engine_id INT NOT NULL, INDEX IDX_A09005A9C4663E4 (page_id), INDEX IDX_A09005A95C978CA2 (search_engine_id), PRIMARY KEY(page_id, search_engine_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE search_engine (id INT AUTO_INCREMENT NOT NULL, short_name VARCHAR(4096) NOT NULL, type INT NOT NULL, check_keyword_position_periodicity INT NOT NULL, check_keyword_position_timeout_between_requests INT NOT NULL, check_keyword_position_request_sites_per_page INT NOT NULL, name VARCHAR(4096) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, name_puny VARCHAR(4096) NOT NULL, seo_strategy_keyword_page INT NOT NULL, name VARCHAR(4096) NOT NULL, active TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, INDEX IDX_694309E4B03A8386 (created_by_id), INDEX IDX_694309E499049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_schedule (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, interval_between_site_download VARCHAR(255) NOT NULL, interval_between_page_download VARCHAR(255) NOT NULL, max_time_limit_for_site_download VARCHAR(255) NOT NULL, max_depth_level_limit_for_site_download VARCHAR(255) NOT NULL, use_user_agent_from_robots_txt VARCHAR(255) NOT NULL, follow_no_follow_links TINYINT(1) NOT NULL, check_external_links_for404 TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_5675AE21F6BD1646 (site_id), INDEX IDX_5675AE21B03A8386 (created_by_id), INDEX IDX_5675AE2199049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_stamp (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, INDEX IDX_91144F0DA40BC2D5 (schedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), INDEX IDX_957A6479B03A8386 (created_by_id), INDEX IDX_957A647999049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_classes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_type VARCHAR(200) NOT NULL, UNIQUE INDEX UNIQ_69DD750638A36066 (class_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_security_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, identifier VARCHAR(200) NOT NULL, username TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8835EE78772E836AF85E0677 (identifier, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_object_identities (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_object_identity_id INT UNSIGNED DEFAULT NULL, class_id INT UNSIGNED NOT NULL, object_identifier VARCHAR(100) NOT NULL, entries_inheriting TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9407E5494B12AD6EA000B10 (object_identifier, class_id), INDEX IDX_9407E54977FA751A (parent_object_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_object_identity_ancestors (object_identity_id INT UNSIGNED NOT NULL, ancestor_id INT UNSIGNED NOT NULL, INDEX IDX_825DE2993D9AB4A6 (object_identity_id), INDEX IDX_825DE299C671CEA1 (ancestor_id), PRIMARY KEY(object_identity_id, ancestor_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_entries (id INT UNSIGNED AUTO_INCREMENT NOT NULL, class_id INT UNSIGNED NOT NULL, object_identity_id INT UNSIGNED DEFAULT NULL, security_identity_id INT UNSIGNED NOT NULL, field_name VARCHAR(50) DEFAULT NULL, ace_order SMALLINT UNSIGNED NOT NULL, mask INT NOT NULL, granting TINYINT(1) NOT NULL, granting_strategy VARCHAR(30) NOT NULL, audit_success TINYINT(1) NOT NULL, audit_failure TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4 (class_id, object_identity_id, field_name, ace_order), INDEX IDX_46C8B806EA000B103D9AB4A6DF9183C9 (class_id, object_identity_id, security_identity_id), INDEX IDX_46C8B806EA000B10 (class_id), INDEX IDX_46C8B8063D9AB4A6 (object_identity_id), INDEX IDX_46C8B806DF9183C9 (security_identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE keyword ADD CONSTRAINT FK_5A93713BF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE keyword ADD CONSTRAINT FK_5A93713BB03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE keyword ADD CONSTRAINT FK_5A93713B99049ECE FOREIGN KEY (modified_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE keyword_page ADD CONSTRAINT FK_F35C3B22115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE keyword_page ADD CONSTRAINT FK_F35C3B22C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE keyword_search_engine ADD CONSTRAINT FK_9CC63AA8115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE keyword_search_engine ADD CONSTRAINT FK_9CC63AA85C978CA2 FOREIGN KEY (search_engine_id) REFERENCES search_engine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE keyword_competitor ADD CONSTRAINT FK_A6A078D9115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id)');
        $this->addSql('ALTER TABLE keyword_competitor ADD CONSTRAINT FK_A6A078D95C978CA2 FOREIGN KEY (search_engine_id) REFERENCES search_engine (id)');
        $this->addSql('ALTER TABLE keyword_position ADD CONSTRAINT FK_D74729A4115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id)');
        $this->addSql('ALTER TABLE keyword_position ADD CONSTRAINT FK_D74729A45C978CA2 FOREIGN KEY (search_engine_id) REFERENCES search_engine (id)');
        $this->addSql('ALTER TABLE keyword_position_log ADD CONSTRAINT FK_904E1CB714CE7B83 FOREIGN KEY (keyword_position_id) REFERENCES keyword_position (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620B03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62099049ECE FOREIGN KEY (modified_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE page_search_engine ADD CONSTRAINT FK_A09005A9C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_search_engine ADD CONSTRAINT FK_A09005A95C978CA2 FOREIGN KEY (search_engine_id) REFERENCES search_engine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4B03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E499049ECE FOREIGN KEY (modified_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE site_schedule ADD CONSTRAINT FK_5675AE21F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE site_schedule ADD CONSTRAINT FK_5675AE21B03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE site_schedule ADD CONSTRAINT FK_5675AE2199049ECE FOREIGN KEY (modified_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE site_stamp ADD CONSTRAINT FK_91144F0DA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES site_schedule (id)');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A6479B03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A647999049ECE FOREIGN KEY (modified_by_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE acl_object_identities ADD CONSTRAINT FK_9407E54977FA751A FOREIGN KEY (parent_object_identity_id) REFERENCES acl_object_identities (id)');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE2993D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors ADD CONSTRAINT FK_825DE299C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806EA000B10 FOREIGN KEY (class_id) REFERENCES acl_classes (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B8063D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_entries ADD CONSTRAINT FK_46C8B806DF9183C9 FOREIGN KEY (security_identity_id) REFERENCES acl_security_identities (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE keyword_page DROP FOREIGN KEY FK_F35C3B22115D4552');
        $this->addSql('ALTER TABLE keyword_search_engine DROP FOREIGN KEY FK_9CC63AA8115D4552');
        $this->addSql('ALTER TABLE keyword_competitor DROP FOREIGN KEY FK_A6A078D9115D4552');
        $this->addSql('ALTER TABLE keyword_position DROP FOREIGN KEY FK_D74729A4115D4552');
        $this->addSql('ALTER TABLE keyword_position_log DROP FOREIGN KEY FK_904E1CB714CE7B83');
        $this->addSql('ALTER TABLE keyword_page DROP FOREIGN KEY FK_F35C3B22C4663E4');
        $this->addSql('ALTER TABLE page_search_engine DROP FOREIGN KEY FK_A09005A9C4663E4');
        $this->addSql('ALTER TABLE keyword_search_engine DROP FOREIGN KEY FK_9CC63AA85C978CA2');
        $this->addSql('ALTER TABLE keyword_competitor DROP FOREIGN KEY FK_A6A078D95C978CA2');
        $this->addSql('ALTER TABLE keyword_position DROP FOREIGN KEY FK_D74729A45C978CA2');
        $this->addSql('ALTER TABLE page_search_engine DROP FOREIGN KEY FK_A09005A95C978CA2');
        $this->addSql('ALTER TABLE keyword DROP FOREIGN KEY FK_5A93713BF6BD1646');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620F6BD1646');
        $this->addSql('ALTER TABLE site_schedule DROP FOREIGN KEY FK_5675AE21F6BD1646');
        $this->addSql('ALTER TABLE site_stamp DROP FOREIGN KEY FK_91144F0DA40BC2D5');
        $this->addSql('ALTER TABLE keyword DROP FOREIGN KEY FK_5A93713BB03A8386');
        $this->addSql('ALTER TABLE keyword DROP FOREIGN KEY FK_5A93713B99049ECE');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620B03A8386');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62099049ECE');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4B03A8386');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E499049ECE');
        $this->addSql('ALTER TABLE site_schedule DROP FOREIGN KEY FK_5675AE21B03A8386');
        $this->addSql('ALTER TABLE site_schedule DROP FOREIGN KEY FK_5675AE2199049ECE');
        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A6479B03A8386');
        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A647999049ECE');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806EA000B10');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B806DF9183C9');
        $this->addSql('ALTER TABLE acl_object_identities DROP FOREIGN KEY FK_9407E54977FA751A');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE2993D9AB4A6');
        $this->addSql('ALTER TABLE acl_object_identity_ancestors DROP FOREIGN KEY FK_825DE299C671CEA1');
        $this->addSql('ALTER TABLE acl_entries DROP FOREIGN KEY FK_46C8B8063D9AB4A6');
        $this->addSql('DROP TABLE keyword');
        $this->addSql('DROP TABLE keyword_page');
        $this->addSql('DROP TABLE keyword_search_engine');
        $this->addSql('DROP TABLE keyword_competitor');
        $this->addSql('DROP TABLE keyword_position');
        $this->addSql('DROP TABLE keyword_position_log');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE page_search_engine');
        $this->addSql('DROP TABLE search_engine');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE site_schedule');
        $this->addSql('DROP TABLE site_stamp');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE acl_classes');
        $this->addSql('DROP TABLE acl_security_identities');
        $this->addSql('DROP TABLE acl_object_identities');
        $this->addSql('DROP TABLE acl_object_identity_ancestors');
        $this->addSql('DROP TABLE acl_entries');
    }
}
