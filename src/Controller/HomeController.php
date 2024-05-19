<?php

namespace App\Controller;

use App\Form\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $characters = null; // Initialisation de la variable characters à null

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchTerm = $form->get('search')->getData();

            $client = HttpClient::create();
            $response = $client->request('GET', 'https://api.disneyapi.dev/character', [
                'query' => [
                    'name' => $searchTerm,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $characters = $response->toArray();

                foreach ($characters['data'] as $key => $character) {
                    if (!isset($character['_id'])) {
                        unset($characters['data'][$key]);
                    }
                }

                if (!empty($characters)) {
                    return $this->render('characters.html.twig', [
                        'characters' => $characters,
                        'form' => $form->createView(),
                    ]);
                } else {
                    return $this->render('error.html.twig', [
                        'message' => 'Aucun personnage trouvé pour le nom "' . $searchTerm . '".',
                    ]);
                }
            } else {
                return $this->render('error.html.twig', [
                    'message' => 'Erreur lors de la récupération des données des personnages depuis l\'API.',
                ]);
            }
        }

        $client = HttpClient::create();
        $response = $client->request('GET', 'https://api.disneyapi.dev/character');

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            $allCharacters = $response->toArray();

            foreach ($allCharacters['data'] as $key => $character) {
                if (!isset($character['_id'])) {
                    unset($allCharacters['data'][$key]);
                }
            }
           // dd($allCharacters);
            return $this->render('base.html.twig', [
                'allCharacters' => $allCharacters,
                'form' => $form->createView(),
            ]);
        } else {
            return $this->render('error.html.twig', [
                'message' => 'Erreur lors de la récupération des données de tous les personnages depuis l\'API.',
            ]);
        }
    }


    #[Route('/character/{id}', name: 'character_details')]
    public function characterDetails($id): Response
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://api.disneyapi.dev/characters/' . $id);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            $character = $response->toArray();

            $films = [];
            if (isset($character['data']['films'])) {
                $films = $character['data']['films'];
            }

            $tvShows = [];
            if (isset($character['data']['tvShows'])) {
                $tvShows = $character['data']['tvShows'];
            }

            return $this->render('character_details.html.twig', [
                'character' => $character,
                'films' => $films,
                'tvShows' => $tvShows,
            ]);
        } else {
            return $this->render('error.html.twig', [
                'message' => 'Erreur lors de la récupération des données du personnage depuis l\'API.',
            ]);
        }
    }


}

