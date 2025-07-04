<?php

namespace App\Service;

use App\Entity\Track;
use App\Repository\TrackRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TrackService
{
    public function __construct(
        private TrackRepository $trackRepository,
        private ValidatorInterface $validator
    ) {}

    /**
     * Get all tracks
     */
    public function getAllTracks(): array
    {
        return $this->trackRepository->findAll();
    }

    /**
     * Create a new track
     */
    public function createTrack(array $data): array
    {
        $track = new Track();
        $track->setTitle($data['title'] ?? '');
        $track->setArtist($data['artist'] ?? '');
        $track->setDuration($data['duration'] ?? 0);
        $track->setIsrc($data['isrc'] ?? null);

        $errors = $this->validateTrack($track);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode(['errors' => $errors]));
        }

        $this->trackRepository->save($track, true);

        return $this->trackToArray($track);
    }

    /**
     * Update an existing track
     */
    public function updateTrack(int $id, array $data): array
    {
        $track = $this->trackRepository->find($id);
        if (!$track) {
            throw new \InvalidArgumentException('Track not found');
        }

        // Only update fields that are present in the request
        if (array_key_exists('title', $data)) {
            $track->setTitle($data['title']);
        }
        if (array_key_exists('artist', $data)) {
            $track->setArtist($data['artist']);
        }
        if (array_key_exists('duration', $data)) {
            $track->setDuration($data['duration']);
        }
        if (array_key_exists('isrc', $data)) {
            $track->setIsrc($data['isrc']);
        }

        $errors = $this->validateTrack($track);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode(['errors' => $errors]));
        }

        $this->trackRepository->save($track, true);

        return $this->trackToArray($track);
    }

    /**
     * Get a track by ID
     */
    public function getTrack(int $id): ?Track
    {
        return $this->trackRepository->find($id);
    }

    /**
     * Validate a track entity
     */
    private function validateTrack(Track $track): array
    {
        $errors = $this->validator->validate($track);
        $errorMessages = [];
        
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $errorMessages;
    }

    /**
     * Convert track entity to array
     */
    private function trackToArray(Track $track): array
    {
        return [
            'id' => $track->getId(),
            'title' => $track->getTitle(),
            'artist' => $track->getArtist(),
            'duration' => $track->getDuration(),
            'isrc' => $track->getIsrc(),
        ];
    }

    /**
     * Convert multiple tracks to array
     */
    public function tracksToArray(array $tracks): array
    {
        return array_map(fn($track) => $this->trackToArray($track), $tracks);
    }
} 