<?php

namespace App\DataFixtures;

use App\Entity\Waiter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class WaiterFixtures extends Fixture
{
    const WAITERS_NUMBER = 2;

    public function load(ObjectManager $manager)
    {
        // creating the waiters seed data
        for ($i = 0; $i < self::WAITERS_NUMBER; $i++) {
            $waiter = new Waiter();
            $waiter->setName('Waiter ' . mt_rand(1, 100));
            $waiter->setAvailable(1);
            $manager->persist($waiter);
        }

        $manager->flush();
    }
}
