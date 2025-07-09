<?php

namespace App\Tests\Service;

use App\Entity\Track;
use App\Repository\TrackRepository;
use App\Service\TrackService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TrackServiceTest extends TestCase
{
    private TrackService $trackService;
    private TrackRepository $trackRepository;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->trackRepository = $this->createMock(TrackRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->trackService = new TrackService($this->trackRepository, $this->validator);
    }

    public function testGetAllTracks(): void
    {
        $tracks = [
            $this->makeTrackWithId(1, 'Test 1', 'Artist 1', 120),
            $this->makeTrackWithId(2, 'Test 2', 'Artist 2', 180),
        ];

        $this->trackRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($tracks);

        $result = $this->trackService->getAllTracks();

        $this->assertSame($tracks, $result);
    }

    public function testCreateTrackSuccess(): void
    {
        $data = [
            'title' => 'Test Track',
            'artist' => 'Test Artist',
            'duration' => 180,
            'isrc' => 'GB-TST-23-12345'
        ];

        $track = new Track();
        $track->setTitle($data['title'])
              ->setArtist($data['artist'])
              ->setDuration($data['duration'])
              ->setIsrc($data['isrc']);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $this->trackRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Track::class), true);

        $result = $this->trackService->createTrack($data);

        $this->assertIsArray($result);
        $this->assertEquals($data['title'], $result['title']);
        $this->assertEquals($data['artist'], $result['artist']);
        $this->assertEquals($data['duration'], $result['duration']);
        $this->assertEquals($data['isrc'], $result['isrc']);
    }

    public function testCreateTrackWithValidationError(): void
    {
        $data = [
            'title' => '',
            'artist' => '',
            'duration' => -1
        ];

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Title is required', '', [], '', 'title', ''),
            new ConstraintViolation('Artist is required', '', [], '', 'artist', ''),
            new ConstraintViolation('Duration must be positive', '', [], '', 'duration', -1),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->trackRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('{"errors":["Title is required","Artist is required","Duration must be positive"]}');

        $this->trackService->createTrack($data);
    }

    public function testUpdateTrackSuccess(): void
    {
        $id = 1;
        $data = [
            'title' => 'Updated Title',
            'duration' => 240
        ];

        $existingTrack = $this->makeTrackWithId($id, 'Old Title', 'Old Artist', 180, 'GB-OLD-23-12345');

        $this->trackRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($existingTrack);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $this->trackRepository
            ->expects($this->once())
            ->method('save')
            ->with($existingTrack, true);

        $result = $this->trackService->updateTrack($id, $data);

        $this->assertIsArray($result);
        $this->assertEquals($id, $result['id']);
        $this->assertEquals('Updated Title', $result['title']);
        $this->assertEquals('Old Artist', $result['artist']); // Should remain unchanged
        $this->assertEquals(240, $result['duration']);
        $this->assertEquals('GB-OLD-23-12345', $result['isrc']); // Should remain unchanged
    }

    public function testUpdateTrackNotFound(): void
    {
        $id = 999;
        $data = ['title' => 'Updated Title'];

        $this->trackRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $this->trackRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Track not found');

        $this->trackService->updateTrack($id, $data);
    }

    public function testUpdateTrackWithValidationError(): void
    {
        $id = 1;
        $data = ['title' => '', 'duration' => -1];

        $existingTrack = $this->makeTrackWithId($id, 'Old Title', 'Old Artist', 180);

        $this->trackRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($existingTrack);

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Title is required', '', [], '', 'title', ''),
            new ConstraintViolation('Duration must be positive', '', [], '', 'duration', -1),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->trackRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('{"errors":["Title is required","Duration must be positive"]}');

        $this->trackService->updateTrack($id, $data);
    }

    public function testGetTrack(): void
    {
        $id = 1;
        $track = $this->makeTrackWithId($id, 'Test', 'Artist', 120);

        $this->trackRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($track);

        $result = $this->trackService->getTrack($id);

        $this->assertSame($track, $result);
    }

    public function testGetTrackNotFound(): void
    {
        $id = 999;

        $this->trackRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $result = $this->trackService->getTrack($id);

        $this->assertNull($result);
    }

    public function testTracksToArray(): void
    {
        $tracks = [
            $this->makeTrackWithId(1, 'Track 1', 'Artist 1', 120, 'GB-TST-23-12345'),
            $this->makeTrackWithId(2, 'Track 2', 'Artist 2', 180, null),
        ];

        $result = $this->trackService->tracksToArray($tracks);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        $this->assertEquals([
            'id' => 1,
            'title' => 'Track 1',
            'artist' => 'Artist 1',
            'duration' => 120,
            'isrc' => 'GB-TST-23-12345'
        ], $result[0]);

        $this->assertEquals([
            'id' => 2,
            'title' => 'Track 2',
            'artist' => 'Artist 2',
            'duration' => 180,
            'isrc' => null
        ], $result[1]);
    }

    private function makeTrackWithId($id, $title, $artist, $duration, $isrc = null): Track
    {
        $track = new Track();
        $track->setTitle($title)
              ->setArtist($artist)
              ->setDuration($duration)
              ->setIsrc($isrc);
        $ref = new \ReflectionProperty(Track::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($track, $id);
        return $track;
    }
} 