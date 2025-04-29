<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Infrastructure\Ui\PublicationProgress;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\Dictionary;
use Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Ui\PublicationProgress\StepResolver;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Ui\PublicationProgress\TestStatusDictionary;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StepResolverTest extends TestCase
{
    private readonly StepResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new StepResolver();
    }

    public function testSupportsReturnsTrueForDictionaryInstance(): void
    {
        $this->assertTrue($this->resolver->supports(Dictionary::Created));
        $this->assertTrue($this->resolver->supports(Dictionary::Done));
    }

    public function testSupportsReturnsFalseForNonDictionaryInstance(): void
    {
        $this->assertFalse($this->resolver->supports(TestStatusDictionary::Test));
    }

    #[DataProvider('validStatusProvider')]
    public function testResolveReturnsCorrectStepForValidStatus(Dictionary $status, int $expectedStep): void
    {
        $step = $this->resolver->resolve($status);
        $this->assertSame($expectedStep, $step->value);
    }

    /**
     * @return iterable<array>
     */
    public static function validStatusProvider(): iterable
    {
        yield [Dictionary::Created, 1];
        yield [Dictionary::FrontendMergeRequestIntoProductionReleaseBranchCreated, 1];
        yield [Dictionary::Done, 32];
    }

    public function testResolveThrowsExceptionForUnsupportedStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported status `Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Ui\PublicationProgress\TestStatusDictionary::Test`');
        $this->resolver->resolve(TestStatusDictionary::Test);
    }
}
