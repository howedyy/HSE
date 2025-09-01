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
	<!--<script src="assests/jquery/jquery.min.js"></script>-->
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
    cursor: pointer;
    z-index: 9999;
    position: relative;
    display: none; /* Hidden by default, shown on mobile */
    min-width: 44px; /* Better touch target */
    min-height: 44px; /* Better touch target */
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.navbar-toggle:hover,
.navbar-toggle:focus {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
}

.navbar-toggle .icon-bar {
    display: block;
    width: 22px;
    height: 2px;
    background-color: #fff !important;
    margin: 4px 0;
    transition: all 0.2s;
}

/* Ensure mobile menu appears correctly */
@media (max-width: 767px) {
    /* Improved mobile navigation structure */
    .navbar-collapse {
        border-top: 1px solid #ddd;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.1);
        margin-top: 10px;
        padding-top: 10px;
        width: 100%;
        clear: both;
        overflow-y: auto;
        max-height: calc(100vh - 70px);
        display: none !important;
    }
    
    /* Show the navbar when active */
    .navbar-collapse.in {
        display: block !important;
        overflow-y: visible;
    }
    
    .navbar-nav {
        margin: 0;
        width: 100%;
    }
    
    .navbar-nav > li {
        float: none;
        width: 100%;
        display: block;
        clear: both;
    }
    
    /* Better touch targets on mobile */
    .navbar-nav > li > a,
    .navbar-nav .dropdown-toggle {
        padding: 12px 15px !important;
        color: #333 !important;
        width: 100%;
        display: block;
        font-size: 16px;
    }
    
    .navbar-nav > li > a:hover,
    .navbar-nav > li > a:active,
    .navbar-nav > li > a:focus {
        background-color: #f5f5f5 !important;
        color: #007bff !important;
    }
    
    /* Enhanced mobile dropdown styles */
    .navbar-nav .dropdown-menu {
        position: static !important;
        float: none;
        width: 100%;
        margin: 0;
        padding: 5px 0;
        background-color: #f8f9fa;
        border: none;
        box-shadow: none;
        border-radius: 0;
        display: none;
    }
    
    .navbar-nav .dropdown-menu > li {
        width: 100%;
    }
    
    .navbar-nav .dropdown-menu > li > a {
        padding: 10px 25px !important;
        color: #555 !important;
        font-size: 15px;
        display: block;
        width: 100%;
    }
    
    .navbar-nav .dropdown-menu > li > a:hover,
    .navbar-nav .dropdown-menu > li > a:active {
        background-color: #e9ecef !important;
        color: #007bff !important;
    }
    
    /* Ensure dropdowns appear below their parent */
    .navbar-nav .dropdown.open .dropdown-menu {
        display: block !important;
        width: 100%;
        margin-top: 0;
        max-height: 350px; /* Limit height on mobile to prevent covering too much of the screen */
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    }
    
    /* Clear styling for caret and make it more visible */
    .caret {
        display: inline-block !important;
        margin-left: 8px;
        border-top: 6px solid;
        border-right: 6px solid transparent;
        border-left: 6px solid transparent;
        vertical-align: middle;
    }
    
    /* All dropdown menus should be left-aligned on mobile */
    .dropdown.open .dropdown-menu {
        right: auto !important;
        left: 0 !important;
        top: 100% !important;
        width: 100% !important;
    }
    
    /* Add visual indicator for dropdown sections */
    .navbar-nav .dropdown > a {
        position: relative;
        background-color: #f0f0f0;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }
    
    /* Make the current open dropdown more visible */
    .navbar-nav .dropdown.open > a {
        background-color: #e0e0e0 !important;
        border-bottom: 2px solid #007bff !important;
    }
    
    /* Fix for user icon dropdown */
    #navSetting > a {
        display: flex !important;
        align-items: center !important;
    }
    
    #navSetting img {
        max-height: 25px !important;
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

/* Improved dropdown styles for all dropdowns */
.dropdown {
    position: relative;
}

.dropdown-toggle {
    cursor: pointer;
}

/* Show dropdown menu when .open class is present */
.dropdown.open .dropdown-menu {
    display: block;
}

/* Right-align the user dropdown menu */
#navSetting.open .dropdown-menu {
    right: 0;
    left: auto;
}

/* Make the user icon clickable */
#navSetting img {
    cursor: pointer;
}

/* Fix positioning issues with dropdowns */
.dropdown-menu {
    position: absolute;
    top: 100%;
    z-index: 1000;
    min-width: 160px;
    background-color: #fff;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0,0,0,.175);
}

/* Ensure content doesn't overlap with fixed navbar */
body {
    padding-top: 70px;
}

/* Fix for the navbar toggle button on mobile */
@media (max-width: 767px) {
    .navbar-toggle {
        display: block !important;
        float: right;
        margin-right: 15px;
    }
    
    .navbar-collapse {
        border-top: 1px solid #e7e7e7;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);
        padding-left: 15px;
        padding-right: 15px;
        transition: all 0.3s ease-in-out;
    }
    
    .navbar-collapse.collapse {
        display: none !important;
        height: 0 !important;
        padding-top: 0;
        padding-bottom: 0;
        overflow: hidden;
    }
    
    .navbar-collapse.in {
        display: block !important;
        overflow-y: auto;
        height: auto !important;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    
    .navbar-fixed-top .navbar-collapse {
        max-height: calc(100vh - 70px);
        overflow-y: auto;
    }
    
    .navbar-header {
        float: none;
    }
    
    /* Improve touch interactions */
    .navbar-nav > li > a,
    .dropdown-toggle {
        -webkit-tap-highlight-color: rgba(0,0,0,0.1);
        touch-action: manipulation;
    }
    
    /* Ensure better spacing on mobile */
    .navbar-brand {
        float: left;
        margin-right: 0;
    }
    
    /* Animation for the hamburger icon when menu is open */
    .navbar-toggle.collapsed .icon-bar:nth-child(2) {
        transform: rotate(0deg);
    }
    
    .navbar-toggle:not(.collapsed) .icon-bar:nth-child(2) {
        transform: rotate(45deg);
        position: relative;
        top: 6px;
    }
    
    .navbar-toggle:not(.collapsed) .icon-bar:nth-child(3) {
        opacity: 0;
    }
    
    .navbar-toggle:not(.collapsed) .icon-bar:nth-child(4) {
        transform: rotate(-45deg);
        position: relative;
        top: -6px;
    }
}
</style>
</head>
<body>

	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false" onclick="simpleToggle()">
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
        
        <?php require_once "constants/auth.php"; ?>

<!-- 🌐 DAILY REPORT -->
<?php if (
    hasAccess('dailyreport_overview.php', 'view') ||
    hasAccess('dailyreport.php', 'view') ||
    hasAccess('dailyreport_analysis.php', 'view')
): ?>
  <li class="dropdown" id="navDailyReport">
    <a href="#" class="dropdown-toggle" >
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
    <a href="#" class="dropdown-toggle" >
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
            <a href="#" class="dropdown-toggle"  style="display: flex; align-items: center; padding: 10px 15px;">
                <img src="assests/icons/user-icon.png" style="max-height: 30px; width: auto; margin-right: 5px;">
                <span class="caret"></span>
            </a>
           <ul class="dropdown-menu" id="userDropdown">
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

<!-- Simple and Reliable Mobile Menu JavaScript -->
<script>
// Ultra-simple toggle function that should always work
function simpleToggle() {
    console.log('Simple toggle called');
    var menu = document.getElementById('bs-example-navbar-collapse-1');
    if (menu) {
        console.log('Current display:', window.getComputedStyle(menu).display);
        if (menu.style.display === 'block') {
            menu.style.display = 'none';
            console.log('Menu hidden');
        } else {
            menu.style.display = 'block';
            console.log('Menu shown');
        }
    } else {
        console.log('Menu element not found');
    }
}

// Simple toggle function for mobile menu
function toggleMobileMenu() {
    console.log('Toggle mobile menu called');
    var navbar = document.getElementById('bs-example-navbar-collapse-1');
    var button = document.querySelector('.navbar-toggle');
    
    console.log('Navbar element:', navbar);
    console.log('Button element:', button);
    
    if (!navbar || !button) {
        console.log('Elements not found');
        return;
    }
    
    // Check if menu is currently open
    var isOpen = navbar.classList.contains('in');
    console.log('Menu is open:', isOpen);
    
    if (isOpen) {
        // Close menu
        navbar.classList.remove('in');
        navbar.style.display = 'none';
        button.classList.add('collapsed');
        button.setAttribute('aria-expanded', 'false');
        console.log('Menu closed');
        
        // Close all dropdowns
        var dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(function(dropdown) {
            dropdown.classList.remove('open');
        });
    } else {
        // Open menu
        navbar.classList.add('in');
        navbar.style.display = 'block';
        button.classList.remove('collapsed');
        button.setAttribute('aria-expanded', 'true');
        console.log('Menu opened');
    }
}

// Simple dropdown toggle
function toggleDropdown(dropdownId) {
    var dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;
    
    var isOpen = dropdown.classList.contains('open');
    
    // Close all other dropdowns first
    var allDropdowns = document.querySelectorAll('.dropdown');
    allDropdowns.forEach(function(dd) {
        dd.classList.remove('open');
    });
    
    // Toggle this dropdown
    if (!isOpen) {
        dropdown.classList.add('open');
    }
}

// Document ready handler
$(document).ready(function() {
    console.log('Document ready, jQuery version:', $.fn.jquery);
    console.log('Toggle buttons found:', $('.navbar-toggle').length);
    
    // Make sure toggle button works
    $('.navbar-toggle').off('click').on('click', function(e) {
        console.log('Toggle button clicked!');
        e.preventDefault();
        e.stopPropagation();
        toggleMobileMenu();
    });
    
    // Handle dropdown clicks
    $('.dropdown-toggle').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var dropdownId = $(this).closest('.dropdown').attr('id');
        if (dropdownId) {
            toggleDropdown(dropdownId);
        }
    });
    
    // Close menu when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.navbar').length) {
            var navbar = document.getElementById('bs-example-navbar-collapse-1');
            var button = document.querySelector('.navbar-toggle');
            
            if (navbar && navbar.classList.contains('in')) {
                toggleMobileMenu();
            }
            
            // Close all dropdowns
            $('.dropdown').removeClass('open');
        }
    });
    
    // Close mobile menu when clicking a navigation link
    $('.navbar-nav a[href]:not(.dropdown-toggle)').on('click', function() {
        var navbar = document.getElementById('bs-example-navbar-collapse-1');
        if (navbar && navbar.classList.contains('in')) {
            setTimeout(function() {
                toggleMobileMenu();
            }, 100);
        }
    });
    
    // Handle window resize
    $(window).on('resize', function() {
        if (window.innerWidth > 767) {
            // Desktop mode - ensure menu is visible and reset mobile styles
            var navbar = document.getElementById('bs-example-navbar-collapse-1');
            var button = document.querySelector('.navbar-toggle');
            
            if (navbar) {
                navbar.classList.remove('in');
                navbar.style.display = '';
            }
            
            if (button) {
                button.classList.add('collapsed');
                button.setAttribute('aria-expanded', 'false');
            }
        }
    });
});
</script>

</body>
</html>