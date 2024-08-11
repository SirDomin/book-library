<?php

namespace App\EventListener;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Book::class)]
class BookAddedListener
{
    public function postPersist(Book $book): void
    {
        $currentTime = new \DateTime('now');

        $logMessage = sprintf(
            '[%s] A new book was added: Title: %s, Author: %s, ISBN: %s%s',
            $currentTime->format(DATE_ATOM),
            $book->getTitle(),
            $book->getAuthor(),
            $book->getIsbn(),
            PHP_EOL
        );

        $logFile = __DIR__.'/../../book_added.log';

        $result = @file_put_contents($logFile, $logMessage, FILE_APPEND);

        if (false === $result) {
            throw new \RuntimeException(sprintf('Failed to write to log file: %s', $logFile));
        }
    }
}
