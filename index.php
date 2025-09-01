<?php 
require_once 'constants/dbconnect.php';
require_once 'constants/auth.php';
session_start();


// Normal login functionality


if(isset($_SESSION['userName'])) {
    header('Location: start_page.php'); 
    exit();
}

$errors = array();
$username = ""; // 

if($_SERVER['REQUEST_METHOD'] == 'POST') {		

    $username = trim($_POST['uname']);
    $password = trim($_POST['psw']);

    if(empty($username)) {
        $errors[] = "Username is required.";
    } 

    if(empty($password)) {
        $errors[] = "Password is required.";
    }

    if(empty($errors)) { 
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1) {
            $value = $result->fetch_assoc();
            $dbPassword = $value['password'];
           
            // Support both hashed and unhashed passwords during transition
            if (password_verify($password, $dbPassword) || $password == $dbPassword) {
                // If plain password matched, update to hashed version
                if ($password == $dbPassword) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $hashedPassword, $value['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
    $_SESSION['user_id'] = $value['id']; 
    $_SESSION['username'] = $value['username'];
    $_SESSION['user_type'] = $value['user_type'];
    $_SESSION['user_name'] = $value['username'];
    header('Location: start_page.php');
    exit();
}
            
             else {
                $errors[] = "Incorrect username/password combination.";
            }
        } else {		
            $errors[] = "Username does not exist.";		
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login</title>
<link rel="stylesheet" href="custom/css/style.css">
<script src="assests/jquery/jquery-3.6.0.min.js"></script>
<style>
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        border-radius: 5px;
        padding: 8px 12px; 
        margin-bottom: 10px; 
        font-size: 14px;
        text-align: center;
        max-width: 350px;
        margin-left: auto;
        margin-right: auto;
    }

    .error-message ul {
        list-style-type: none;
        padding-left: 0;
        margin: 0; 
    }

    .error-message ul li {
        margin-bottom: 5px; 
    }

    .container {
        max-width: 400px;
        margin: auto;
    }

    .btn {
        background-color: #4CAF50;
        color: white;
        padding: 14px 20px;
        margin: 8px 0;
        border: none;
        cursor: pointer;
        width: 100%;
    }

    body {font-family: Arial, Helvetica, sans-serif;}
form {border: 3px solid #f1f1f1;}

input[type=text], input[type=password] {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  box-sizing: border-box;
}

button {
  background-color: #04AA6D;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
}

button:hover {
  opacity: 0.8;
}

.cancelbtn {
  width: auto;
  padding: 10px 18px;
  background-color: #f44336;
}

.imgcontainer {
text-align: center;
    margin: 24px 0 12px 0;
  
}

.imgcontainer p{
    font-size: larger;
    font-weight: bolder;
}

img.avatar {
  width: 40%;
  border-radius: 50%;
  
}

.container {
  padding: 16px;
}

span.psw {
  float: right;
  padding-top: 16px;
}

/* Change styles for span and cancel button on extra small screens */
@media screen and (max-width: 300px) {
  span.psw {
     display: block;
     float: none;
  }
  .cancelbtn {
     width: 100%;
  }
}
</style>

</head>
<body>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="loginForm">
  <div class="imgcontainer">
    <img src="logo.png" style="width: 150px; height: 70px;">
    
  </div>

<?php 

if (!empty($errors)) {
    echo '<div class="error-message">';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
}
?>

  <div class="container">
    <label for="uname"><b>Username</b></label>
    <input type="text" placeholder="Enter Username" name="uname" value="<?php echo htmlspecialchars($username); ?>" required>

    <label for="psw"><b>Password</b></label>
    <input type="password" placeholder="Enter Password" name="psw" required>

    <label onclick="togglePasswordVisibility()" style="cursor:pointer;"> Show Password</label><br><br>
        
    <button type="submit" class="btn-in">Login</button>
    <label>
      <input type="checkbox" checked="checked" name="remember"> Remember me
    </label>
  </div>
<!--
  <div class="container" style="background-color:#f1f1f1">
    <button type="button" class="cancelbtn">Cancel</button>
    <span class="psw">Forgot <a href="#">password?</a></span>
  </div>
-->
</form>

<script>
function togglePasswordVisibility() {
    const passwordField = document.querySelector("input[name='psw']");
    passwordField.type = passwordField.type === "password" ? "text" : "password";
}

setTimeout(function() {
    const errorBox = document.querySelector('.error-message');
    if (errorBox) {
        errorBox.style.display = 'none';
    }
}, 2000); 
</script>

</body>
</html>
