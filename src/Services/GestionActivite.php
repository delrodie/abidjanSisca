<?php

namespace App\Services;

use App\Enum\InstanceType;
use App\Repository\ActiviteRepository;

readonly class GestionActivite
{
    public function __construct(
        private ActiviteRepository $activiteRepository
    )
    {
    }

    /**
     * Generation des references des activités
     *
     * @param $instanceType
     * @return string
     */
    public function generateReference($instanceType): string
    {
        $racine = $this->prefix($instanceType).$this->annee();
        do{
            $aleatoire_numero = random_int(1000,9999);
            $reference = $racine.'-'.$aleatoire_numero;
        }while ($this->activiteRepository->findOneBy(['reference' => $reference]));

        return $reference;
    }

    protected function prefix($instanceType): string
    {
        return match ($instanceType){
            InstanceType::DISTRICT => 'ABJ-D-',
            InstanceType::GROUPE => 'ABJ-G-',
            default => 'ABJ-R-'
        };
    }

    public function annee(): string
    {
        $anneeEncours = (int) Date('y');
        $moisEncours = (int) Date('m');

        $debutAnnee = $moisEncours > 9 ? $anneeEncours : $anneeEncours - 1;
        $finAnnee = $moisEncours > 9 ? $anneeEncours + 1 : $anneeEncours;

        return sprintf('%d%d', $debutAnnee, $finAnnee);
    }
}
