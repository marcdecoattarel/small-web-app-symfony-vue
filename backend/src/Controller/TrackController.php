<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TrackRepository;
use App\Entity\Track;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tracks')]
final class TrackController extends AbstractController
{

    #[Route('', name: 'tracks_list', methods: ['GET'])]
    public function index(TrackRepository $trackRepository): JsonResponse
    {
        $tracks = $trackRepository->findAll();

        // Transform the tracks into a JSON response can be done with a serializer
        $data = array_map(function ($track) {
            return [
                'id' => $track->getId(),
                'title' => $track->getTitle(),
                'artist' => $track->getArtist(),
                'duration' => $track->getDuration(),
                'isrc' => $track->getIsrc(),
            ];
        }, $tracks);
        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('', name: 'tracks_create', methods: ['POST'])]
    public function create(Request $request, TrackRepository $trackRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $track = new Track();
        $track->setTitle($data['title'] ?? '');
        $track->setArtist($data['artist'] ?? '');
        $track->setDuration($data['duration'] ?? 0);
        $track->setIsrc($data['isrc'] ?? null);

        // Validate the entity
        $errors = $validator->validate($track);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Save to database
        $trackRepository->save($track, true);

        return $this->json([
            'id' => $track->getId(),
            'title' => $track->getTitle(),
            'artist' => $track->getArtist(),
            'duration' => $track->getDuration(),
            'isrc' => $track->getIsrc(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'tracks_update', methods: ['PUT'])]
    public function update(int $id, Request $request, TrackRepository $trackRepository, ValidatorInterface $validator): JsonResponse
    {
        $track = $trackRepository->find($id);
        
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
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

        // Validate the updated entity
        $errors = $validator->validate($track);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['error' => 'Validation failed', 'details' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Save to database
        $trackRepository->save($track, true);

        return $this->json([
            'id' => $track->getId(),
            'title' => $track->getTitle(),
            'artist' => $track->getArtist(),
            'duration' => $track->getDuration(),
            'isrc' => $track->getIsrc(),
        ], Response::HTTP_OK);
    }
}
