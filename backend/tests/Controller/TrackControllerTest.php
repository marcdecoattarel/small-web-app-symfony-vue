<?php

namespace App\Tests\Controller;

use App\Entity\Track;
use App\Repository\TrackRepository;
use App\Service\TrackService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TrackControllerTest extends WebTestCase
{
    private $client;
    private $trackRepository;
    private $trackService;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->trackRepository = static::getContainer()->get(TrackRepository::class);
        $this->trackService = static::getContainer()->get(TrackService::class);
        
        // Create database schema for tests
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $schemaTool->createSchema($metadata);
    }

    public function testGetTracks(): void
    {
        // Create a test track
        $track = new Track();
        $track->setTitle('Test Track')
              ->setArtist('Test Artist')
              ->setDuration(180)
              ->setIsrc('GB-TST-23-12345');
        
        $this->trackRepository->save($track, true);

        $this->client->request('GET', '/api/tracks');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        
        // Find our test track in the response
        $foundTrack = null;
        foreach ($responseData as $trackData) {
            if ($trackData['title'] === 'Test Track') {
                $foundTrack = $trackData;
                break;
            }
        }
        
        $this->assertNotNull($foundTrack);
        $this->assertEquals('Test Track', $foundTrack['title']);
        $this->assertEquals('Test Artist', $foundTrack['artist']);
        $this->assertEquals(180, $foundTrack['duration']);
        $this->assertEquals('GB-TST-23-12345', $foundTrack['isrc']);
    }

    public function testCreateTrackSuccess(): void
    {
        $trackData = [
            'title' => 'New Track',
            'artist' => 'New Artist',
            'duration' => 240,
            'isrc' => 'GB-NEW-24-12345'
        ];

        $this->client->request(
            'POST',
            '/api/tracks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($trackData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('New Track', $responseData['title']);
        $this->assertEquals('New Artist', $responseData['artist']);
        $this->assertEquals(240, $responseData['duration']);
        $this->assertEquals('GB-NEW-24-12345', $responseData['isrc']);
    }

    public function testCreateTrackWithValidationErrors(): void
    {
        $invalidTrackData = [
            'title' => '',
            'artist' => '',
            'duration' => -1,
            'isrc' => 'INVALID-ISRC'
        ];

        $this->client->request(
            'POST',
            '/api/tracks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidTrackData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals('Validation failed', $responseData['error']);
        $this->assertArrayHasKey('details', $responseData);
        $this->assertIsArray($responseData['details']);
        
        // Check that we have validation errors
        $this->assertGreaterThan(0, count($responseData['details']));
    }

    public function testCreateTrackWithInvalidJson(): void
    {
        $this->client->request(
            'POST',
            '/api/tracks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid JSON data', $responseData['error']);
    }

    public function testUpdateTrackSuccess(): void
    {
        // Create a track to update
        $track = new Track();
        $track->setTitle('Original Title')
              ->setArtist('Original Artist')
              ->setDuration(180)
              ->setIsrc('GB-ORI-23-12345');
        
        $this->trackRepository->save($track, true);
        $trackId = $track->getId();

        $updateData = [
            'title' => 'Updated Title',
            'duration' => 300
        ];

        $this->client->request(
            'PUT',
            "/api/tracks/{$trackId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals($trackId, $responseData['id']);
        $this->assertEquals('Updated Title', $responseData['title']);
        $this->assertEquals('Original Artist', $responseData['artist']); // Should remain unchanged
        $this->assertEquals(300, $responseData['duration']);
        $this->assertEquals('GB-ORI-23-12345', $responseData['isrc']); // Should remain unchanged
    }

    public function testUpdateTrackNotFound(): void
    {
        $updateData = [
            'title' => 'Updated Title'
        ];

        $this->client->request(
            'PUT',
            '/api/tracks/999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Track not found', $responseData['error']);
    }

    public function testUpdateTrackWithValidationErrors(): void
    {
        // Create a track to update
        $track = new Track();
        $track->setTitle('Original Title')
              ->setArtist('Original Artist')
              ->setDuration(180);
        
        $this->trackRepository->save($track, true);
        $trackId = $track->getId();

        $invalidUpdateData = [
            'title' => '',
            'duration' => -1
        ];

        $this->client->request(
            'PUT',
            "/api/tracks/{$trackId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidUpdateData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Validation failed', $responseData['error']);
        $this->assertArrayHasKey('details', $responseData);
        $this->assertIsArray($responseData['details']);
    }

    public function testUpdateTrackWithInvalidJson(): void
    {
        $this->client->request(
            'PUT',
            '/api/tracks/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid JSON data', $responseData['error']);
    }

    public function testIsrcValidation(): void
    {
        // Test valid ISRC
        $validTrackData = [
            'title' => 'Valid ISRC Track',
            'artist' => 'Valid Artist',
            'duration' => 180,
            'isrc' => 'GB-VAL-24-12345'
        ];

        $this->client->request(
            'POST',
            '/api/tracks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($validTrackData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Test invalid ISRC
        $invalidTrackData = [
            'title' => 'Invalid ISRC Track',
            'artist' => 'Invalid Artist',
            'duration' => 180,
            'isrc' => 'INVALID-ISRC-FORMAT'
        ];

        $this->client->request(
            'POST',
            '/api/tracks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidTrackData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Validation failed', $responseData['error']);
        
        // Check that ISRC validation error is present
        $hasIsrcError = false;
        foreach ($responseData['details'] as $error) {
            if (strpos($error, 'ISRC') !== false) {
                $hasIsrcError = true;
                break;
            }
        }
        $this->assertTrue($hasIsrcError, 'ISRC validation error should be present');
    }
} 