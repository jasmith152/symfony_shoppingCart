<?php
namespace AppBundle\services;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use \PDO;

class helperFunctions { 
    function startSession(){
        $session = new Session();   
        return $session;
    }
    function connection(){
        try{
            $results = new PDO("mysql:host=localhost;dbname=shopping_cart","johnny","Question1521");
            $results->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
            $results->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            header('Content-Type: text/html; charset=utf-8'); 
        }catch(PDOException $e){
            $results =  'ERROR: ' . $e->getMessage();
        }
        return $results;
    }

    function returnResults($conn,$sql,$sql_params){
        try{
            $statement = $conn->prepare($sql);
            $statement->execute($sql_params);
            $results = $statement->fetchAll();    
        }catch(PDOException $e){
            $results = 'ERROR: ' . $e->getMessage();
        }
        return $results;
    }

    function insertContent($conn,$sql,$sql_params){
        try{
            $statement = $conn->prepare($sql);
            $statement->execute($sql_params);  
            $results = $conn->lastInsertId();
        }catch(PDOException $e){
            $results = 'ERROR: ' . $e->getMessage();
        }
        return $results;
    }

    function getImage($name){
        $image_flag = FALSE;
        $upload_path = 'images/uploads/';
        $image = $upload_path.$name;
        if (!file_exists($image) || !is_file($image)){
            $image_flag = FALSE;
            $image = 'images/unavailable.png';
            $name = 'unavailable.png';
        }
        return $image;
    }
    function validate_form(){
        $post = array(
            'first_name' => filter_var($_POST['first_name'], FILTER_SANITIZE_STRING),
            'last_name' => filter_var($_POST['last_name'], FILTER_SANITIZE_STRING),
            'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            'confirm_email' => filter_var($_POST['confirm_email'], FILTER_SANITIZE_EMAIL, FILTER_SANITIZE_EMAIL),
            'password' => filter_var($_POST['password'], FILTER_SANITIZE_STRING),
            'confirm_password' => filter_var($_POST['confirm_password'], FILTER_SANITIZE_STRING)
        );
        unset($_POST);
        foreach($post as $row => $value){
            $value = filter_var($value, FILTER_FLAG_STRIP_LOW,FILTER_FLAG_STRIP_HIGH);
        }
        return $post;
    }
    function error_check($post){
        if(empty($post['password'])){$error['password'] = "Please enter a password.";}
        if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)){$error['email'] = "Invalid E-Mail Address";}
        $sql = "SELECT 1 FROM customers WHERE email = :email";
        $sql_params = array(
            ':email' => $post['email']
        );        
        $results = returnResults($conn,$sql,$sql_params);
        if($results){$error['dup_account'] = "This username/email account is already registered and in use";}
        if($post['email'] !== $post['confirm_email']){$error['confirm_email'] = "Email entered do not match";}
        if($post['password'] !== $post['confirm_password']){$error['confirm_password'] = "password entered do not match";}
        if(isset($error) && !empty($error)){
            echo '<Pre>';print_r($_SESSION);print_r($error);exit;
            //header("Location: register.php");
        }
    }
    function create_account_submit(){
        if(isset($_POST['create_account_submit'])){
            $conn = connection();
            $post = validate_form();
            error_check($post);    
            $sql = "INSERT INTO customers(first_name, last_name, email, password, salt) 
                   VALUES (:first_name,:last_name,:email,:password,:salt)";
            $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
            $password = hash('sha256', $post['password'] . $salt);
            for($i = 0; $i < 65536; $i++)
            {
                $password = hash('sha256', $password . $salt);
            }
            $sql_params = array(
                ':first_name' => $post['first_name'],
                ':last_name' => $post['last_name'],
                ':email' => $post['email'],
                ':password' => $password,
                ':salt' => $salt
            );
            $success = insertContent($conn,$sql,$sql_params);
            if(filter_var($success, FILTER_VALIDATE_INT)){
                $_SESSION['user']['email'] = $post['email'];
                $_SESSION['user']['first_name'] = $post['first_name'];
                $_SESSION['user']['last_name'] = $post['last_name'];
                header("Location: ../index.php");
            }else{
                $_SESSION['error']['create_account'] = "there was a problem creating your account. please contact someone about it.";
                header("Location: ../register.php");
            }
        }
    }

    function sign_in_submit($email,$password){
        $conn = $this->connection();
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $submitted_username = htmlentities($email, ENT_QUOTES, 'UTF-8');
        $sql = "SELECT customer_id,first_name,last_name,email,password,salt FROM customers WHERE email = :email";
        $sql_params = array(
            ':email' => $email
        );
        $results = $this->returnResults($conn,$sql,$sql_params);
        $login_ok = false;
        if(is_array($results)){
            $check_password = hash('sha256', $password . $results[0]['salt']);
            for($i = 0; $i < 65536; $i++){
                $check_password = hash('sha256', $check_password . $results[0]['salt']);
            }
            if($check_password === $results[0]['password']){
                $login_ok = true;
            }
        }
        if($login_ok){
            $session = $this->startSession();
            $session->set('user_customer_id', $results[0]['customer_id']);
            $session->set('user_first_name', $results[0]['first_name']);
            $session->set('user_last_name', $results[0]['last_name']);
            $session->set('user_email', $results[0]['email']);
            unset($results);
            $session->remove('error');
        }  
        return $login_ok;
    }

    function sign_out_submit(){
        $logout = 1;
        $session = $this->startSession();
        $session->remove('user_customer_id');
        $session->remove('user_first_name');
        $session->remove('user_last_name');
        $session->remove('user_email');
        if($session->has('user_customer_id') || $session->has('user_first_name') || $session->has('user_last_name') || $session->has('user_email')){
            $logout = 0;
        }
        return $logout;
    }
}