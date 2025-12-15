<?php
require_once 'dbconnect.php';
require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Migration Tool</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow: auto;
            max-height: 400px;
            font-family: monospace;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Password Migration Tool</h2>
    
    <div class="card">
        <div class="warning">
            <strong>Warning:</strong> This tool will hash all plain text passwords in your database. This is a one-way process and cannot be reversed. Make sure you have a backup of your database before proceeding.
        </div>
        
        <h3>Hash All Passwords</h3>
        <p>Click the button below to hash all plain text passwords in the database. This will make your system more secure by ensuring passwords are properly protected.</p>
        
        <form id="hashForm" method="post">
            <button type="submit" id="hashButton" class="btn-primary">Hash All Passwords</button>
        </form>
        
        <div id="result" style="margin-top: 20px; display: none;">
            <h4>Migration Result:</h4>
            <pre id="output"></pre>
        </div>
    </div>
</div>

<script>
document.getElementById('hashForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    document.getElementById('hashButton').disabled = true;
    document.getElementById('hashButton').innerText = 'Processing...';
    document.getElementById('result').style.display = 'block';
    document.getElementById('output').innerText = 'Starting password hashing process...\n';
    
    fetch('hash_password.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('output').innerText = data;
            document.getElementById('hashButton').innerText = 'Completed';
        })
        .catch(error => {
            document.getElementById('output').innerText = 'Error: ' + error;
            document.getElementById('hashButton').disabled = false;
            document.getElementById('hashButton').innerText = 'Try Again';
        });
});
</script>

</body>
</html>