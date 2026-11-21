<?php

namespace App\Tests\State;

use App\State\AccreditationCycleProcessor;
use PHPUnit\Framework\TestCase;

class AccreditationCycleProcessorTest extends TestCase
{
    public function testUndergraduateLevelThreeUsesInstructionAndExtension(): void
    {
        self::assertSame(['III', 'VI'], AccreditationCycleProcessor::mandatoryAreaNumbers('undergraduate'));
    }

    public function testGraduateLevelThreeUsesInstructionAndResearch(): void
    {
        self::assertSame(['III', 'V'], AccreditationCycleProcessor::mandatoryAreaNumbers('graduate'));
    }
}
