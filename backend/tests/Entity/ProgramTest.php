<?php

namespace App\Tests\Entity;

use App\Entity\Program;
use PHPUnit\Framework\TestCase;

class ProgramTest extends TestCase
{
    public function testValidityStartCanBeStored(): void
    {
        $date = new \DateTimeImmutable('2026-01-15');
        $program = new Program();

        $program->setValidityStart($date);

        self::assertSame($date, $program->getValidityStart());
    }
}
