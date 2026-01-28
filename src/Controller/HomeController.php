<?php

namespace App\Controller;

use App\Repository\PagesRepository;
use App\Repository\PostsRepository;
use App\Repository\TabsRepository;
use App\Service\HeaderService;
use App\Service\PageLogicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack; // Import nécessaire
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private $pageTitle = 'Accueil | Réseau des Semeurs de Jardins';

    public function __construct(
        private HeaderService $headerService,
        private PostsRepository $postsRepository,
        private TabsRepository $tabsRepository,
        private PagesRepository $pagesRepository,
        private PageLogicService $pageLogicService,
        private RequestStack $requestStack // Inject RequestStack
    ) {}

    #[Route('/{slug?}/{id?}', name: 'app_home', methods: ['GET'])]
    public function index(?string $slug, ?int $id): Response
    {
        // Récupérer la requête courante
        $request = $this->requestStack->getCurrentRequest();

        // Récupérer headerData
        $headerData = ($id !== null)
            ? $this->headerService->getHeaderData($id)
            : $this->headerService->getDefaultHeaderData();

        // Préparer le logo (non modifié ici)
        $assoLogo = [
            'logoUrl' => 'path/to/default/logo.png',
            'link' => $this->generateUrl('app_home'),
        ];
        if (!empty($headerData['assoLogo'])) {
            $assoLogo['logoUrl'] = 'uploads/profiles/SDJ/logo/' . $headerData['assoLogo'];
        }

        // Si page d'accueil
        if ($slug === null && $id === null) {
            return $this->render('home/index.html.twig', [
                'pageTitle' => $this->pageTitle,
                'pages' => [],
                'headerData' => $headerData,
                'backToList' => false,
                'assoLogo' => $assoLogo,
            ]);
        }

        // Si pas de slug mais id, récupérer le slug
        if ($slug === null && $id !== null) {
            $tab = $this->tabsRepository->find($id);
            if ($tab !== null) {
                $slug = $tab->getSlug();
            } else {
                throw $this->createNotFoundException('Tab non trouvé pour l\'id ' . $id);
            }
        }

        // Récupérer la route courante
        $currentRoute = $request->attributes->get('_route');

        // Définir $isHomeRoute
        $isHomeRoute = ($currentRoute === 'app_home');

        // Récupérer les posts
        try {
            $postsQueryBuilder = $this->postsRepository->createQueryBuilder('p')
                ->addSelect('t')
                ->innerJoin('p.tab', 't')
                ->andWhere('t.slug = :slug')
                ->setParameter('slug', $slug)
                ->andWhere('p.status = 1')
                ->orderBy('p.posted_at', 'DESC');
            $posts = $postsQueryBuilder->getQuery()->execute();
        } catch (\Exception $e) {
            $posts = [];
        }

        // Récupérer les pages
        try {
            $pages = $this->pagesRepository->createQueryBuilder('p')
                ->addSelect('t')
                ->leftJoin('p.tabsPage', 't')
                ->andWhere('p.home = 1')
                ->getQuery()
                ->execute();
        } catch (\Exception $e) {
            $pages = [];
        }

        // Récupérer le contexte via le service
        $context = $this->pageLogicService->getPageContext([
            'slug' => $slug,
            'posts' => $posts,
            'pages' => $pages,
            'headerData' => $headerData,
            'order' => null,
            'backToList' => false,
            'currentRoute' => $currentRoute,
            'isHomeRoute' => $isHomeRoute,
        ]);

        // Transmettre le contexte et les autres variables voulues
        return $this->render('home/index.html.twig', array_merge($context, [
            'pageTitle' => $this->pageTitle,
            'isHomeRoute' => $isHomeRoute,
        ]));
    }
}
