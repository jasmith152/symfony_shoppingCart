<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $session = $helperFunctions->startSession();        
        if($session->has('user_customer_id')){
            $loggedIn = 1;
            $first_name = $session->get('user_first_name');
            $last_name = $session->get('user_first_name');
            $title = 'Logged In';
        }else{
            $loggedIn = 0;
            $first_name = '';
            $last_name = '';
            $title = 'Welcome';
        }        
        return $this->render('index.html.twig', array(
            'title' => $title,
            'loggedIn' => $loggedIn,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ));
    }
    public function signInAction(Request $request)
    {   
        $title = 'Sign In';
        $loggedIn = 0;        
        return $this->render('signIn.html.twig', array(
            'title' => $title,
            'loggedIn' => $loggedIn
        ));
    }
    public function signIn_processAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $successful_login = $helperFunctions->sign_in_submit($email,$password);  
        if($successful_login == 1){   
            $session = $helperFunctions->startSession();
            $session_array = $session->all();
            $title = 'Logged In';
            $loggedIn = 1;
            return $this->render('index.html.twig', array(
                'title' => $title,
                'loggedIn' => $loggedIn,
                'first_name' => $session_array['user_first_name'],
                'last_name' => $session_array['user_last_name'],
            )); 
        }else{
            return $this->render('signIn.html.twig', array(
                'title' => $title,
                'loggedIn' => $loggedIn
            ));
        }
    }
    
    public function signOutAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $successful_logout = $helperFunctions->sign_out_submit();  
        if($successful_logout == 1){   
            $title = 'Welcome';
            $loggedIn = 0;
            $first_name = '';
            $last_name = '';
            return $this->render('index.html.twig', array(
                'title' => $title,
                'loggedIn' => $loggedIn,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ));
        }        
    }
    public function registerAction(Request $request)
    {   
        $title = 'Create An Account';
        $loggedIn = 0;        
        return $this->render('register.html.twig', array(
            'title' => $title,
            'loggedIn' => $loggedIn
        ));
    }
    public function register_processAction(Request $request)
    {   
        $title = 'inside register process Action';
        echo $title;
        exit;
        
    }
    public function browsePrintsAction(Request $request)
    {   
        $title = 'inside browsePrints Action process Action';
        echo $title;
        exit;
    }
    public function viewCartAction(Request $request)
    {   
        $title = 'inside viewCart Action process Action';
        echo $title;
        exit;
    }
}
