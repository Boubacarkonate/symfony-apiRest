<?php

namespace App\Tests\Functional;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        // Repart d'un schéma propre pour chaque test (base SQLite de test isolée).
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    private function post(string $uri, array $payload): void
    {
        $this->client->request(
            'POST',
            $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload)
        );
    }

    public function testCreateThenListTasks(): void
    {
        $this->post('/api/tasks', ['title' => 'Écrire les tests']);

        $this->assertResponseStatusCodeSame(201);
        $created = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Écrire les tests', $created['title']);
        $this->assertFalse($created['done']);
        $this->assertSame(2, $created['priority']);

        $this->client->request('GET', '/api/tasks');
        $this->assertResponseIsSuccessful();
        $list = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $list);
    }

    public function testCreateFailsWithEmptyTitle(): void
    {
        $this->post('/api/tasks', ['title' => '']);

        $this->assertResponseStatusCodeSame(422);
        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('title', $body['errors']);
    }

    public function testShowUnknownTaskReturns404(): void
    {
        $this->client->request('GET', '/api/tasks/999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testToggleThenDeleteTask(): void
    {
        $task = (new Task())->setTitle('Tâche à basculer');
        $this->em->persist($task);
        $this->em->flush();
        $id = $task->getId();

        $this->client->request('PATCH', "/api/tasks/{$id}/toggle");
        $this->assertResponseIsSuccessful();
        $toggled = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($toggled['done']);

        $this->client->request('DELETE', "/api/tasks/{$id}");
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', "/api/tasks/{$id}");
        $this->assertResponseStatusCodeSame(404);
    }
}
