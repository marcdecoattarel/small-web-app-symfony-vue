<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TrackRepository;
use Symfony\Component\HttpFoundation\Response;

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
}
