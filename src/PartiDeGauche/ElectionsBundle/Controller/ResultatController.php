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

namespace PartiDeGauche\ElectionsBundle\Controller;

use PartiDeGauche\ElectionDomain\Entity\Candidat\Specification\CandidatNuanceSpecification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResultatController extends Controller
{

    private $nuancess = array(
        array('FG', 'LCOP', 'LCOM' ,'LPG', 'LFG'),
        array('EXG', 'LEXG'),
        array('SOC', 'LSOC', 'LUG'),
        array('VEC', 'LVEC'),
        array('AUT', 'LAUT'),
        array('CEN', 'LCMD', 'LCM', 'LMDM', 'LUC', 'LUDI', 'NCE'),
        array('UMP', 'LMAJ', 'LUD'),
        array('DVD', 'LDVD'),
        array('DVG', 'LDVG'),
        array('FN', 'LFN'),
        array('EXD', 'LEXD'),
    );

    /**
     * @Route(
     *     "/commune/{departement}/{code}/{nom}",
     *     name="resultat_commune"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig")
     */
    public function communeAction($departement, $code)
    {
        $commune = $this
            ->get('repository.territoire')
            ->getCommune($departement, $code)
        ;

        $results = $this->getResults($commune);

        return array('resultats' => $results);
    }

    /**
     * @Route(
     *     "/departement/{code}/{nom}",
     *     name="resultat_departement"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig")
     */
    public function departementAction($code)
    {
        $departement = $this
            ->get('repository.territoire')
            ->getDepartement($code)
        ;

        $results = $this->getResults($departement);

        return array('resultats' => $results);
    }

    /**
     * @Route(
     *     "/region/{code}/{nom}",
     *     name="resultat_region"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig")
     */
    public function regionAction($code)
    {
        $region = $this
            ->get('repository.territoire')
            ->getRegion($code)
        ;

        $results = $this->getResults($region);

        return array('resultats' => $results);
    }

    private function getResults($territoire)
    {
        $result = array();
        foreach ($this->get('repository.echeance')->getAll() as $echeance) {
            $result[$echeance->getNom()] = array();
            $voteInfo = $this
                ->get('repository.election')
                ->getVoteInfo($echeance, $territoire)
            ;
            $result[$echeance->getNom()]['inscrits'] = $voteInfo->getInscrits();
            $result[$echeance->getNom()]['votants'] = $voteInfo->getVotants();
            $result[$echeance->getNom()]['exprimes'] = $voteInfo->getExprimes();

            foreach ($this->nuancess as $nuances) {
                $score =
                    $this
                        ->get('repository.election')
                        ->getScore(
                            $echeance,
                            $territoire,
                            new CandidatNuanceSpecification($nuances)
                        )
                    ;
                $result[$echeance->getNom()][$nuances[0]] = $score;
            }
        }

        return $result;
    }
}
