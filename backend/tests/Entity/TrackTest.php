<?php

namespace App\Tests\Entity;

use App\Entity\Track;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrackTest extends KernelTestCase
{
    private $validator;

    protected function setUp(): void
    {
        if (method_exists(static::class, 'getContainer')) {
            $this->validator = static::getContainer()->get('validator');
        } else {
            $this->markTestSkipped('Symfony test container not available.');
        }
    }

    public function testValidTrack(): void
    {
        $track = new Track();
        $track->setTitle('Valid Track')
              ->setArtist('Valid Artist')
              ->setDuration(180)
              ->setIsrc('GB-VAL-24-12345');

        $violations = $this->validator->validate($track);
        $this->assertCount(0, $violations);
    }

    public function testTrackWithBlankTitle(): void
    {
        $track = new Track();
        $track->setTitle('')
              ->setArtist('Valid Artist')
              ->setDuration(180);

        $violations = $this->validator->validate($track);
        $this->assertCount(1, $violations);
        $this->assertEquals('Title is required', $violations[0]->getMessage());
    }

    public function testTrackWithBlankArtist(): void
    {
        $track = new Track();
        $track->setTitle('Valid Title')
              ->setArtist('')
              ->setDuration(180);

        $violations = $this->validator->validate($track);
        $this->assertCount(1, $violations);
        $this->assertEquals('Artist is required', $violations[0]->getMessage());
    }

    public function testTrackWithNegativeDuration(): void
    {
        $track = new Track();
        $track->setTitle('Valid Title')
              ->setArtist('Valid Artist')
              ->setDuration(-1);

        $violations = $this->validator->validate($track);
        $this->assertCount(1, $violations);
        $this->assertEquals('Duration must be a positive number', $violations[0]->getMessage());
    }

    public function testTrackWithZeroDuration(): void
    {
        $track = new Track();
        $track->setTitle('Valid Title')
              ->setArtist('Valid Artist')
              ->setDuration(0);

        $violations = $this->validator->validate($track);
        $this->assertCount(1, $violations);
        $this->assertEquals('Duration must be a positive number', $violations[0]->getMessage());
    }

    public function testTrackWithInvalidIsrc(): void
    {
        $track = new Track();
        $track->setTitle('Valid Title')
              ->setArtist('Valid Artist')
              ->setDuration(180)
              ->setIsrc('INVALID-ISRC');

        $violations = $this->validator->validate($track);
        $this->assertCount(1, $violations);
        $this->assertEquals('ISRC must match the format: XX-XXX-XX-XXXXX', $violations[0]->getMessage());
    }

    public function testTrackWithValidIsrc(): void
    {
        $track = new Track();
        $track->setTitle('Valid Title')
              ->setArtist('Valid Artist')
              ->setDuration(180)
              ->setIsrc('GB-VAL-24-12345');

        $violations = $this->validator->validate($track);
        $this->assertCount(0, $violations);
    }

    public function testTrackWithNullIsrc(): void
    {
        $track = new Track();
        $track->setTitle('Valid Title')
              ->setArtist('Valid Artist')
              ->setDuration(180)
              ->setIsrc(null);

        $violations = $this->validator->validate($track);
        $this->assertCount(0, $violations); // ISRC is optional
    }

    public function testMultipleValidationErrors(): void
    {
        $track = new Track();
        $track->setTitle('')
              ->setArtist('')
              ->setDuration(-1)
              ->setIsrc('INVALID');

        $violations = $this->validator->validate($track);
        $this->assertCount(4, $violations);

        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
        }

        $this->assertContains('Title is required', $messages);
        $this->assertContains('Artist is required', $messages);
        $this->assertContains('Duration must be a positive number', $messages);
        $this->assertContains('ISRC must match the format: XX-XXX-XX-XXXXX', $messages);
    }

    public function testGettersAndSetters(): void
    {
        $track = new Track();

        // Test setters and getters
        $track->setTitle('Test Title');
        $this->assertEquals('Test Title', $track->getTitle());

        $track->setArtist('Test Artist');
        $this->assertEquals('Test Artist', $track->getArtist());

        $track->setDuration(300);
        $this->assertEquals(300, $track->getDuration());

        $track->setIsrc('GB-TST-24-12345');
        $this->assertEquals('GB-TST-24-12345', $track->getIsrc());

        $track->setIsrc(null);
        $this->assertNull($track->getIsrc());
    }

    public function testIdIsInitiallyNull(): void
    {
        $track = new Track();
        $this->assertNull($track->getId());
    }

    public function testFluentInterface(): void
    {
        $track = new Track();
        
        $result = $track->setTitle('Test')
                       ->setArtist('Artist')
                       ->setDuration(180)
                       ->setIsrc('GB-TST-24-12345');

        $this->assertSame($track, $result);
        $this->assertEquals('Test', $track->getTitle());
        $this->assertEquals('Artist', $track->getArtist());
        $this->assertEquals(180, $track->getDuration());
        $this->assertEquals('GB-TST-24-12345', $track->getIsrc());
    }

    public function testIsrcFormatVariations(): void
    {
        $validIsrcs = [
            'GB-EMI-76-12345',
            'US-RC1-77-12345',
            'FR-ABC-78-12345',
            'DE-XYZ-79-12345'
        ];

        foreach ($validIsrcs as $isrc) {
            $track = new Track();
            $track->setTitle('Valid Title')
                  ->setArtist('Valid Artist')
                  ->setDuration(180)
                  ->setIsrc($isrc);

            $violations = $this->validator->validate($track);
            $this->assertCount(0, $violations, "ISRC '{$isrc}' should be valid");
        }
    }

    public function testInvalidIsrcFormats(): void
    {
        $invalidIsrcs = [
            'GB-EMI-76-1234',    // Too short
            'GB-EMI-76-123456',  // Too long
            'GB-EMI7-76-12345',  // Invalid character in middle
            'GB-EMI-7A-12345',   // Invalid character in year
            'GB-EMI-76-1234A',   // Invalid character in number
            'INVALID-ISRC',      // Completely wrong format
            'GB-EMI-76-12345-EXTRA', // Extra characters
        ];

        foreach ($invalidIsrcs as $isrc) {
            $track = new Track();
            $track->setTitle('Valid Title')
                  ->setArtist('Valid Artist')
                  ->setDuration(180)
                  ->setIsrc($isrc);

            $violations = $this->validator->validate($track);
            $this->assertCount(1, $violations, "ISRC '{$isrc}' should be invalid");
            $this->assertEquals('ISRC must match the format: XX-XXX-XX-XXXXX', $violations[0]->getMessage());
        }
    }
} 