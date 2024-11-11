<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dmpcs_dashboard";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch distinct data
function fetchDistinctData($conn, $column, $table) {
    $sql = "SELECT DISTINCT $column FROM $table";
    $result = $conn->query($sql);
    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row[$column];
        }
    }
    return $data;
}

// Function to fetch detailed data
function fetchDetailedData($conn, $sql, $format) {
    $result = $conn->query($sql);
    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $formatted = vsprintf($format, $row);
            $data[] = $formatted;
        }
    }
    return $data;
}

// Fetch data for academic years
$academic_years = fetchDistinctData($conn, 'SchoolYear', 'time_period');

// Fetch data for semesters
$semesters = fetchDistinctData($conn, 'Semester', 'time_period');

// Fetch data for yearLevel
$yrLevel = fetchDistinctData($conn, 'yearLevel', 'college_degree');

// Fetch data for degree programs, school year, and year level
$sql = "SELECT cd.yearLevel, cd.degprogID, tp.SchoolYear, tp.semester
        FROM college_degree cd
        JOIN time_period tp ON cd.timeID = tp.timeID";
$degree_programs = fetchDetailedData($conn, $sql, '%s, %s, %s, %s');

// Fetch data for degree programs
$degree_prog = fetchDistinctData($conn, 'degprogID', 'deg_prog');

// Fetch data for award types
$awardtypes = fetchDistinctData($conn, 'awardType', 'award_type');

// Fetch data for ranks
$ranks = fetchDistinctData($conn, 'title', 'rank_title');

// Fetch data for educational attainments
$educational_attainments = fetchDistinctData($conn, 'attainment', 'educ_attainment');

// Fetch for existing academic year and semester
$sql = "SELECT SchoolYear, semester FROM time_period";
$time_period_info = fetchDetailedData($conn, $sql, '%s, %s');

// Fetch existing degree programs
$deg_programs = fetchDistinctData($conn, 'degprogID', 'deg_prog');

// Fetch data for existing achievements
$sql = "SELECT at.awardType, cd.yearLevel, cd.degprogID, tp.SchoolYear, tp.semester
        FROM student_awards sa
        JOIN award_type at ON sa.awardTypeID = at.awardTypeID
        JOIN college_degree cd ON sa.degID = cd.degID
        JOIN time_period tp ON cd.timeID = tp.timeID";
$achievements = fetchDetailedData($conn, $sql, '%s, %s, %s, %s, %s');

// Fetch data for existing degree program information
$sql = "SELECT cd.yearLevel, cd.degprogID, tp.SchoolYear, tp.semester
        FROM college_degree cd
        JOIN time_period tp ON cd.timeID = tp.timeID";
$degree_exist = fetchDetailedData($conn, $sql, '%s, %s, %s, %s');

// Fetch existing events
$event_name = fetchDistinctData($conn, 'eventName', 'event');

// Fetch existing publications
$pub_title = fetchDistinctData($conn, 'title', 'publication');

// Fetch the existing faculty information
$sql = "SELECT rt.title, ea.attainment, tp.SchoolYear, tp.semester
        FROM faculty f
        JOIN rank_title rt ON f.rankID = rt.rankID
        JOIN educ_attainment ea ON f.educAttainmentID = ea.educAttainmentID
        JOIN time_period tp ON f.timeID = tp.timeID";
$faculty_info = fetchDetailedData($conn, $sql, '%s, %s, %s, %s');

// Close connection
$conn->close();
?>
