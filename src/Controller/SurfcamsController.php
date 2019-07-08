<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SurfcamsController extends AbstractController
{
    /**
    * @Route("/", name="home")
    */
    public function home()
    {
        return $this->render('surfcams/home.html.twig', [
            'title' => "SURFCAMS",
            'navItems' => ["WEBCAMS", "PRÉVISIONS", "BOUÉES"],
        ]);
    }

    /**
     * @Route("/Webcams", name="Webcams") 
    */
    public function Webcams()
    {
        return $this->render('surfcams/webcams.html.twig', [
            'title' => "SURFCAMS",
            'navItems' => ["WEBCAMS", "PRÉVISIONS", "BOUÉES"],
        ]);
    }

    /**
     * @Route("/Prévisions", name="Previsions") 
    */
    public function Previsions()
    {
        return $this->render('surfcams/previsions.html.twig', [
            'title' => "SURFCAMS",
            'navItems' => ["WEBCAMS", "PRÉVISIONS", "BOUÉES"],
        ]);
    }

    /**
     * @Route("/Bouées", name="Bouées") 
    */
    public function Bouées()
    {
        return $this->render('surfcams/bouées.html.twig', [
            'title' => "SURFCAMS",
            'navItems' => ["WEBCAMS", "PRÉVISIONS", "BOUÉES"],
        ]);
    }
}
