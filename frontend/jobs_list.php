<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Employment Form</title>
        <style>
            .job_box {
                border-style: solid;
            }
        </style>
    </head>
    <body>
        <?php
            include("database.php");
            $job_status = "active";
            $stmt = $DBConnect->prepare("SELECT job_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date FROM jobs WHERE job_status = ?");
            $stmt->bind_param("s", $job_status); 
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_job_id, $retrieved_job_title, $retrieved_job_type, $retrieved_job_location, $retrieved_job_description, $retrieved_preferred_skills, $retrieved_required_skills, $retrieved_job_posted_date);
            if ($stmt->num_rows > 0) {
                while($stmt->fetch()) {
                    echo 
                    "<div class=\"job_box\">
                        <p><b>Job Id:</b>  $retrieved_job_id</p>
                        <p><b>Job Title:</b>  $retrieved_job_title</p>
                        <p><b>Job Type:</b>  $retrieved_job_type</p>
                        <p><b>Job Location:</b>  $retrieved_job_location</p>
                        <p><b>Job Description:</b> <br />$retrieved_job_description</p>
                        <p><b>Preferred Skills:</b>  $retrieved_preferred_skills</p>
                        <p><b>Required Skills:</b>  $retrieved_required_skills</p>
                    </div>
                    <br/>";
                }
            }
            else {
                echo "<p>Currently no positions available</p>";
            }
            $stmt->close();
            $DBConnect->close();
        ?> 
    </body>
</html>