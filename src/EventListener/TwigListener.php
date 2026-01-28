<?php

namespace App\EventListener;

use App\Service\HeaderService;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class TwigListener
{
    public function __construct(
        private HeaderService $headerService
    ) {}

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();

        // Récupérer l'ID depuis l'URL
        $id = $request->attributes->get('id');

        if ($id === null) {
            // Récupérer l'association avec le plus petit ID
            $headerData = $this->headerService->getDefaultHeaderData();
        } else {
            $headerData = $this->headerService->getHeaderData((int)$id);
        }

        $request->attributes->set('header_data', $headerData);
    }
}
