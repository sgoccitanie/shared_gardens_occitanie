<?php

namespace App\Service;

use App\Repository\AssociationRepository;

class HeaderService
{
    public function __construct(
        private TextAnalyzerService $textAnalyzerService,
        private AssociationRepository $assoRepo
    ) {}

    /**
     * Récupèrer les données de l'association en fonction de l'ID fourni
     */
    public function getHeaderData(int $id): array
    {
        $asso = $this->assoRepo->findOneBy(['id' => $id]);

        if (!$asso) {
            return $this->getDefaultHeaderData();
        }

        // Récupérer et décoder chaque champ
        $assoMantra = $this->decodeOrDefault($asso->getMantra(), ["Choisir un mantra"]);
        $assoBanner = $this->decodeOrDefault($asso->getBanner(), null);
        $assoLogo = $this->decodeOrDefault($asso->getLogo(), null);
        $assoAddress = $this->decodeOrDefault($asso->getAddress(), null);
        $assoDescription = $this->decodeOrDefault($asso->getDescription(), null);
        $assoEmail = $this->decodeOrDefault($asso->getEmail(), null);
        $assoPhone = $this->decodeOrDefault($asso->getMobile(), null);
        $assoLinks = $this->decodeOrDefaultLinks($asso->getLinks());

        // Traiter le mantra
        if ($this->textAnalyzerService->getWordCount($assoMantra) > 4 && !str_contains($assoMantra, ",")) {
            $assoMantra = $this->textAnalyzerService->splitTextByWordCount($assoMantra, 4);
        } else {
            $assoMantra = $this->textAnalyzerService->splitAtComma($assoMantra);
        }

        return [
            'assoMantra' => $assoMantra,
            'assoBanner' => $assoBanner,
            'assoLogo' => $assoLogo,
            'assoAddress' => $assoAddress,
            'assoDescription' => $assoDescription,
            'assoEmail' => $assoEmail,
            'assoPhone' => $assoPhone,
            'assoLinks' => $assoLinks,
        ];
    }

    /**
     * Retourner les données par défaut pour l'en-tête
     */
    public function getDefaultHeaderData(): array
    {
        return [
            'assoMantra' => ["Choisir un mantra"],
            'assoBanner' => null,
            'assoLogo' => null,
            'assoAddress' => null,
            'assoDescription' => null,
            'assoEmail' => null,
            'assoPhone' => null,
            'assoLinks' => null,
        ];
    }

    private function decodeOrDefault($value, $default)
    {
        if (empty($value)) {
            return $default;
        }
        return Utils::decode($value);
    }

    private function decodeOrDefaultLinks($links)
    {
        if (!$links) {
            return []; // Retourner un tableau vide au lieu de null
        }
        $linksArray = $links->toArray();
        $decodedLinks = [];
        foreach ($linksArray as $link) {
            $decodedLinks[] = Utils::decode($link->getUrl());
        }
        return $decodedLinks;
    }
}
