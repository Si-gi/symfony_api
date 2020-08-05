<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class UserController extends AbstractController
{

	/**
	* @Route("/register", name="api_register", methods={"POST"})
	*/
	public function register(EntityManagerInterface $em, UserPasswordEncoderInterface $pwdEncoder, Request $request){
		$errors = [];
		$user = new User();
   		$email = $request->get("email");
		$password = $request->get("password");
	    	$passwordConfirmation = $request->get("password_confirmation");
		if(strlen($password) < 6)    {
		      $errors[] = "Password should be at least 6 characters.";
    		}
		if($password != $passwordConfirmation) {
		      $errors[] = "Password does not match the password confirmation.";
		}
		if(!$errors) {
			$encodedPassword = $pwdEncoder->encodePassword($user, $password);
			$user->setEmail($email);
			$user->setPassword($encodedPassword);
			try{
				$em->persist($user);
				$em->flush();
				return $this->json(['user' => $user ]);
			}catch(UniqueConstraintViolationException $e){
				$errors[] = "The email provided as already an account";
			}catch(\Exception $e){
				$errors[] = "Unable to save new user at this time";
			}
		}
		return $this->json(['errors' => $errors],400);
	}

	/**
	* @Route("/login", name="api_login", methods={"POST"})
	*/
	public function login(){

	}

	/**
	* @Route("/profile", name="api_profile")
	* @IsGranted("ROLE_USER")
	*/
	public function profile(){
		return $this->json(['user' => $this->getUser()],200, [], ['groups' => ['api']
					]);
	}
	/**
	* @Route("/", name="api_home")
	*/
	public function home()
	{
		return $this->json(['result' => true ]);
	}

}
