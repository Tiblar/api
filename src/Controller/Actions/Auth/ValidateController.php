<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use App\Service\User\Validator;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

class ValidateController extends ApiController
{
    /**
     * @Route("/auth/validate-username", name="validate_username", methods={"GET"})
     */
    public function validateUsername(Request $request, Validator $validator)
    {
        $username = $request->query->get('username');

        $usernameValidator = $validator->username($username);
        if($usernameValidator !== true){
            return $this->respondWithErrors([
                'username' => $usernameValidator
            ], 'Authentication error.');
        }

        return $this->respond([]);
    }

    /**
     * @Route("/auth/validate-email", name="validate_email", methods={"GET"})
     */
    public function validateEmail(Request $request, Validator $validator)
    {
        $email = $request->query->get('email');

        if(!is_string($email) || strlen($email) === 0 || empty($email)){
            $email = null;
        }

        $emailValidator = $validator->email($email);
        if($emailValidator !== true && !is_null($email)){
            return $this->respondWithErrors([
                'email' => $emailValidator
            ], 'Authentication error.');
        }

        return $this->respond([]);
    }
}