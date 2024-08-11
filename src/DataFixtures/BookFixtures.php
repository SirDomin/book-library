<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $bookData = [
            ['title' => 'Book One', 'author' => 'Author One', 'description' => 'Description One', 'publishYear' => 2021, 'isbn' => '978-3-16-148410-0', 'image' => 'default.jpg'],
            ['title' => 'Book Two', 'author' => 'Author Two', 'description' => 'Description Two', 'publishYear' => 2022, 'isbn' => '978-3-16-148411-7', 'image' => 'default.jpg'],
            ['title' => 'Another Book', 'author' => 'Author Three', 'description' => 'Description Three', 'publishYear' => 2023, 'isbn' => '978-3-16-148412-4', 'image' => 'default.jpg'],
        ];

        foreach ($bookData as $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setAuthor($data['author']);
            $book->setDescription($data['description']);
            $book->setPublishYear($data['publishYear']);
            $book->setIsbn($data['isbn']);
            $book->setImage($data['image']);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
