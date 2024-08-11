<?php

namespace App\DataFixtures;

ini_set('memory_limit', '-1');

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;

class HugeBookFixtures extends Fixture
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $batchSize = 1000;
        $totalBooks = 1000000;
        $values = [];

        for ($i = 0; $i < $totalBooks; ++$i) {
            $values[] = sprintf(
                "(%d, 'Title %d', 'Author %d', 'Description for book %d', %d, 'ISBN%d', 'default.jpg')",
                $i, $i, $i, $i, 1500 + ($i % 523), $i
            );

            if (($i + 1) % $batchSize === 0) {
                $query = 'INSERT INTO book (id, title, author, description, publish_year, isbn, image) VALUES '.implode(',', $values);
                $this->connection->executeStatement($query);
                $values = [];
            }
        }

        if (count($values) > 0) {
            $query = 'INSERT INTO book (id, title, author, description, publish_year, isbn, image) VALUES '.implode(',', $values);
            $this->connection->executeStatement($query);
        }
    }
}
