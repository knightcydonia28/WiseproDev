<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>Apply Now</title>
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

	<!-- modernizr js -->	
	<script type="text/javascript" src="assets/js/vendor/modernizr-3.5.0.min.js"></script>
	
</head>
<body>
    <div id="page-container">
		<div id="content-wrap">
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
                                        <a class="dtbtn" href="#">Contact Us</a>
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

            <br>
            <br>

            <!--==================================================-->
            <!----- Start TOP Area ----->
            <!--==================================================-->
            <div class="about_area bg_color1 pt-35 pb-10">
                <div class="container">
                    <div class="row">

                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-6">
                            <div class="section_title text_center mb-40 mt-3">

                                <div class="section_main_title">
                                    <h1>Careers</h1>
                                </div>
                                
                                <div class="section_main_title pt-4">
                                    <h3>Come and join the team today!</h3>
                                </div>
                            </div>

                        </div>
                        
                    </div>
                </div>	
            </div>
            <!--==================================================-->
            <!----- Start TOP Area ----->
            <!--==================================================-->



            <!--==================================================-->
            <!----- Start MIDDLE Area ----->
            <!--==================================================-->
            <div class="feature_area bg_color2 pt-40 pb-35">
                <div class="container">
                    <div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12">
							<div class="service_style_two pt-30 pl-30 pr-30 mb-5">
                        
								<?php
									if (isset($_POST['jobApp_submit'])) 
									{
										//Post Value Set
										$jobChoice = $_POST['jobApp_submit'];

										include("database.php");
										$job_status = "active";
										$stmt = $DBConnect->prepare("SELECT job_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date FROM jobs WHERE job_id = ?");
										$stmt->bind_param("i", $jobChoice); 
										$stmt->execute();
										$stmt->store_result();
										$stmt->bind_result($retrieved_job_id, $retrieved_job_title, $retrieved_job_type, $retrieved_job_location, $retrieved_job_description, $retrieved_preferred_skills, $retrieved_required_skills, $retrieved_job_posted_date);
										$stmt->fetch();
				
										echo 
											"			
											<div class=\"service_style_three_title text-center\">
													<h3>$retrieved_job_title: $retrieved_job_location - Job ID:#$retrieved_job_id</h3>
													<br>
											</div>

											<form id=\"appEmail_form\" action=\"jobAppForm.php\" method=\"POST\">	
												
												<div class=\"col-lg-12 col-md-12\">

													<div class =\"row text-center\">
														<div class=\"col-lg-12\"> 
															<h4>Please upload your resume below</h4> 
														</div>
														<div class=\"col-12\"> 
															<label for=\"myfile\"><b>Select a file:</b></label>
															<input type=\"file\" id=\"myfile\" name=\"myfile\"> 
														</div>
													</div>
													
													<div class=\"col-lg-12 pt-5\">
													
														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"user_first_name\"><b>First Name:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"text\" id=\"user_first_name\" name=\"user_first_name\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your first name has letters, dashes, apostrophes and whitespaces only\" size=\"40\" required autofocus /><br /><br />
															</div>
														</div>

														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"user_last_name\"><b>Last Name:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"text\" id=\"user_last_name\" name=\"user_last_name\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your last name has letters, dashes, apostrophes and whitespaces only\" size=\"40\" required /><br /><br />
															</div>
														</div>

														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"user_address_1\"><b>Address Line 1:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"text\" id=\"user_address_1\" name=\"user_address_1\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your address has, dashes, apostrophes and whitespaces only\" size=\"40\" required /><br /><br />
															</div>
														</div>

														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"user_address_2\"><b>Address Line 2:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"text\" id=\"user_address_2\" name=\"user_address_2\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your address has, dashes, apostrophes and whitespaces only\" size=\"40\" /><br /><br />
															</div>
														</div>

														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"city\"><b>City:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"text\" id=\"user_city\" name=\"user_city\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your address has, dashes, apostrophes and whitespaces only\" size=\"40\" required /><br /><br />
															</div>
														</div>

														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"state\"><b>State:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"text\" id=\"user_state\" name=\"user_state\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your address has, dashes, apostrophes and whitespaces only\" size=\"40\" required /><br /><br />
															</div>
														</div>
														
														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"postal_code\"><b>Postal Code:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"text\" id=\"user_postal_code\" name=\"user_postal_code\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your address has, dashes, apostrophes and whitespaces only\" size=\"40\" required /><br /><br />
															</div>
														</div>

														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"email\"><b>Email Address:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"email\" id=\"user_email\" name=\"user_email\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your address has, dashes, apostrophes and whitespaces only\" size=\"40\" required /><br /><br />
															</div>
														</div>

														<div class=\"row\">
															<div class=\"col-lg-4 text-align-right\">
																<label for=\"phone\"><b>Phone Number:</b></label>
															</div>
															<div class=\"col-lg-5\">
																<input type=\"tel\" id=\"user_phone_number\" name=\"user_phone_number\" placeholder=\"123-456-7890\" title=\"Please ensure that your address has, dashes, apostrophes and whitespaces only\" size=\"40\" required /><br /><br />
															</div>
														</div>	

													</div>	
												</div>

												<div class=\"col-12\">
													<div class=\"quote_btn float-right\">
														<button class=\"btn\" type=\"submit\" name=\"appEmail_submit\">Submit Application</button>
													</div>
												</div>

											</form>
											";

										$stmt->close();
										$DBConnect->close();
									} 
									else 
									{
										echo "<h3>You must submit for a job submission, please go back.</h3>";
									}	
								?>	
							</div>
						</div>

						<br>
						<br>
                   
                    
                            

                    </div>
                </div>
            </div>
            <!--==================================================-->
            <!----- Start MIDDLE Area ----->
            <!--==================================================-->

        </div>
    </div>
	


	<!--==================================================-->
	<!----- Start FOOTER Area ----->
	<!--==================================================-->
	<div class="footer footer-middle pt-40" style="background-image:url(assets/images/call-bg.png)" > 
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
	<!----- End FOOTER Area ----->
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
	<script type="text/javascript" src="assets/js/ajax-mail.js"></script>
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
