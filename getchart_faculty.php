<?php

$fromYear = isset($_POST['fromYear']) ? $_POST['fromYear'] : '2022-2023';
$fromSemester = isset($_POST['fromSemester']) ? $_POST['fromSemester'] : 1;
$toYear = isset($_POST['toYear']) ? $_POST['toYear'] : '2022-2023';
$toSemester = isset($_POST['toSemester']) ? $_POST['toSemester'] : 1;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dmpcs_dashboard";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$charts = [];

// Helper function to execute a query and fetch all results
function fetchQueryResults($conn, $sql) {
    $result = $conn->query($sql);
    if ($result === FALSE) {
        die("Query failed: " . $conn->error);
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Helper function to build the SQL query
function buildQuery($select, $from, $joins, $where, $groupBy, $orderBy) {
    return "SELECT $select FROM $from $joins WHERE $where GROUP BY $groupBy ORDER BY $orderBy";
}

$timePeriodCondition = "
    CAST(SUBSTRING_INDEX(time_period.SchoolYear, '-', 1) AS UNSIGNED) BETWEEN CAST(SUBSTRING_INDEX('$fromYear', '-', 1) AS UNSIGNED) AND CAST(SUBSTRING_INDEX('$toYear', '-', 1) AS UNSIGNED)
    AND time_period.semester BETWEEN $fromSemester AND $toSemester
";

// Execute SQL query to retrieve the total count of faculty by rank
$sql1 = buildQuery(
    "rank_title.title AS facultyRank, SUM(COALESCE(faculty.count, 0)) AS rankCount",
    "faculty",
    "JOIN rank_title ON faculty.rankID = rank_title.rankID JOIN time_period ON faculty.timeID = time_period.timeID",
    $timePeriodCondition,
    "rank_title.title",
    "rankCount DESC"
);
$charts['ratioByRank'] = fetchQueryResults($conn, $sql1);

// Execute SQL query to retrieve the ratio of faculty by educational attainment
$sql2 = buildQuery(
    "educ_attainment.attainment AS educationalAttainment, SUM(COALESCE(faculty.count, 0)) AS facultyCount",
    "faculty",
    "JOIN educ_attainment ON faculty.educAttainmentID = educ_attainment.educAttainmentID JOIN time_period ON faculty.timeID = time_period.timeID",
    $timePeriodCondition,
    "educ_attainment.attainment",
    "facultyCount DESC"
);
$charts['ratioByEduc'] = fetchQueryResults($conn, $sql2);

// Execute SQL query to retrieve the total number of faculty per semester
$sql3 = buildQuery(
    "time_period.SchoolYear, time_period.semester, SUM(faculty.count) AS totalFaculty",
    "faculty",
    "JOIN time_period ON faculty.timeID = time_period.timeID",
    $timePeriodCondition,
    "time_period.SchoolYear, time_period.semester",
    "time_period.SchoolYear, time_period.semester"
);
$charts['numberOfTotalFaculty'] = fetchQueryResults($conn, $sql3);

// Execute SQL query to retrieve the total number of publications per year
$sql4 = buildQuery(
    "time_period.SchoolYear AS SchoolYear, time_period.semester AS semester, SUM(publication.count) AS totalPublications",
    "publication",
    "JOIN time_period ON publication.timeID = time_period.timeID",
    $timePeriodCondition,
    "time_period.SchoolYear, time_period.semester",
    "time_period.SchoolYear, time_period.semester"
);
$charts['numberOfPublications'] = fetchQueryResults($conn, $sql4);

// Execute SQL query to retrieve the population of faculty by rank per semester
$sql6 = buildQuery(
    "time_period.SchoolYear, time_period.semester, rank_title.title AS Rank, SUM(faculty.count) AS facultyCount",
    "faculty",
    "JOIN rank_title ON faculty.rankID = rank_title.rankID JOIN time_period ON faculty.timeID = time_period.timeID",
    $timePeriodCondition,
    "time_period.SchoolYear, time_period.semester, rank_title.title",
    "time_period.SchoolYear, time_period.semester, rank_title.title"
);
$charts['facultySembyRank'] = fetchQueryResults($conn, $sql6);

// Execute SQL query to retrieve the population of faculty by educational attainment
$sql7 = buildQuery(
    "time_period.SchoolYear, time_period.semester, educ_attainment.attainment AS EducationalAttainment, SUM(faculty.count) AS facultyCount",
    "faculty",
    "JOIN educ_attainment ON faculty.educAttainmentID = educ_attainment.educAttainmentID JOIN time_period ON faculty.timeID = time_period.timeID",
    $timePeriodCondition,
    "time_period.SchoolYear, time_period.semester, educ_attainment.attainment",
    "time_period.SchoolYear, time_period.semester, educ_attainment.attainment"
);
$charts['facultyByEducAttainment'] = fetchQueryResults($conn, $sql7);

echo json_encode($charts);
$conn->close();
?>
