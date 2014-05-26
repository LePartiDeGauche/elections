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
        array('VEC', 'LVEC'),
        array('SOC', 'LSOC', 'LUG'),
        array('DVG', 'LDVG'),
        array('CEN', 'LCMD', 'LCM', 'LMDM', 'LUC', 'LUDI', 'NCE'),
        array('UMP', 'LMAJ', 'LUD', 'LUMP'),
        array('DVD', 'LDVD'),
        array('FN', 'LFN'),
        array('EXD', 'LEXD'),
        array('AUT', 'LAUT', 'LDIV'),
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

        if (
            !$circo
            || $this->get('cocur_slugify')->slugify($circo->getNom()) !== $nom
        ) {
            throw $this->createNotFoundException('Circonscription inconnue.');
        }

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($circo)
        );
        $response->setPublic();
        $response->setVary(array('Authorization', 'Cookie', 'X-User-Hash'));
        $user = $this->getUser() ? $this->getUser()->getUsername() : 'Anonymous';
        $response->headers->set('X-User-Hash', md5($user));

        if ($this->get('kernel')->getEnvironment() == 'prod' && $response->isNotModified($request)) {
            return $response;
        }

        $form = $this->createEcheanceChoiceForm();
        $echeances = $this->getEcheances($form, $request);
        $reference = $echeances['reference'];
        $echeances = $echeances['echeances'];
        $results = $this->getResults($circo, $echeances);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array(
                'resultats' => $results,
                'territoire' => $circo->getNom(),
                'form' => $form->createView(),
                'reference' => $reference
            ),
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

        if (
            !$commune
            || $this->get('cocur_slugify')->slugify($commune->getNom()) !== $nom
        ) {
            throw $this->createNotFoundException('Commune inconnue.');
        }

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($commune)
        );
        $response->setPublic();
        $response->setVary(array('Authorization', 'Cookie', 'X-User-Hash'));
        $user = $this->getUser() ? $this->getUser()->getUsername() : 'Anonymous';
        $response->headers->set('X-User-Hash', md5($user));

        if ($this->get('kernel')->getEnvironment() == 'prod' && $response->isNotModified($request)) {
            return $response;
        }

        $form = $this->createEcheanceChoiceForm();
        $echeances = $this->getEcheances($form, $request);
        $reference = $echeances['reference'];
        $echeances = $echeances['echeances'];
        $results = $this->getResults($commune, $echeances);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array(
                'resultats' => $results,
                'territoire' => $commune->getNom(),
                'form' => $form->createView(),
                'reference' => $reference
            ),
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

        if (
            !$departement
            || $this->get('cocur_slugify')->slugify($departement->getNom()) !== $nom
        ) {
            throw $this->createNotFoundException('Département inconnu.');
        }

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($departement)
        );
        $response->setPublic();
        $response->setVary(array('Authorization', 'Cookie', 'X-User-Hash'));
        $user = $this->getUser() ? $this->getUser()->getUsername() : 'Anonymous';
        $response->headers->set('X-User-Hash', md5($user));

        if ($this->get('kernel')->getEnvironment() == 'prod' && $response->isNotModified($request)) {
            return $response;
        }

        $form = $this->createEcheanceChoiceForm();
        $echeances = $this->getEcheances($form, $request);
        $reference = $echeances['reference'];
        $echeances = $echeances['echeances'];
        $results = $this->getResults($departement, $echeances);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array(
                'resultats' => $results,
                'territoire' => $departement->getNom(),
                'form' => $form->createView(),
                'reference' => $reference
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
        $response->setVary(array('Authorization', 'Cookie', 'X-User-Hash'));
        $user = $this->getUser() ? $this->getUser()->getUsername() : 'Anonymous';
        $response->headers->set('X-User-Hash', md5($user));

        if ($this->get('kernel')->getEnvironment() == 'prod' && $response->isNotModified($request)) {
            return $response;
        }

        $form = $this->createEcheanceChoiceForm();
        $echeances = $this->getEcheances($form, $request);
        $reference = $echeances['reference'];
        $echeances = $echeances['echeances'];
        $results = $this->getResults($pays, $echeances);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array(
                'resultats' => $results,
                'territoire' => $pays->getNom(),
                'form' => $form->createView(),
                'reference' => $reference
            ),
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

        if (
            !$region
            || $this->get('cocur_slugify')->slugify($region->getNom()) !== $nom
        ) {
            throw $this->createNotFoundException('Région inconnue.');
        }

        $response = new Response();
        $response->setLastModified(
            $this->get('repository.cache_info')->getLastModified($region)
        );
        $response->setPublic();
        $response->setVary(array('Authorization', 'Cookie', 'X-User-Hash'));
        $user = $this->getUser() ? $this->getUser()->getUsername() : 'Anonymous';
        $response->headers->set('X-User-Hash', md5($user));

        if ($this->get('kernel')->getEnvironment() == 'prod' && $response->isNotModified($request)) {
            return $response;
        }

        $form = $this->createEcheanceChoiceForm();
        $echeances = $this->getEcheances($form, $request);
        $reference = $echeances['reference'];
        $echeances = $echeances['echeances'];
        $results = $this->getResults($region, $echeances);

        return $this->render(
            'PartiDeGaucheElectionsBundle:Resultat:tableau.html.twig',
            array(
                'resultats' => $results,
                'territoire' => $region->getNom(),
                'form' => $form->createView(),
                'reference' => $reference
            ),
            $response
        );
    }

    private function createEcheanceChoiceForm()
    {
        $echeances = $this
            ->get('repository.echeance')
            ->getAll()
        ;

        $form = $this
            ->createFormBuilder(null, array(
                'csrf_protection' => false
            ))
            ->setMethod('GET')
            ->add('echeances', 'entity', array(
                'class' => 'PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance',
                'choices' => $echeances,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Elections'
            ))
            ->add('comparaison', 'entity', array(
                'class' => 'PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance',
                'choices' => $echeances,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'empty_value' => 'Comparer par rapport à la précédente.'
            ))
            ->add('voir', 'submit', array('label' => 'Voir la sélection'))
            ->getForm()
        ;

        return $form;
    }

    private function getEcheances($form, Request $request)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $reference = $data['comparaison'] ?
                $data['comparaison']->getNom() : null
            ;

            if (!in_array($data['comparaison'], $data['echeances']->toArray())) {
                $reference = null;
            }

            return array(
                'echeances' => $data['echeances'],
                'reference' => $reference
            );
        }

        return array('echeances' => array(), 'reference' => null);
    }

    private function getResults($territoire, $echeances = array())
    {
        $result = array();

        if (empty($echeances)) {
            $echeances = $this->get('repository.echeance')->getAll();
        }

        foreach ($echeances as $echeance) {
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
            $result[$echeance->getNom()]['election'] = $election;

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
