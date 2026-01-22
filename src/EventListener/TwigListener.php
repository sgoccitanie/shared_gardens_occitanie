<?php

namespace App\EventListener;

use App\Repository\AssociationRepository;
use App\Service\HeaderService;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\TwigFunction;

class TwigListener
{
    public function __construct(private HeaderService $headerService, private AssociationRepository $assoRepo) {}

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();

        // Récupérer l'ID de l'association depuis la requête
        $associationId = $request->attributes->get('id');

        // Vérifier que l'ID est présent
        if ($associationId !== null) {
            $headerData = $this->headerService->getHeaderData($this->assoRepo, (int)$associationId);
            $request->attributes->set('header_data', $headerData);
        } else {
            // Si l'ID n'est pas fourni
            $request->attributes->set('header_data', []);
        }
    }
}
