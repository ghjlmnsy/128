<?php

function getPostParam($param, $default) {
    return isset($_POST[$param]) ? $_POST[$param] : $default;
}

$fromYear = getPostParam('fromYear', '2022-2023');
$fromSemester = getPostParam('fromSemester', 1);
$toYear = getPostParam('toYear', '2022-2023');
$toSemester = getPostParam('toSemester', 1);

function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dmpcs_dashboard";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function getEventData($conn, $fromYear, $fromSemester, $toYear, $toSemester) {
    $sql = "SELECT event.eventName, event.count 
            FROM event 
            INNER JOIN time_period ON event.timeId = time_period.timeId
            WHERE CAST(SUBSTRING_INDEX(time_period.SchoolYear, '-', 1) AS UNSIGNED) BETWEEN CAST(SUBSTRING_INDEX('$fromYear', '-', 1) AS UNSIGNED) AND CAST(SUBSTRING_INDEX('$toYear', '-', 1) AS UNSIGNED)
            AND time_period.semester BETWEEN $fromSemester AND $toSemester";
    $result = $conn->query($sql);

    if ($result === FALSE) {
        die("Query failed: " . $conn->error);
    }

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    return $events;
}

$conn = getDbConnection();
$events = getEventData($conn, $fromYear, $fromSemester, $toYear, $toSemester);
$conn->close();

$data = [
    'events' => $events
];

echo json_encode($data);
