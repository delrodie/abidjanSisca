<?php

namespace App\Services;

use App\Repository\UtilisateurRepository;

class GenerateCompte
{
    private array $forbiddenWords = [
        'chapelle',
        'paroisse',
        'quasi-paroisse',
        'quasi paroisse',
        'eglise',
        'église',
        'communauté',
        'communaute',
        'groupe',
        'quasi'
    ];

    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository
    )
    {
    }

    /**
     * 1. Retirer le contenu entre parenthèses
     * 2. Retirer accents
     * 3. Mettre en minuscules pour faciliter le nettoyage
     * 4. Retirer les mots interdits
     * 5. Retirer tout ce qui n’est pas lettre ou chiffre
     * 6. Nettoyer les espaces résiduels
     * 7. Gérer les doublons
     *
     * @param string $name
     * @return string
     */
    public function generate(string $name): string
    {

        $name = preg_replace('/\s*\(.*?\)/', '', $name);
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        $name = strtolower($name);

        foreach ($this->forbiddenWords as $word) {
            $name = str_replace($word, '', $name);
        }

        $name = preg_replace('/[^a-z0-9]/', '', $name);
        $name = trim($name);

        return $this->makeUnique($name);
    }

    private function makeUnique(string $name): string
    {
        $username = $name;
        $counter = 1;

        while ($this->utilisateurRepository->findOneBy(['username' => $username])) {
            $counter++;
            $username = $name . $counter;
        }

        return $username;
    }

    public function plainPassword(): string
    {
        $prefix = 'Requin';
        $nombre_aleatoire = random_int(1000,9999);
        return $prefix.$nombre_aleatoire;
    }
}
