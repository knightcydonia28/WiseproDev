<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>Contact Us</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Favicon -->
	<link rel="icon" type="image/png" sizes="56x56" href="assets/images/logo/initial_logo.png">
	<!-- bootstrap CSS -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css" media="all" />
	<!-- carousel CSS -->
	<link rel="stylesheet" href="assets/css/owl.carousel.min.css" type="text/css" media="all" />	
	<!-- responsive CSS -->
	<link rel="stylesheet" href="assets/css/responsive.css" type="text/css" media="all" />
	<!-- nivo-slider CSS -->
	<link rel="stylesheet" href="assets/css/nivo-slider.css" type="text/css" media="all" />
	<!-- animate CSS -->
	<link rel="stylesheet" href="assets/css/animate.css" type="text/css" media="all" />	
	<!-- animated-text CSS -->
	<link rel="stylesheet" href="assets/css/animated-text.css" type="text/css" media="all" />	
	<!-- font-awesome CSS -->
	<link type="text/css" rel="stylesheet" href="assets/fonts/font-awesome/css/font-awesome.min.css">
	<!-- font-flaticon CSS -->
	<link rel="stylesheet" href="assets/css/flaticon.css" type="text/css" media="all" />	
	<!-- theme-default CSS -->
	<link rel="stylesheet" href="assets/css/theme-default.css" type="text/css" media="all" />	
	<!-- meanmenu CSS -->
	<link rel="stylesheet" href="assets/css/meanmenu.min.css" type="text/css" media="all" />	
	<!-- Main Style CSS -->
	<link rel="stylesheet"  href="styles.css" type="text/css" media="all" />
	<!-- transitions CSS -->
	<link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css" media="all" />
	<!-- venobox CSS -->
	<link rel="stylesheet" href="venobox/venobox.css" type="text/css" media="all" />
	<!-- widget CSS -->
	<link rel="stylesheet" href="assets/css/widget.css" type="text/css" media="all" />
    <!--map CSS and script-->
    <link rel="stylesheet" href="assets/css/leaflet.css" />
    <script src="assets/js/leaflet.js"></script>
	<!-- modernizr js -->	
	<script type="text/javascript" src="assets/js/vendor/modernizr-3.5.0.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    
	
</head>
<body>

	<!--==================================================-->
	<!----- Start Main Menu Area ----->
	<!--==================================================-->
		<!-- DESKTOP TOP NAV BAR START -->
		<div id="header" class="techno_nav_manu d-md-none d-lg-block d-sm-none d-none">
			<div class="container">
				<div class="row">
					<div class="col-md-3">
						<div class="logo mt-3">
							<a class="logo_img" href="home.html" title="Wisepro">
								<img src="assets/images/logo/logo.png" alt="Wisepro Logo" height=60px/>
							</a>
						</div>
					</div>

					<div class="col-md-7">
						<nav class="techno_menu">
							<ul class="nav_scroll">
                                <li><a href="home.html">Home</a></li>
								<li><a href="home.html#services">Services</a></li>
								<li><a href="home.html#technology">Our Technologies</a></li>
								<li><a href="industries.html">Industries Served</a></li>
								<li><a href="#careers.html">Careers</a></li>
							</ul>
						</nav>
					</div>

					<div class="col-md-2">
						<div class="mt-3" float="right">
							<div class="donate-btn-header">
								<a class="dtbtn" href="contact.php">Contact Us</a>
						   </div>
						</div>
					</div>

				</div>
			</div>
		</div>
		<!-- DESKTOP TOP NAV BAR END -->

		<!-- MOBILE TOP NAV BAR END -->
		<div class="mobile-menu-area d-sm-block d-md-block d-lg-none ">
			<div class="mobile-menu">
					<nav class="techno_menu">
						<ul>
							<li><a href="#home">Home</a></li>
				            <li><a href="#about.html">About</a></li>
							<li><a href="#services.html">Services</a></li>
							<li><a href="#careers.html">Careers</a></li>
							<li><a href="#contact">Contact Us</a></li>
                            <li>
								<div class="donate-btn-header">
									<a class="dtbtn" href="#">Contact Us</a>	
                            	</div>
							</li>	
						</ul>
					</nav>
			</div>
		</div>
		<!-- MOBILE TOP NAV BAR END -->
	<!--==================================================-->
	<!----- End Main Menu Area ----->
	<!--==================================================-->
	

	<!--==================================================-->
	<!----- Start Contact Us Area ----->
	<!--==================================================-->
	<div class="main_contact_area style_three pt-160 pb-90">
		<div class="container">
			<div class="row align-items-center">


			<?php 
				//IF THE SUBMIT BUTTON IS PRESSED, LOAD BELOW
				 if (isset($_POST['form_submit'])) 
				{
					//echo '<h2>EMAIL FUNCTION START</h2> <br>';
					
					//User Form Entity Set
					$name    = $_POST['name'];
					$email   = $_POST['email'];
					$tel     = $_POST['tel'];
					$message = $_POST['message'];

					//Email To's Varables
					$email_to      = "tombaham@yahoo.com";
					$email_from    = "tombaham@yahoo.com";	
					$email_subject = "New Contact Form Submission";
				
					//Email Message Formatting
					$mainbody_message  = "<h2>New Contact Form Submission</h2>";
					$mainbody_message .= "<h3>Name: <u>" .  $name ."</u></h3>";
					$mainbody_message .= "<h3>Email Contact: <u>" . $email . "</u></h3>";
					$mainbody_message .= "<h3>Phone Contact: <u>" . $tel . "</u></h3>";
					$mainbody_message .= "<h3>Main Message:" . $message . "</u>";
					$mainbody_message .= "<p>Date/Time of Submission: </p>" . date("F j, Y, g:i a");
					$mainbody_message = wordwrap($mainbody_message, 140);

					//Email Subject/Header Attributes
					$headers = array('From' => 'administration@wisepro.com', 'Reply-To' => 'administration@wisepro.com', 'X-Mailer' => 'PHP/' . phpversion(), 'MIME-Version' => '1.0', 'Content-Type' => 'text/html; charset=utf8');

					$retval = mail( $email_to, $email_subject, $mainbody_message, $headers);
					
					if( $retval == true ) 
					{
						echo '<h2>Message sent successfully!</h2>';
					}
					else 
						{
							echo '<h2>Message could not be sent</h2>';
						}

				} 
				//IF SUBMIT BUTTON IS NOT HIT, LOAD BELOW
				else {
					echo "
					<div class=\"col-lg-6\">
					<div class=\"section_title text_left mb-50 mt-3\">
						<div class=\"section_sub_title uppercase mb-3\">
							<h6>Contact Info</h6>
						</div>
						<div class=\"section_main_title\">
							<h1>Get in Touch Today!</h1>
						</div>
					</div>
					<div class=\"contact_address\">
						<div class=\"contact_address_company mb-3\">
							<ul>
								<li><i class=\"fa fa-envelope-o\"></i><span><a href=\"#\">PLACEHOLDER_EMAIL</a></span></li>
								<li><i class=\"fa fa-mobile\"></i><span> (703)-766-8850</span></li>
								<li><i class=\"fa fa-map-marker\"></i> <span> 11211 Waples Mill Rd, Suite 220 Fairfax, VA 22030. USA</span></li>
							</ul>
						</div>
					</div>
					</div>
					<div class=\"col-lg-6\">
						<div class=\"contact_from\">
							<div class=\"contact_from_box\">
								<div class=\"contact_title pb-4\">
									<h3>Send Message </h3>
								</div>


								<form id=\"contact_form\" action=\"contact.php\" method=\"POST\">
									<div class=\"row\">
										<div class=\"col-lg-12\">
											<div class=\"form_box mb-30\">
												<input type=\"text\" name=\"name\" placeholder=\"Name\">
											</div>
										</div>

										<div class=\"col-lg-12\">
											<div class=\"form_box mb-30\">
												<input type=\"email\" name=\"email\" placeholder=\"Email Address\">
											</div>
										</div>

										<div class=\"col-lg-12\">
											<div class=\"form_box mb-30\">
												<input type=\"tel\" name=\"tel\" placeholder=\"Phone Number\">
											</div>
										</div>

										<div class=\"col-lg-12\">
											<div class=\"form_box mb-30\">
												<textarea name=\"message\" id=\"message\" cols=\"30\" rows=\"10\" placeholder=\"Write a Message\"></textarea>
											</div>
											<div class=\"quote_btn\">
												<input type=\"submit\" name=\"form_submit\">
												<!-- <button class=\"btn\" type=\"submit\">Send Message</button> -->
											</div>
										</div>
									</div>
								</form>
								<p class=\"form-message\"></p>
							</div>
						</div>
					</div>
				";
			}?>

			</div>
		</div>
	</div>
	<!--==================================================-->
	<!----- End Contact Us Area ----->
	<!--==================================================-->


	<!--==================================================-->
	<!----- Begin Leaflet Map Area ----->
	<!--==================================================-->
	<div id="map" style="width:100%; height:400px;position:relative;">
    <script>
        var map = L.map('map').setView([38.857088,-77.332315],15);
        
        L.tileLayer('https://api.maptiler.com/maps/pastel/{z}/{x}/{y}.png?key=jkLWcyqfEpOASeQNltFk', {
            attribution:'<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
        }).addTo(map);
        
        var marker = L.marker([38.857088,-77.332315]).addTo(map);
    </script>
	</div>
    <!--==================================================-->
	<!----- End Leaflet Map Area ----->
	<!--==================================================-->

  
	<!--==================================================-->
	<!----- Start Techno Footer Middle Area ----->
	<!--==================================================-->
	<div class="footer footer-middle pt-30" style="background-image:url(assets/images/call-bg.png)" > 
		<div class="container">
			<div class="row">
				
				<!-- Company Logo & Description -->
				<div class="col-lg-6 col-md-6 col-sm-12">
					<div class="widget widgets-company-info">
						<div class="company-info-desc">
							<div class="logo mt-3">
								<a class="logo_img" href="home.html" title="Wisepro">
									<img src="assets/images/logo/logo2.png" alt="Wisepro Logo" height=60px/>
								</a>
							</div>
							<br>
							<p>Wisepro is a pioneer in providing innovative business and Information Technology services to Fortune 500 companies, U.S. government agencies, and global development and financial organizations.
							</p>
						</div>
					</div>					
				</div>

				<!--Company Address Footer-->
				<div class="col-lg-6 col-md-6 col-sm-12">
					<div class="widget widgets-company-info">
						<br>
						<h3 class="widget-title pb-4">Company Address</h3>
						<div class="footer-social-info">
							<p><span>Address :</span> 11211 Waples Mill Rd, Suite 220
								Fairfax, VA 22030. USA</p>
						</div>
						<div class="footer-social-info">
							<p><span>Phone : </span>(703)-766-8850</p>
						</div>						
					</div>					
				</div>				
			</div>

			<!-- Footer of the Footer -->
			<div class="row footer-bottom mt-25 pt-3 pb-1">
				<div class="col-lg-6 col-md-6">
					<div class="footer-bottom-content">
						<div class="footer-bottom-content-copy">
							<p>Copyright © 2022 Wisetek Providers, Inc. All rights reserved.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>		
	<!--==================================================-->
	<!----- End Techno Footer Middle Area ----->
	<!--==================================================-->
	
	<!-- jquery js -->	
	<script type="text/javascript" src="assets/js/vendor/jquery-3.2.1.min.js"></script>
	<!-- bootstrap js -->	
	<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
	<!-- carousel js -->
	<script type="text/javascript" src="assets/js/owl.carousel.min.js"></script>
	<!-- counterup js -->
	<script type="text/javascript" src="assets/js/jquery.counterup.min.js"></script>
	<!-- waypoints js -->
	<script type="text/javascript" src="assets/js/waypoints.min.js"></script>
	<!-- wow js -->
	<script type="text/javascript" src="assets/js/wow.js"></script>
	<!-- imagesloaded js -->
	<script type="text/javascript" src="assets/js/imagesloaded.pkgd.min.js"></script>
	<!-- venobox js -->
	<script type="text/javascript" src="venobox/venobox.js"></script>
	<!-- ajax mail js -->
	<script type="text/javascript" src="assets/ajax-mail.js"></script>
	<!--  testimonial js -->	
	<script type="text/javascript" src="assets/js/testimonial.js"></script>
	<!--  animated-text js -->	
	<script type="text/javascript" src="assets/js/animated-text.js"></script>
	<!-- venobox min js -->
	<script type="text/javascript" src="venobox/venobox.min.js"></script>
	<!-- isotope js -->
	<script type="text/javascript" src="assets/js/isotope.pkgd.min.js"></script>
	<!-- jquery nivo slider pack js -->
	<script type="text/javascript" src="assets/js/jquery.nivo.slider.pack.js"></script>
	<!-- jquery meanmenu js -->	
	<script type="text/javascript" src="assets/js/jquery.meanmenu.js"></script>
	<!-- jquery scrollup js -->	
	<script type="text/javascript" src="assets/js/jquery.scrollUp.js"></script>
	<!-- theme js -->	
	<script type="text/javascript" src="assets/js/theme.js"></script>
		<!-- jquery js -->	
</body>
</html>