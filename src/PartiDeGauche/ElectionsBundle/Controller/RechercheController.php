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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RechercheController extends Controller
{
    /**
     * @Route("/rechercher/{terme}", name="rechercher")
     * @Method({"GET"})
     * @Template()
     */
    public function rechercherAction($terme = null)
    {
        $territoires = array();
        if (!empty($terme)) {
            $territoires = $this
                ->get('repository.territoire')
                ->findLike($terme, 90);
        }

        $form = $this->createFormBuilder(array())
            ->setAction($this->generateUrl('rechercher_post'))
            ->add('terme', 'text', array('label' => 'Nom du territoire : '))
            ->add('Rechercher', 'submit')
            ->getForm();

        return array(
            'form' => $form->createView(),
            'territoires' => $territoires
        );
    }

    /**
     * @Route("/rechercher", name="rechercher_post")
     * @Method({"POST"})
     */
    public function rechercherPostAction(Request $request)
    {
        $form = $this->createFormBuilder(array())
            ->setAction($this->generateUrl('rechercher_post'))
            ->add('terme', 'text', array('label' => 'Nom du territoire : '))
            ->add('Rechercher', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->redirect(
                $this->generateUrl(
                    'rechercher',
                    array('terme' => $form['terme']->getData())
                )
            );
        }

        return $this->redirect($this->generateUrl('rechercher'));
    }
}
