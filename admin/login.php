<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Admin Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <link rel="stylesheet" type="text/css" href="../assets/bootstrap/css/bootstrap.min.css" media="all">
        <link rel="stylesheet" type="text/css" href="../assets/bootstrap/css/bootstrap-responsive.min.css" media="all">
        <link rel="stylesheet" type="text/css" href="../assets/css/admin-login.css" media="all">
    </head>
    <body>
        <h1>Admin Login</h1>
        <div id="loginbox">
            <form action="" method="post" id="login">
                <p>Enter username and password to continue.</p>
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-user"></i></span><input type="text" name="user" id="user" placeholder="Username or Email" value="">
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-lock"></i></span><input type="password" name="password" id="password" placeholder="Password" value="">
                        </div>
                    </div>
                </div>
                <div style="display: inline-block; width: 247px;">
                    <label class="checkbox" style="text-align: left;"><input type="checkbox" name="remember" id="remember"> Remember me</label>
                </div>
                <?php
                    //If the form is submited
                    if (isset($_POST['login'])) 
                    {
                        //Create an array with the data needed for the login function
                        $data = array(
                                'user'     => $_POST['user'],
                                'password' => $_POST['password'],
                                'remember' => (isset($_POST['remember'])) ? TRUE : FALSE
                            );
                        //Call the login function
                        $Account->login($data);

                        //Get the errors
                        $errors = $Account->errors();

                        //If we have errors display them
                        if (!empty($errors)) 
                        {
                            echo '<div class="alert alert-error"><strong>Error: </strong> Enter valid information.</div>';
                        }
                        //Else the user is logged and refresh the page
                        else redirect('index.php');
                    }
                ?>

                <div class="form-actions">
                    <span class="pull-left"><a href="../">&larr; My Site</a></span>
                    <span class="pull-right"><input type="submit" name="login" class="btn" value="Login"></span>
                </div>
        	</form>
        </div>
    </body>
</html>