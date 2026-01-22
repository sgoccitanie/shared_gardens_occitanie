<?php

namespace App\Controller;

use App\Repository\PagesRepository;
use App\Repository\PostsRepository;
use App\Repository\TabsRepository;
use App\Service\HeaderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class HomeController extends AbstractController
{
    private $pageTitle = 'Accueil | Réseau des Semeurs de Jardins';

    public function __construct(
        private HeaderService $headerService,
        private PostsRepository $postsRepository,
        private TabsRepository $tabsRepository,
        private PagesRepository $pagesRepository,
        private SluggerInterface $slugger
    ) {}

    // Ajout du paramètre $id dans la route
    #[Route('/{slug?}/{id?}', name: 'app_home', methods: ['GET'])]
    public function index(?string $slug, ?int $id): Response
    {
        if ($slug === null && $id === null) {
            // Ajouter 'pages' pour éviter l'erreur Twig
            // Initialiser $pages à un tableau vide
            $pages = [];

            return $this->render('home/index.html.twig', [
                'pageTitle' => $this->pageTitle,
                'pages' => $pages,
            ]);
        }

        if ($slug === null) {
            if ($id !== null) {
                // Récupèrer le tab avec id
                $tab = $this->tabsRepository->find($id);
                if ($tab !== null) {
                    $slug = $tab->getSlug();
                } else {
                    throw $this->createNotFoundException('Tab non trouvé pour l\'id ' . $id);
                }
            } else {
                throw $this->createNotFoundException('Slug ou ID requis.');
            }
        }

        $order = 'DESC';

        // get posts
        try {
            $postsQueryBuilder = $this->postsRepository->createQueryBuilder('p')
                ->addSelect('t')
                ->innerJoin('p.tab', 't')
                ->andWhere('t.slug = :slug')
                ->setParameter('slug', $slug)
                ->andWhere('p.status = 1')
                ->orderBy('p.posted_at', $order);
            $posts = $postsQueryBuilder->getQuery()->execute();
        } catch (\Exception $e) {
            $posts = [];
        }

        // get pages
        try {
            $pages = $this->pagesRepository->createQueryBuilder('p')
                ->addSelect('t')
                ->leftJoin('p.tabs_page', 't')
                ->andWhere('p.home = 1')
                ->getQuery()
                ->execute();
        } catch (\Exception $e) {
            $pages = [];
        }

        $select = count($posts) > 0;
        $eventCalendar = ($slug === 'coming');

        return $this->render('home/index.html.twig', [
            'pageTitle' => $this->pageTitle,
            'posts' => $posts,
            'pages' => $pages,
            'slug' => $slug,
            'select' => $select,
            'eventCalendar' => $eventCalendar,
            'order' => $order
        ]);
    }

    #[Route('/{slug?}/order/{order}', name: 'app_home_order', methods: ['POST'])]
    public function order(?string $slug, ?string $order, ?int $id): Response
    {
        if ($slug === null) {
            if ($id !== null) {
                $tab = $this->tabsRepository->find($id);
                if ($tab !== null) {
                    $slug = $tab->getSlug();
                } else {
                    throw $this->createNotFoundException('Tab non trouvé pour l\'id ' . $id);
                }
            } else {
                throw $this->createNotFoundException('Slug ou ID requis.');
            }
        }

        if ($order !== 'ASC' && $order !== 'DESC') {
            $order = 'DESC';
        }

        try {
            $posts = $this->postsRepository->createQueryBuilder('p')
                ->addSelect('t')
                ->innerJoin('p.tab', 't')
                ->andWhere('t.slug = :slug')
                ->setParameter('slug', $slug)
                ->andWhere('p.status = 1')
                ->orderBy('p.posted_at', $order)
                ->getQuery()
                ->execute();
        } catch (\Exception $e) {
            $posts = [];
        }

        $select = true;
        $eventCalendar = false;

        return $this->render('home/postslist.html.twig', [
            'posts' => $posts,
            'slug' => $slug,
            'order' => $order,
            'select' => $select,
            'eventCalendar' => $eventCalendar
        ]);
    }

    #[Route('/{slug}/{id}', name: 'app_home_post', methods: ['GET'])]
    public function post(?string $slug, ?string $id): Response
    {
        try {
            $post = $this->postsRepository->find($id);
        } catch (\Exception $e) {
            $post = null; // Corrigé pour renvoyer null si pas trouvé
        }
        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé');
        }

        $select = false;
        $eventCalendar = false;
        $backToList = true;

        try {
            $pages = $this->pagesRepository->createQueryBuilder('p')
                ->addSelect('t')
                ->leftJoin('p.tabs_page', 't')
                ->andWhere('p.home = 1')
                ->getQuery()
                ->execute();
        } catch (\Exception $e) {
            $pages = [];
        }

        return $this->render('home/index.html.twig', [
            'pageTitle' => $this->pageTitle,
            'posts' => $post,
            'pages' => $pages,
            'slug' => $slug,
            'select' => $select,
            'eventCalendar' => $eventCalendar,
            'backToList' => $backToList
        ]);
    }
}
