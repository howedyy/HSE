<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
<!--<link rel="icon" type="image/x-icon" href="favicon.ico">-->

	<title>Title</title>
<!-- bootstrap -->
	<link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
	<!-- bootstrap theme-->
	<link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
	<!-- font awesome -->
	<link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">

  <!-- custom css -->
  <link rel="stylesheet" href="custom/css/custom.css">
  <!-- jquery -->
	<script src="assests/jquery/jquery.min.js"></script>
	<script src="assests/jquery/jquery-3.6.0.min.js"></script>
  <!-- jquery ui -->  
  <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
  <script src="assests/jquery-ui/jquery-ui.min.js"></script>
<script src="assests/bootstrap/js/bootstrap.min.js"></script>	

<!-- Enhanced Mobile Navigation Styles -->
<style>
/* Fix navbar height and ensure proper mobile functionality */
.navbar {
    min-height: 60px !important;
    height: auto !important;
}

.navbar-header {
    height: auto !important;
}

/* Enhanced mobile toggle button */
.navbar-toggle {
    margin-top: 13px;
    margin-right: 15px;
    padding: 9px 10px;
    background-color: #007bff !important;
    border: 1px solid #007bff !important;
    border-radius: 4px;
}

.navbar-toggle:hover,
.navbar-toggle:focus {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
}

.navbar-toggle .icon-bar {
    background-color: #fff !important;
}

/* Ensure mobile menu appears correctly */
@media (max-width: 767px) {
    .navbar-collapse {
        border-top: 1px solid #ddd;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.1);
        margin-top: 10px;
        padding-top: 10px;
    }
    
    .navbar-nav {
        margin: 0;
    }
    
    .navbar-nav > li {
        float: none;
    }
    
    .navbar-nav > li > a {
        padding: 10px 15px;
        color: #333 !important;
    }
    
    .navbar-nav > li > a:hover {
        background-color: #f5f5f5 !important;
        color: #007bff !important;
    }
    
    /* Style dropdowns in mobile */
    .navbar-nav .dropdown-menu {
        position: static;
        float: none;
        width: auto;
        margin-top: 0;
        background-color: #f8f9fa;
        border: 0;
        box-shadow: none;
        border-radius: 0;
    }
    
    .navbar-nav .dropdown-menu > li > a {
        padding: 8px 25px;
        color: #555 !important;
        font-size: 14px;
    }
    
    .navbar-nav .dropdown-menu > li > a:hover {
        background-color: #e9ecef !important;
        color: #007bff !important;
    }
    
    /* Open dropdowns by default on mobile */
    .navbar-nav .dropdown.open .dropdown-menu {
        display: block;
    }
}

/* Logo adjustment */
.navbar-brand {
    padding: 5px 15px !important;
}

.navbar-brand img {
    max-height: 50px;
    width: auto;
}

/* Ensure content doesn't overlap with fixed navbar */
body {
    padding-top: 70px;
}
</style>
</head>
<body>

	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>        
      </button>
      <a class="navbar-brand" href="start_page.php">
		<img src="logo.png" alt="Logo" width="130" height="40">
	  </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
   <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
    <ul class="nav navbar-nav navbar-right">
        
        <?php require_once "auth.php"; ?>

<!-- 🌐 DAILY REPORT -->
<?php if (
    hasAccess('dailyreport_overview.php', 'view') ||
    hasAccess('dailyreport.php', 'view') ||
    hasAccess('dailyreport_analysis.php', 'view')
): ?>
  <li class="dropdown" id="navDailyReport">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
      <span style="color: blue;">DAILY REPORT</span> <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
      <?php if (hasAccess('dailyreport_overview.php', 'view')): ?>
        <li><a href="dailyreport_overview.php"><i class="glyphicon glyphicon-list-alt"></i> Daily Report Overview</a></li>
      <?php endif; ?>
      <?php if (hasAccess('dailyreport.php', 'view')): ?>
        <li><a href="dailyreport.php"><i class="glyphicon glyphicon-file"></i> Daily Report</a></li>
      <?php endif; ?>
      <?php if (hasAccess('dailyreport_analysis.php', 'view')): ?>
        <li><a href="dailyreport_analysis.php"><i class="glyphicon glyphicon-stats"></i> Daily Report Analysis</a></li>
      <?php endif; ?>
    </ul>
  </li>
<?php endif; ?>

<!-- 🛠 PTW -->
<?php if (
  hasAccess('ptw_overview.php', 'view') || 
  hasAccess('ptw.php', 'view') || 
  hasAccess('ptw_analysis.php', 'view')
): ?>
  <li class="dropdown" id="navPTW">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
      <span style="color: red;">PTW</span> <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
      <?php if (hasAccess('ptw_overview.php', 'view')): ?>
        <li><a href="ptw_overview.php"><i class="glyphicon glyphicon-list"></i> PTW Overview</a></li>
      <?php endif; ?>
      <?php if (hasAccess('ptw.php', 'view')): ?>
        <li><a href="ptw.php"><i class="glyphicon glyphicon-plus"></i> New PTW</a></li>
      <?php endif; ?>
      <?php if (hasAccess('ptw_analysis.php', 'view')): ?>
        <li><a href="ptw_analysis.php"><i class="glyphicon glyphicon-stats"></i> PTW Analysis</a></li>
      <?php endif; ?>
    </ul>
  </li>
<?php endif; ?>
        
       <!--- Setting -->
        <li class="dropdown" id="navSetting">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                <img src="assests/icons/user-icon.png">
                <span class="caret"></span>
            </a>
           <ul class="dropdown-menu">
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
        <li id="topNavSetting">
            <a href="#"><i class="glyphicon glyphicon-wrench"></i> Setting</a>
        </li>
        <li id="topNavUser">
            <a href="user.php"><i class="glyphicon glyphicon-user"></i> Users</a>
        </li>
        <li id="topNavUser">
            <a href="add_user.php"><i class="glyphicon glyphicon-credit-card"></i> Add User</a></li>
    <?php endif; ?>

    <li id="topNavLogout">
        <a href="logout.php"><i class="glyphicon glyphicon-log-out"></i> Log Out</a>
    </li>
</ul>
        </li>
    </ul>
</div><!-- /.navbar-collapse -->

  </div><!-- /.container -->
</nav>

<!-- Enhanced JavaScript for stable mobile menu behavior -->
<script>
$(document).ready(function() {
    var isMenuOpen = false;
    var $navbarCollapse = $('#bs-example-navbar-collapse-1');
    var $navbarToggle = $('.navbar-toggle');
    
    // Override default Bootstrap behavior for more control
    $navbarToggle.off('click.bs.collapse.data-api');
    
    // Custom toggle handler
    $navbarToggle.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        isMenuOpen = !isMenuOpen;
        
        if (isMenuOpen) {
            // Open menu
            $navbarCollapse.addClass('in').addClass('show');
            $(this).removeClass('collapsed').attr('aria-expanded', 'true');
        } else {
            // Close menu
            $navbarCollapse.removeClass('in').removeClass('show');
            $(this).addClass('collapsed').attr('aria-expanded', 'false');
            $('.dropdown').removeClass('open');
        }
    });
    
    // Handle dropdown clicks on mobile
    $('.dropdown-toggle').on('click', function(e) {
        if ($(window).width() <= 767) {
            e.preventDefault();
            e.stopPropagation();
            
            var $dropdown = $(this).parent();
            var wasOpen = $dropdown.hasClass('open');
            
            // Close all other dropdowns
            $('.dropdown').removeClass('open');
            
            // Toggle current dropdown
            if (!wasOpen) {
                $dropdown.addClass('open');
            }
        }
    });
    
    // Close menu when clicking outside (but not immediately)
    $(document).on('click', function(e) {
        if (isMenuOpen && !$(e.target).closest('.navbar').length) {
            setTimeout(function() {
                isMenuOpen = false;
                $navbarCollapse.removeClass('in').removeClass('show');
                $navbarToggle.addClass('collapsed').attr('aria-expanded', 'false');
                $('.dropdown').removeClass('open');
            }, 100);
        }
    });
    
    // Close dropdowns when clicking menu items
    $('.navbar-nav a:not(.dropdown-toggle)').on('click', function() {
        if ($(window).width() <= 767) {
            setTimeout(function() {
                isMenuOpen = false;
                $navbarCollapse.removeClass('in').removeClass('show');
                $navbarToggle.addClass('collapsed').attr('aria-expanded', 'false');
                $('.dropdown').removeClass('open');
            }, 200);
        }
    });
    
    // Handle window resize
    $(window).resize(function() {
        if ($(window).width() > 767) {
            isMenuOpen = false;
            $navbarCollapse.removeClass('in').removeClass('show');
            $navbarToggle.addClass('collapsed').attr('aria-expanded', 'false');
            $('.dropdown').removeClass('open');
        }
    });
    
    // Prevent menu from closing when clicking inside dropdown
    $('.dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>

</body>
</html>