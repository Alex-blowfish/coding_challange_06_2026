<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260610143925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert the very standard products to be available in the online store';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(sql: "
            INSERT INTO
                product ('name', 'price', 'description')
            VALUES ('table', 300.99, 'A big solid wooden table.'),
                   ('chair', 100.99, 'A classy white chair'),
                   ('carpet', 150.99, 'Red carpet. 2m long, 1,05m wide.'),
                   ('TV', 900.99, 'A big an colorful 70 in TV')
            ");

        $this->addSql(sql: "INSERT INTO cart ('id') VALUES (1) ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sql: "
            DELETE FROM product WHERE
                name = 'table' or
                name = 'chair' or
                name = 'carpet' or
                name = 'TV';
        ");

        $this->addSql(sql: "DELETE FROM cart WHERE id = 1");

    }
}
