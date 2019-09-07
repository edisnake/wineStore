<?php

namespace App\DataFixtures;

use App\Entity\Sommelier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SommelierFixtures extends Fixture
{
    const SOMMELIERS_NUMBER = 1;

    public function load(ObjectManager $manager)
    {
        // creating the Sommeliers seed data
        for ($i = 0; $i < self::SOMMELIERS_NUMBER; $i++) {
            $waiter = new Sommelier();
            $waiter->setName('Sommelier ' . mt_rand(1, 100));
            $waiter->setAvailable(1);
            $manager->persist($waiter);
        }

        $manager->flush();
    }
}
