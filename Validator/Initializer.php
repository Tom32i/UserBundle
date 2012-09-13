<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tom32i\UserBundle\Validator;

use Symfony\Component\Validator\ObjectInitializerInterface;
use Tom32i\UserBundle\Entity\User;

/**
 * Automatically updates the canonical fields before validation.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class Initializer implements ObjectInitializerInterface
{
    public function initialize($object)
    {
    	if ($object instanceof User) 
    	{
	        $object->updateCanonicalFields();
	    }
    }
}
