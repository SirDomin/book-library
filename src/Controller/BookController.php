<?php

namespace App\Controller;

use App\Entity\Book;
use App\Manager\FileManager;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private FileManager $fileManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        FileManager $fileManager,
    ) {
        $this->entityManager = $entityManager;
        $this->fileManager = $fileManager;
    }

    #[Route('/api/books', name: 'create_book', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title']) || empty($data['author']) || empty($data['publishYear']) || empty($data['isbn']) || empty($data['image'])) {
            return $this->json(['error' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        $book = new Book();
        $book->setDescription($data['description']);
        $book->setAuthor($data['author']);
        $book->setIsbn($data['isbn']);
        $book->setTitle($data['title']);
        $book->setPublishYear($data['publishYear']);

        try {
            $imageName = $this->fileManager->saveFile($data['image'], $data['extension']);
        } catch (FileException $e) {
            return $this->json(['error' => 'Failed to upload image'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $book->setImage($imageName);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json(['message' => 'Book created successfully', 'book' => $book->toArray()], Response::HTTP_CREATED);
    }

    #[Route('/api/books', name: 'get_books', methods: ['GET'])]
    public function getBooks(Request $request, BookRepository $bookRepository): JsonResponse
    {
        $search = $request->query->get('search');
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);

        if ($page < 1) {
            return $this->json(['error' => 'Page must be greater than 0'], Response::HTTP_BAD_REQUEST);
        }

        if ($limit < 1) {
            return $this->json(['error' => 'Limit must be greater than 0'], Response::HTTP_BAD_REQUEST);
        }

        $booksData = $bookRepository->findPaginatedBooks($search, $page, $limit);
        $data = array_map(fn ($book) => $book->toArray(), $booksData['books']);

        return $this->json([
            'data' => $data,
            'page' => $page,
            'limit' => $limit,
            'total' => $booksData['total'],
            'pages' => $booksData['pages'],
        ]);
    }

    #[Route('/api/books/{id}', name: 'get_book', methods: ['GET'])]
    public function getBook(Book $book): JsonResponse
    {
        return $this->json($book->toArray());
    }

    #[Route('/api/books/{id}', name: 'update_book', methods: ['PUT'])]
    public function updateBook(Request $request, Book $book): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) {
            $book->setTitle($data['title']);
        }
        if (isset($data['author'])) {
            $book->setAuthor($data['author']);
        }
        if (isset($data['description'])) {
            $book->setDescription($data['description']);
        }
        if (isset($data['publishYear'])) {
            $book->setPublishYear($data['publishYear']);
        }
        if (isset($data['isbn'])) {
            $book->setIsbn($data['isbn']);
        }

        if (isset($data['image'])) {
            $imageName = $this->fileManager->saveFile($data['image'], $data['extension']);
            $book->setImage($imageName);
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json($book->toArray());
    }

    #[Route('/api/books/{id}', name: 'delete_book', methods: ['DELETE'])]
    public function deleteBook(Book $book): JsonResponse
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/books/image/{fileName}', name: 'get_image', methods: ['GET'])]
    public function getFile(string $fileName): Response
    {
        try {
            return $this->fileManager->getFileContent($fileName);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
