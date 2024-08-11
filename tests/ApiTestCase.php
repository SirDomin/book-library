<?php

namespace App\Tests;

use App\DataFixtures\BookFixtures;
use App\DataFixtures\UserFixtures;
use App\Kernel;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTestCase extends WebTestCase
{
    /** @var KernelBrowser|null */
    protected $client;

    protected EntityManagerInterface $entityManager;

    protected AbstractDatabaseTool $databaseTool;

    protected string $bearerToken;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /**
     * @before
     */
    public function setUpClient(): void
    {
        $this->client = static::createClient();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel(['environment' => 'test']);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $this->databaseTool->loadFixtures([
            UserFixtures::class,
            BookFixtures::class,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }

    public function loginAs(string $email, string $password): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        $this->bearerToken = $data['token'];
    }

    public function request(string $uri, string $method, array $data): array
    {
        $this->client->request(
            $method,
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
