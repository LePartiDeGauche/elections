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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResultatController extends Controller
{

    private $nuancess = array(
        array('EXG', 'LEXG'),
        array('FG', 'LCOP', 'LCOM' ,'LPG', 'LFG'),
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
     *     "/circo-europeenne/{code}/{nom}",
     *     name="resultat_circo_europeenne"
     * )
     */
    public function circoEuropeenneAction(Request $request, $code)
    {
        $circo = $this
            ->get('repository.territoire')
            ->getCirconscriptionEuropeenne($code)
        ;

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($circo)
        );
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $results = $this->getResults($circo);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array('resultats' => $results),
            $response
        );
    }

    /**
     * @Route(
     *     "/commune/{departement}/{code}/{nom}",
     *     name="resultat_commune"
     * )
     */
    public function communeAction(Request $request, $departement, $code)
    {
        $commune = $this
            ->get('repository.territoire')
            ->getCommune($departement, $code)
        ;

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($commune)
        );
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $results = $this->getResults($commune);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array('resultats' => $results),
            $response
        );
    }

    /**
     * @Route(
     *     "/departement/{code}/{nom}",
     *     name="resultat_departement"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig")
     */
    public function departementAction(Request $request, $code)
    {
        $departement = $this
            ->get('repository.territoire')
            ->getDepartement($code)
        ;

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($departement)
        );
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $results = $this->getResults($departement);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array('resultats' => $results),
            $response
        );
    }

    /**
     * @Route(
     *     "/region/{code}/{nom}",
     *     name="resultat_region"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig")
     */
    public function regionAction(Request $request, $code)
    {
        $region = $this
            ->get('repository.territoire')
            ->getRegion($code)
        ;

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($region)
        );
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $results = $this->getResults($region);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array('resultats' => $results),
            $response
        );
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
