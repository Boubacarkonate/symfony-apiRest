<?php

namespace App\Tests\Unit;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testANewTaskStartsNotDoneWithNormalPriority(): void
    {
        $task = new Task();

        $this->assertFalse($task->isDone());
        $this->assertSame(2, $task->getPriority());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
    }

    public function testToggleSwitchesTheDoneState(): void
    {
        $task = new Task();

        $task->toggle();
        $this->assertTrue($task->isDone());

        $task->toggle();
        $this->assertFalse($task->isDone());
    }

    public function testSettersAreFluentAndStoreValues(): void
    {
        $task = (new Task())
            ->setTitle('Préparer la démo')
            ->setDescription('API REST de tâches')
            ->setPriority(3);

        $this->assertSame('Préparer la démo', $task->getTitle());
        $this->assertSame('API REST de tâches', $task->getDescription());
        $this->assertSame(3, $task->getPriority());
    }
}
