<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Spots;

class SpotsFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // for($i = 1; $i <= 10; $i++){
        //     $spot = new Spots();
        //     $spot-> setTitle("titre $i")
        //          -> setPicture("127.0.0.1:8000/images/");

        //     $manager->persist($spot);
        // }

        // $manager->flush();
    }
}
