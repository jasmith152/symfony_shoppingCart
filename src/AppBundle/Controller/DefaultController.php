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
        $login = $helperFunctions->loggedInCheck();        
        $title = 'Welcome';
        return $this->render('index.html.twig', array(
            'title' => $title,
            'loggedIn' => $login['loggedIn'],
            'name' => $login['name'],
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
            $login = $helperFunctions->loggedInCheck();
            $title = 'Logged In';
            return $this->render('index.html.twig', array(
                'title' => $title,
                'loggedIn' => $login['loggedIn'],
                'name' => $login['name'],
            )); 
        }else{
            $loggedIn = 0; 
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
            $login = $helperFunctions->loggedInCheck();        
            $title = 'Welcome';
            return $this->render('index.html.twig', array(
                'title' => $title,
                'loggedIn' => $login['loggedIn'],
                'name' => $login['name'],
            ));
        }        
    }
    public function registerAction(Request $request)
    {   
        $title = 'Create An Account';
        $loggedIn = 0;        
        return $this->render('register.html.twig', array(
            'title' => $title,
            'loggedIn' => $loggedIn,
            'error' => ""
        ));
    }
    public function register_processAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $data = $request->request->all();
        $results = $helperFunctions->create_account_submit($data);
        if(is_int($results)){            
            $login = $helperFunctions->loggedInCheck();        
            $title = 'Welcome';
            return $this->render('index.html.twig', array(
                'title' => $title,
                'loggedIn' => $login['loggedIn'],
                'name' => $login['name'],
            ));
        }else{
            $title = 'Create An Account';
            $loggedIn = 0;        
            return $this->render('register.html.twig', array(
                'title' => $title,
                'loggedIn' => $loggedIn,
                'error' => $results
            ));
        }        
    }
    public function browsePrintsAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $sql = "SELECT prints.*,artists.artists_id,CONCAT_WS(' ', first_name, middle_name, last_name) AS artist FROM prints AS prints
        JOIN artists ON prints.artist_id = artists.artists_id WHERE prints.available = :available ORDER BY prints.print_id ASC ";
        $sql_params = array(
            ':available' => 1
        );
        $results = $helperFunctions->returnResults($sql,$sql_params);
        $results = $helperFunctions->getImage($results);
        $login = $helperFunctions->loggedInCheck();
        $title = 'Browse Prints';
        return $this->render('browsePrints.html.twig', array(
            'title' => $title,
            'loggedIn' => $login['loggedIn'],
            'name' => $login['name'],
            'data' => $results,
        ));
    }
    public function viewPrintsAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $pid = $request->query->get('pid');
        $sql = "SELECT prints.*,artists.artists_id,CONCAT_WS(' ', first_name, middle_name, last_name) AS artist FROM prints AS prints
        JOIN artists ON prints.artist_id = artists.artists_id WHERE prints.print_id = :print_id";
        $sql_params = array(
            ':print_id' => $pid
        );
        $results = $helperFunctions->returnResults($sql,$sql_params);
        $results = $helperFunctions->getImage($results);
        $login = $helperFunctions->loggedInCheck();
        $title = $results[0]['print_name'].' by '.$results[0]['artist'];
        return $this->render('viewPrint.html.twig', array(
            'title' => $title,
            'loggedIn' => $login['loggedIn'],
            'name' => $login['name'],
            'data' => $results,
        ));
    }    
    public function viewAtristAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $aid = $request->query->get('aid');
        $sql = "SELECT prints.*,artists.artists_id,CONCAT_WS(' ', first_name, middle_name, last_name) AS artist FROM prints AS prints
        JOIN artists ON prints.artist_id = artists.artists_id WHERE prints.artist_id = :artist_id";
        $sql_params = array(
            ':artist_id' => $aid
        );
        $results = $helperFunctions->returnResults($sql,$sql_params);
        $results = $helperFunctions->getImage($results);
        $login = $helperFunctions->loggedInCheck();
        $title =  $results[0]['artist'].' Art';
        return $this->render('viewArtist.html.twig', array(
            'title' => $title,
            'loggedIn' => $login['loggedIn'],
            'name' => $login['name'],
            'data' => $results,
        ));
    }    
    public function addCartAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $pid = $request->query->get('pid');
        $session = $helperFunctions->startSession();
        $cart = $session->get('cart');
        $sql = "SELECT price FROM prints WHERE prints.print_id = :print_id";
        $sql_params = array(
            ':print_id' => $pid
        );
        $results = $helperFunctions->returnResults($sql,$sql_params);
        if(is_array($results)){
            $found = FALSE;
            if($session->has('cart')){
                $cart = $session->get('cart');
                foreach($cart as $row => $value){
                    if($row == $pid){
                       $cart[$pid]['quantity']++;
                       $found = TRUE;
                    }
                }
                if(!$found){
                   $pid_array = array(
                        'quantity' => 1, 
                        'price' => $results[0]['price']
                    ); 
                    $cart[$pid] = $pid_array; 
                }
            }else{
                $pid = array(
                    'quantity' => 1, 
                    'price' => $results[0]['price']
                ); 
            }            
            $session->set('cart', $cart);
        }else{
            $error = '<div align="center">'.$results.'</div>';
        }
        $login = $helperFunctions->loggedInCheck();
        $title = 'Add to Cart';
        return $this->render('addCart.html.twig', array(
            'title' => $title,
            'loggedIn' => $login['loggedIn'],
            'name' => $login['name']
        ));
    }    
    public function viewCartAction(Request $request)
    {   
        $helperFunctions = $this->get('helperFunctions');
        $session = $helperFunctions->startSession();
        $cart = $session->get('cart');
        echo'<pre>';print_r($cart);exit;
        $title = 'inside viewCart Action process Action';
        echo $title;
        exit;
    }
}
