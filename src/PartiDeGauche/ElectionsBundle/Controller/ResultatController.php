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
use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\VO\VoteInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
    public function circoEuropeenneAction(Request $request, $code, $nom)
    {
        $circo = $this
            ->get('repository.territoire')
            ->getCirconscriptionEuropeenne($code)
        ;

        if ($this->get('cocur_slugify')->slugify($circo->getNom()) !== $nom) {
            throw $this->createNotFoundException('Circonscription inconnue.');
        }

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
            array('resultats' => $results, 'territoire' => $circo->getNom()),
            $response
        );
    }

    /**
     * @Route(
     *     "/commune/{departement}/{code}/{nom}",
     *     name="resultat_commune"
     * )
     */
    public function communeAction(Request $request, $departement, $code, $nom)
    {
        $commune = $this
            ->get('repository.territoire')
            ->getCommune($departement, $code)
        ;

        if ($this->get('cocur_slugify')->slugify($commune->getNom()) !== $nom) {
            throw $this->createNotFoundException('Commune inconnue.');
        }

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
            array('resultats' => $results, 'territoire' => $commune->getNom()),
            $response
        );
    }

    /**
     * @Route(
     *     "/departement/{code}/{nom}",
     *     name="resultat_departement"
     * )
     */
    public function departementAction(Request $request, $code, $nom)
    {
        $departement = $this
            ->get('repository.territoire')
            ->getDepartement($code)
        ;

        $dNom = $this->get('cocur_slugify')->slugify($departement->getNom());
        if ($dNom !== $nom) {
            throw $this->createNotFoundException('Département inconnu.');
        }

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
            array(
                'resultats' => $results,
                'territoire' => $departement->getNom()
            ),
            $response
        );
    }

    /**
     * @Route(
     *     "/france",
     *     name="resultat_france"
     * )
     */
    public function paysAction(Request $request)
    {
        $pays = $this
            ->get('repository.territoire')
            ->getPays()
        ;

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($pays)
        );
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $results = $this->getResults($pays);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array('resultats' => $results, 'territoire' => $pays->getNom()),
            $response
        );
    }

    /**
     * @Route(
     *     "/region/{code}/{nom}",
     *     name="resultat_region"
     * )
     */
    public function regionAction(Request $request, $code, $nom)
    {
        $region = $this
            ->get('repository.territoire')
            ->getRegion($code)
        ;

        if ($this->get('cocur_slugify')->slugify($region->getNom()) !== $nom) {
            throw $this->createNotFoundException('Région inconnue.');
        }

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
            array('resultats' => $results, 'territoire' => $region->getNom()),
            $response
        );
    }

    private function getResults($territoire)
    {
        $result = array();
        foreach ($this->get('repository.echeance')->getAll() as $echeance) {
            $result[$echeance->getNom()] = array();

            $election = $this
                ->get('repository.election')
                ->get($echeance, $territoire)
            ;

            if (
                $election
                && $echeance->getType() === Echeance::EUROPEENNES
                && $election->getCirconscription() === $territoire
            ) {
                $this->fakeCompletedResult($echeance, $territoire, $election);
            }

            $voteInfo = $this
                ->get('repository.election')
                ->getVoteInfo($echeance, $territoire)
            ;
            $result[$echeance->getNom()]['inscrits'] = $voteInfo->getInscrits();
            $result[$echeance->getNom()]['votants'] = $voteInfo->getVotants();
            $result[$echeance->getNom()]['exprimes'] = $voteInfo->getExprimes();

            foreach ($this->nuancess as $nuances) {
                $spec = new CandidatNuanceSpecification($nuances);
                $score =$this
                    ->get('repository.election')
                    ->getScore(
                        $echeance,
                        $territoire,
                        $spec
                    )
                ;

                $candidats = array();
                $sieges = 0;
                if ($election) {
                    $candidats = array_filter(
                        $election->getCandidats(),
                        array($spec, 'isSatisfiedBy')
                    );

                    if ($election->getCirconscription() == $territoire) {
                        foreach ($candidats as $candidat) {
                            $sieges += $election->getSiegesCandidat($candidat);
                        }
                    }
                }

                $result[$echeance->getNom()][$nuances[0]] = array();
                $result[$echeance->getNom()][$nuances[0]]['score'] = $score;
                $result[$echeance->getNom()][$nuances[0]]['sieges'] = $sieges;
                $result[$echeance->getNom()][$nuances[0]]['candidats'] =
                    $candidats;
            }
        }

        return $result;
    }

    private function fakeCompletedResult($echeance, $territoire, $election)
    {
        foreach ($election->getCandidats() as $candidat) {
            $voix = $this
                ->get('repository.election')
                ->getScore(
                    $echeance,
                    $territoire,
                    $candidat
                )->toVoix()
            ;
            $election->setVoixCandidat(
                $voix,
                $candidat
            );
        }
    }
}
