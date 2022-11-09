<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>Careers</title>
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
                        
					<?php

						if (isset($_POST['jobChoice_submit'])) 
						{
							//Post Value Set
							$jobChoice = $_POST['jobChoice_submit'];

							include("database.php");
							$job_status = "active";
							$stmt = $DBConnect->prepare("SELECT job_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date FROM jobs WHERE job_id = ?");
							$stmt->bind_param("i", $jobChoice); 
							$stmt->execute();
							$stmt->store_result();
							$stmt->bind_result($retrieved_job_id, $retrieved_job_title, $retrieved_job_type, $retrieved_job_location, $retrieved_job_description, $retrieved_preferred_skills, $retrieved_required_skills, $retrieved_job_posted_date);
							$stmt->fetch();

							$myJobArray = explode("•", $retrieved_job_description);
							
							//JOB DESCRIPTION ARRAY DEBUG
							//echo print_r($myJobArray); 
							
							echo 
								"
								<div class=\"col-lg-12 col-md-12 col-sm-12\">
									<div class=\"service_style_two pt-30 pl-30 pr-30 mb-5\">

										<form id=\"contact_form\" action=\"contact.php\" method=\"POST\">
											<div class=\"service_style_three_title pb-3\">
												<h4>$retrieved_job_title: $retrieved_job_location</h4>
											</div>
											<div class=\"service_style_three_text\">
												<p>$reteived_job_title</p>
												<p>Job ID: #$retrieved_job_id</p>
												<p>Time Type: $retrieved_job_type</p>
												<p>Job Location: $retrieved_job_location</p>
												<p>Required Skills: $retrieved_required_skills</p>
												<p>Preffered Skills: $retrieved_preferred_skills</p>
												<p>Full Description: <br> "; 
												
												foreach ($myJobArray as $qualLine) {
													echo "•$qualLine <br>";
												} 					
													echo "</p>
											</div>
											<div class=\"col-lg-12\">
												<div class=\"quote_btn\">
													<button class=\"btn\" type=\"submit\" name=\"job_submit\">Apply Now!</button>
												</div>
											</div>
										</form>

									</div>
								</div>
								";

							$stmt->close();
							$DBConnect->close();
						} 
						else 
						{
							include("database.php");
							$job_status = "active";
							$stmt = $DBConnect->prepare("SELECT job_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date FROM jobs WHERE job_status = ?");
							$stmt->bind_param("s", $job_status); 
							$stmt->execute();
							$stmt->store_result();
							$stmt->bind_result($retrieved_job_id, $retrieved_job_title, $retrieved_job_type, $retrieved_job_location, $retrieved_job_description, $retrieved_preferred_skills, $retrieved_required_skills, $retrieved_job_posted_date);
							if ($stmt->num_rows > 0) 
							{
								while($stmt->fetch()) {
									echo 
									"
									<div class=\"col-lg-4 col-md-6 col-sm-12\">
										<div class=\"service_style_three pt-60 pl-30 pr-30 mb-5 text_center\">
											<div class=\"service_style_three_title pb-3\">
												<h4>$retrieved_job_title</h4>
											</div>
											<div class=\"service_style_three_text\">
												<p>$retrieved_job_id</p>
												<p>$retrieved_job_type</p>
												<p>$retrieved_job_location</p>
												<p>$retrieved_required_skills</p>
											</div>
											<form id=\"jobForm_submit\" action=\"careers.php\" method=\"POST\">
												<div class=\"service_style_three_bt_icon pt-30\">

													<button type=\"submit\" name=\"jobChoice_submit\" value=\"$retrieved_job_id\"><i class=\"fa fa-long-arrow-right\"></i></button>

												</div>
											</form>
										</div>
									</div>
									";
								}
							}
							else 
							{
								echo "<p>Currently no positions available</p>";
							}
							$stmt->close();
							$DBConnect->close();
						}
					?>	

					<br>
					<br>

					<!-- JOB DIV STRUCTURE AND STYLE

                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="service_style_three pt-60 pl-30 pr-30 mb-5 text_center">
                                <div class="service_style_three_title pb-3">
                                    <h4>Job Title</h4>
                                </div>
                                <div class="service_style_three_text">
                                    <p>Job Id</p>
                                    <p>Job Type</p>
                                    <p>Job ID</p>
                                    <p>Job Location</p>
                                </div>
                                <div class="service_style_three_bt_icon pt-30">
                                    <button type="submit" name="$retrieved_job_id"><i class="fa fa-long-arrow-right"></i></button>
                                </div>
                            </div>
                        </div>

					-->
                   
                    
                            

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
