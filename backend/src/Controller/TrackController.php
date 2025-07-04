<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TrackRepository;
use App\Entity\Track;
use App\Service\TrackService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tracks')]
final class TrackController extends AbstractController
{

    #[Route('', name: 'tracks_list', methods: ['GET'])]
    public function index(TrackService $trackService): JsonResponse
    {
        $tracks = $trackService->getAllTracks();
        $data = $trackService->tracksToArray($tracks);
        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('', name: 'tracks_create', methods: ['POST'])]
    public function create(Request $request, TrackService $trackService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $track = $trackService->createTrack($data);
            return $this->json($track, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            $errorData = json_decode($e->getMessage(), true);
            return $this->json(['error' => 'Validation failed', 'details' => $errorData['errors']], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'tracks_update', methods: ['PUT'])]
    public function update(int $id, Request $request, TrackService $trackService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $track = $trackService->updateTrack($id, $data);
            return $this->json($track, Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            if ($e->getMessage() === 'Track not found') {
                return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
            }
            $errorData = json_decode($e->getMessage(), true);
            return $this->json(['error' => 'Validation failed', 'details' => $errorData['errors']], Response::HTTP_BAD_REQUEST);
        }
    }
}
