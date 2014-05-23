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

namespace PartiDeGauche\ElectionsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CandidatScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
                'disabled' => true,
                'read_only' => true
            ))
            ->add('nuance', 'choice', array(
                'choices' => array(
                    'EXG',
                    'FG',
                    'VEC',
                    'SOC',
                    'DVG',
                    'CEN',
                    'UMP',
                    'DVD',
                    'FN',
                    'EXD',
                    'AUT'
                ),
                'expanded' => false,
                'multiple' => false
            ))
            ->add('voix', 'integer', array('required' => false))
        ;
    }

    public function getName()
    {
        return 'candidat';
    }
}
