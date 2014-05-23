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

use PartiDeGauche\ElectionDomain\Entity\Echeance\Echeance;
use PartiDeGauche\ElectionDomain\VO\VoteInfo;
use PartiDeGauche\ElectionsBundle\Form\Type\ElectionScoreType;
use PartiDeGauche\TerritoireDomain\Entity\Territoire\AbstractTerritoire;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_ADMIN')")
 */
class EditionController extends Controller
{
    /**
     * @Route(
     *     "/circo-europeenne/{code}/{nom}/edit/{echeance}",
     *     name="edit_resultat_circo_europeenne"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Edition:edit.html.twig")
     */
    public function circoEuropeenneAction(
        Request $request,
        $code,
        $nom,
        $echeance
    ) {
        $circo = $this
            ->get('repository.territoire')
            ->getCirconscriptionEuropeenne($code)
        ;

        if ($this->get('cocur_slugify')->slugify($circo->getNom()) !== $nom) {
            throw $this->createNotFoundException('Circonscription inconnue.');
        }

        $echeance = $this->getEcheance($echeance);
        $form = $this->createAndHandleForm($request, $circo, $echeance);

        if (!$form) {
            return $this->redirect(
                $this->generateUrl(
                    'resultat_circo_europeenne',
                    array('code' => $code, 'nom' => $nom)
                )
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/commune/{departement}/{code}/{nom}/edit/{echeance}",
     *     name="edit_resultat_commune"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Edition:edit.html.twig")
     */
    public function communeAction(
        Request $request,
        $departement,
        $code,
        $nom,
        $echeance
    ) {
        $commune = $this
            ->get('repository.territoire')
            ->getCommune($departement, $code)
        ;

        if ($this->get('cocur_slugify')->slugify($commune->getNom()) !== $nom) {
            throw $this->createNotFoundException('Commune inconnue.');
        }

        $echeance = $this->getEcheance($echeance);
        $form = $this->createAndHandleForm($request, $commune, $echeance);

        if (!$form) {
            return $this->redirect(
                $this->generateUrl(
                    'resultat_commune',
                    array(
                        'departement' => $departement,
                        'code' => $code,
                        'nom' => $nom
                    )
                )
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/departement/{code}/{nom}/edit/{echeance}",
     *     name="edit_resultat_departement"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Edition:edit.html.twig")
     */
    public function departementAction(
        Request $request,
        $code,
        $nom,
        $echeance
    ) {
        $departement = $this
            ->get('repository.territoire')
            ->getDepartement($code)
        ;

        $dNom = $this->get('cocur_slugify')->slugify($departement->getNom());
        if ($dNom !== $nom) {
            throw $this->createNotFoundException('Département inconnu.');
        }

        $echeance = $this->getEcheance($echeance);
        $form = $this->createAndHandleForm($request, $departement, $echeance);

        if (!$form) {
            return $this->redirect(
                $this->generateUrl(
                    'resultat_departement',
                    array('code' => $code, 'nom' => $nom)
                )
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/france/edit/{echeance}",
     *     name="edit_resultat_france"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Edition:edit.html.twig")
     */
    public function paysAction(Request $request)
    {
        $pays = $this
            ->get('repository.territoire')
            ->getPays()
        ;

        $echeance = $this->getEcheance($echeance);
        $form = $this->createAndHandleForm($request, $pays, $echeance);

        if (!$form) {
            return $this->redirect($this->generateUrl('resultat_france'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/region/{code}/{nom}/edit/{echeance}",
     *     name="edit_resultat_region"
     * )
     * @Template("PartiDeGaucheElectionsBundle:Edition:edit.html.twig")
     */
    public function regionAction(
        Request $request,
        $code,
        $nom,
        $echeance
    ) {
        $region = $this
            ->get('repository.territoire')
            ->getRegion($code)
        ;

        if ($this->get('cocur_slugify')->slugify($region->getNom()) !== $nom) {
            throw $this->createNotFoundException('Région inconnue.');
        }

        $echeance = $this->getEcheance($echeance);
        $form = $this->createAndHandleForm($request, $region, $echeance);

        if (!$form) {
            return $this->redirect(
                $this->generateUrl(
                    'resultat_region',
                    array('code' => $code, 'nom' => $nom)
                )
            );
        }

        return array('form' => $form->createView());
    }

    private function createAndHandleForm(
        Request $request,
        AbstractTerritoire $territoire,
        Echeance $echeance
    ) {
        $election = $this
            ->get('repository.election')
            ->get($echeance, $territoire)
        ;

        if (!$election) {
            return false;
        }

        $voteInfo = $election->getVoteInfo($territoire);
        $inscrits = $voteInfo ? $voteInfo->getInscrits() : 0;
        $votants = $voteInfo ? $voteInfo->getVotants() : 0;
        $exprimes = $voteInfo ? $voteInfo->getExprimes() : 0;

        $candidats = $election->getCandidats();

        foreach ($candidats as $key => $candidat) {
            $score = $election->getScoreCandidat($candidat, $territoire);
            $voix = $score ? $score->toVoix() : 0;

            $candidats[$key] = array(
                'nom' => $candidat->getNom(),
                'nuance' => $candidat->getNuance(),
                'voix' => $voix
            );
        }

        $form = $this->createForm(
            new ElectionScoreType(),
            array(
                'inscrits' => $inscrits,
                'votants' => $votants,
                'exprimes' => $exprimes,
                'candidats' => $candidats
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $election->setVoteInfo(
                new VoteInfo(
                    $data['inscrits'],
                    $data['votants'],
                    $data['exprimes']
                ),
                $territoire
            );

            $candidats = $election->getCandidats();
            foreach ($data['candidats'] as $key => $candidat) {
                $election->setVoixCandidat(
                    $candidat['voix'],
                    $candidats[$key],
                    $territoire
                );
            }

            $this->get('repository.election')->save();

            return false;
        }

        return $form;
    }

    private function getEcheance($echeanceSlug)
    {
        $echeances = $this
            ->get('repository.echeance')
            ->getAll()
        ;

        foreach ($echeances as $echeance) {
            $slug = $this
                ->get('cocur_slugify')
                ->slugify($echeance->getNom())
            ;

            if ($slug === $echeanceSlug) {
                return $echeance;
            }
        }
    }
}
