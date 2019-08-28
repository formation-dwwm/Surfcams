<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Spots;

class SurfcamsController extends AbstractController
{
    /**
    * @Route("/", name="home")
    */
    public function home()
    {
        require 'databaseConnect.php';

        if($user->Get_is_authenticated() === FALSE){
            return $this->redirectToRoute('login');
        }

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
        require 'databaseConnect.php';

        if($user->Get_is_authenticated() === FALSE){
            return $this->redirectToRoute('login');
        }

        $repo = $this->getDoctrine()->getRepository(Spots::class);

        $spots = $repo->findAll();

        return $this->render('surfcams/webcams.html.twig', [
            'title' => "SURFCAMS",
            'spots' => $spots,
            'navItems' => ["WEBCAMS", "PRÉVISIONS", "BOUÉES"],
        ]);
    }

    /**
     * @Route("/Prévisions", name="Previsions") 
    */
    public function Previsions()
    {
        require 'databaseConnect.php';

        if($user->Get_is_authenticated() === FALSE){
            return $this->redirectToRoute('login');
        }

        return $this->render('surfcams/previsions.html.twig', [
            'title' => "SURFCAMS",
            'navItems' => ["WEBCAMS", "PRÉVISIONS", "BOUÉES"],
        ]);
    }

    /**
     * @Route("/Bouées", name="Bouées") 
    */
    public function Bouees()
    {
        require 'databaseConnect.php';

        if($user->Get_is_authenticated() === FALSE){
            return $this->redirectToRoute('login');
        }

        return $this->render('surfcams/bouées.html.twig', [
            'title' => "SURFCAMS",
            'navItems' => ["WEBCAMS", "PRÉVISIONS", "BOUÉES"],
        ]);
    }

    /**
     * @Route("/Register", name="register") 
    */
    public function Register()
    {


    require 'databaseConnect.php';

    $error = NULL;
    $success = FALSE;
    $sentmail = FALSE;

    if($user->Get_is_authenticated()){
        return $this->redirectToRoute("home");
    }

    if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['passwordRepeat'])){ // Tu check si tes input ne sont pas vides via la methode POST
            $username = $_POST['username']; // Tu stock les valeurs des inputs dans des variables
            $password = $_POST['password'];
            $passwordRepeat = $_POST['passwordRepeat'];
            $email = $_POST['email'];
            $passKeyOut = NULL;
            $res = $user->add_account($username, $password, $passwordRepeat, $email, $db, $passKeyOut);
            if($res === FALSE){ // Ici je check si il y a bien entre 3 et 24 caracteres pour le password et le username (j'ai changé le TRUE en -2 et -3 dans la méthode add-account)
                $error = 'Ce nom d\'utilisateur existe déjà';
            }
            else if($res == -4){
                $error =  'Veuillez renseigner votre email';
            }

            else if ($res === -2) {
                $error =  'Veuillez entrer un nom d\'utilisateur entre 3 et 24 caractères';
            }
            else if ($res === -3) {
                $error =  'Veuillez écrire un mot de passe entre 3 et 24 caractères';
            }
            else if($password != $passwordRepeat){
                $error = 'Mauvais mot de passe';
            }
            else if ($res === 1){

                ini_set("SMTP", "127.0.0.1");
                ini_set("smtp_port", "1025");

                $to =  $_POST['email'];
                $subject = "Confirmation from Surfcams to $username";
                $header = "Surfcams: Confirmation from Surfcams";
                $message = "Please click the link below to verify and activate your account : ";
                $message .= "http://127.0.0.1:8000/Confirm?passkey=$passKeyOut";
            
                $sentmail = mail($to,$subject,$message,$header);
            
                if($sentmail == TRUE)
                        {
                $success = 'Un mail de confirmation vous à été envoyé à l\'adresse : '.$email.'';
                }
                else
                    {
                $error =  'Impossible d\'envoyer un email de confirmation, merci de recommencer';
                }
            }  
        }


        return $this->render('surfcams/register.html.twig', [
            'error' => $error, 
            'success' => $success,
        ]);
    }


    /**
     * @Route("/Login", name="login") 
    */
    public function Login() {

        require 'databaseConnect.php';

        $error = NULL;
        $success = FALSE;

        if(isset($_POST['name']) && isset($_POST['pass'])){ 
        
            $pass = $_POST['pass'];
            $name = $_POST['name'];
            $log = $user->login($name, $pass);

            if ($log === -2) {
                $error = 'Mauvais nom d\'utilisateur ou mot de passe';
            }
            else if ($log === 5) {
                $error = 'Mauvais nom d\'utilisateur ou mot de passe';
            }
            else if ($log === 6) {
                $error =  'Mauvais nom d\'utilisateur ou mot de passe';
            }
            else if ($log === -5) {
                $error =  'Veuillez créer un compte ou confirmer votre Email si vous vous êtes déjà inscrit';
            }
            else if($log === 0){ 
                return $this->redirectToRoute("home");
            }
        
        
        }

        return $this->render('surfcams/login.html.twig', [
        'error' => $error, 
        'success' => $success,
        ]);
    }  
    
    /**
    * @Route("/Confirm", name="Confirm") 
    */
    public function Confirm() {

        require 'databaseConnect.php';

        $passKey = $_GET['passkey'];

        $user->confirm_account($passKey, $db);

        
        return $this->redirectToRoute("home");
    }

    /**
    * @Route("/Logout", name="Logout") 
    */
    public function Logout() {

        require 'databaseConnect.php';

        $user->logout();

        return $this->redirectToRoute("login");

    }


    /**
    * @Route("/Delete", name="Delete") 
    */
    public function DeleteAccount() {

        require 'databaseConnect.php';

        if($user->Get_is_authenticated() === FALSE){
            return $this->redirectToRoute('login');
        }

        $account_id = $user->Get_account_id();

        $user->delete_account($account_id, $db);

        return $this->redirectToRoute("login");
    }


     /**
    * @Route("/Profil", name="Profil") 
    */
    public function Profil() {

        require 'databaseConnect.php';

        if($user->Get_is_authenticated() === FALSE){
            return $this->redirectToRoute('login');
        }

        $error = NULL;
        $success = FALSE;

        if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['passwordRepeat'])){
            $account_id = $user->Get_account_id();
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $passwordRepeat = $_POST['passwordRepeat'];
            $res = $user->edit_account($account_id, $db, $username, $password, $enabled = NULL, $expiry = NULL, $email, $passwordRepeat);
    
            if($res === FALSE){ // Ici je check si il y a bien entre 3 et 24 caracteres pour le password et le username (j'ai changé le TRUE en -2 et -3 dans la méthode add-account)
                $error = 'Ce nom d\'utilisateur existe déjà';
            }
            else if($res == -4){
                $error =  'Veuillez renseigner votre email';
            }
            
            else if ($res === -2) {
                $error =  'Veuillez entrer un nom d\'utilisateur entre 3 et 24 caractères';
            }
            else if ($res === -3) {
                $error =  'Veuillez écrire un mot de passe entre 3 et 24 caractères';
            }
            else if($password != $passwordRepeat){
                $error =  'Mauvais mot de passe';
            }
            else if($res === -9){
                return $this->redirectToRoute("home");
            }
        }

        return $this->render('surfcams/profil.html.twig', [
            'error' => $error, 
            'success' => $success,
        ]);
    }
    
}
