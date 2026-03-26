<?php

    $uri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET'){
        if($uri === '/signin'){
            echo '<form id="signinForm" action="/signin" method="post">
                    <input type="email" id="signinEmail" name="email" placeholder="Email">
                    <input type="password" id="signinPassword" name="password" placeholder="Password" required>
                    <button type="submit">Sign In</button>
                </form>';
            exit;
        }
        if ($uri === '/signup'){
            echo '<form id="signupForm" action="/signup" method="post">
                    <input type="email" name="email" id="signupEmail" placeholder="Email" required>
                    <input type="password" name="password" id="signupPassword" placeholder="Password" required>
                    <button type="submit">Sign Up</button>
                </form>';
            exit;         
        }
    }
    elseif ($uri === '/signup' && $method === 'POST'){
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if(!$email || !$password){
            echo "Email and password are required!";
        }
        // No database yet
        else{
            echo "User registered successfully.";
        }
    }elseif($uri === '/signin' && $method === 'POST'){
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if(!$email || !$password){
            echo "Email and password are required!";
        }
        // Temporary as we don't have a db yet
        else{
            echo "Login successful";
        }
    }else {
        echo "Route not found";
    }

?>