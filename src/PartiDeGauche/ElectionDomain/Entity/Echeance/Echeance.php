<?php
/*
 * This file is part of the Parti de Gauche elections data project.
 *
 * The Parti de Gauche elections data project is free software: you can
 * redistribute it and/or modify it under the terms of the GNU Affero General
 * Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * The Parti de Gauche elections data project is distributed in the hope
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with the Parti de Gauche elections data project.
 * If not, see <http://www.gnu.org/licenses/>.
 */

namespace PartiDeGauche\ElectionDomain\Entity\Echeance;

class Echeance
{

    const PREMIER_TOUR = false;
    const SECOND_TOUR = true;
    const MUNICIPALES = 1;
    const CANTONALES = 2;
    const REGIONALES = 3;
    const LEGISLATIVES = 4;
    const PRESIDENTIELLE = 5;
    const EUROPEENNES = 6;

    /**
     * @var integer
     */
    private $id;

    /**
     * La date de l'échéance.
     * @var DateTime
     */
    private $date;

    /**
     * Le type de l'échéance (législative, présidentielle, etc)
     * @var integer
     */
    private $type;

    /**
     * Est-ce que l'échance est un second tour.
     * @var boolean
     */
    private $secondTour;

    /**
     * Constructeur d'objet Echeance.
     * @param DateTime $date       La date de l'échance.
     * @param string   $type       Le type de l'échéance.
     * @param boolean  $secondTour Si l'échéance est un second tour.
     */
    public function __construct(
        \DateTime $date,
        $type,
        $secondTour = self::PREMIER_TOUR
    ) {
        \Assert\that((integer) $type)
            ->inArray(array(
                self::MUNICIPALES,
                self::CANTONALES,
                self::REGIONALES,
                self::LEGISLATIVES,
                self::PRESIDENTIELLE,
                self::EUROPEENNES,
            ))
        ;
        \Assert\that($secondTour)
            ->boolean()
        ;

        $this->date = $date;
        $this->type = (integer) $type;
        $this->secondTour = (boolean) $secondTour;
    }

    /**
     * Récupérer la date de l'échéance.
     * @return DateTime La date de l'échéance.
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Récupérer le nom de l'échéance.
     * @return string Le nom de l'échéance.
     */
    public function getNom()
    {
        switch ($this->type) {
            case self::MUNICIPALES:
                $echeance = 'Municipales';
                break;
            case self::CANTONALES:
                $echeance = 'Cantonales';
                break;
            case self::REGIONALES:
                $echeance = 'Regionales';
                break;
            case self::LEGISLATIVES:
                $echeance = 'Législatives';
                break;
            case self::PRESIDENTIELLE:
                $echeance = 'Présidentielle';
                break;
            case self::EUROPEENNES:
                $echeance = 'Européennes';
        }

        return $echeance . ' ' .$this->date->format('Y');
    }

    /**
     * Récupérer le type de l'élection.
     * @return int Le type de l'élection.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Savoir si l'échéance est un secon tour.
     * @return boolean Vrai s'il l'échéance est un second tour.
     */
    public function isSecondTour()
    {
        return $this->secondTour;
    }

    /**
     * Afficher l'échéance sous forme de chaîne de caractères.
     * @return string [description]
     */
    public function __toString()
    {
        return $this->getNom();
    }
}
