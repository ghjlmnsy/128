<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dmpcs_dashboard";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function redirectToStudentsPage() {
    header("Location: students.php");
    exit();
}

function fetchID($sql, $param, $type)
{
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($type, $param);
        $stmt->execute();
        $stmt->bind_result($id);
        if ($stmt->fetch()) {
            $stmt->close();
            return $id;
        }
        $stmt->close();
    }
    return null;
}

function executeSQL($sql, $types, ...$params)
{
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            echo "<script type='text/javascript'>
                    alert('Error: " . $stmt->error . "');
                    window.location.href = 'admin.php';
                  </script>";
        }
        $stmt->close();
    }
    return false;
}

// Add academic year and semester function
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_acad'])) {
        $newSchoolYear = $_POST['newSchoolYear'];
        $semester = $_POST['semester'];

        // Validate the academic year format
        if (!preg_match('/^\d{4}-\d{4}$/', $newSchoolYear)) {
            echo "<script type='text/javascript'>
                    alert('Invalid academic year format. Please use YYYY-YYYY.');
                    window.location.href = 'admin.php';
                  </script>";
            exit();
        }

        // Extract the last two digits of the ending year
        $parts = explode('-', $newSchoolYear);
        $lastTwoDigits = substr($parts[1], -2);  // Get last two digits of the end year
        $timeID = $lastTwoDigits . '-' . $semester;  // Concatenate to form timeID

        // Check if the timeID already exists to avoid duplicates
        $checkSql = "SELECT * FROM time_period WHERE timeID = ?";
        if (fetchID($checkSql, $timeID, "s")) {
            echo "<script type='text/javascript'>
                    alert('Academic year and semester combination already exists.');
                    window.location.href = 'admin.php';
                </script>";
        } else {
            // Insert new academic year, semester, and generated timeID
            $sql = "INSERT INTO time_period (timeID, SchoolYear, semester) VALUES (?, ?, ?)";
            if (executeSQL($sql, "ssi", $timeID, $newSchoolYear, $semester)) {
                echo "<script type='text/javascript'>
                        alert('Added successfully.');
                        window.location.href = 'admin.php';
                      </script>";
            }
        }
    }
}

// Delete academic year function
if (isset($_POST['delete_acad'])) {
    // Get the selected option
    $selected_option = $_POST['existingSY'];

    // Separate the SchoolYear and semester using explode
    list($schoolYear, $semester) = array_map('trim', explode(",", $selected_option));

    // Prepare the SQL statement for deletion
    $sql = "DELETE FROM time_period WHERE SchoolYear = ? AND semester = ?";
    if (executeSQL($sql, "si", $schoolYear, $semester)) {
        echo "<script type='text/javascript'>
                alert('Deleted successfully.');
                window.location.href = 'admin.php';
              </script>";
    }
}

// Add degree program function
if (isset($_POST['add_degree'])) {
    $degprogID = $_POST['degprogID'];
    $name = $_POST['name'];

    $sql = "INSERT INTO deg_prog (degprogID, name) VALUES (?, ?)";
    if (executeSQL($sql, "ss", $degprogID, $name)) {
        echo "<script type='text/javascript'>
                alert('Added successfully.');
                window.location.href = 'admin.php';
              </script>";
    }
}

// Delete degree program function
if (isset($_POST['delete_degree']) && isset($_POST['existingSY'])) {
    $degprogID = $conn->real_escape_string($_POST['existingSY']);

    $deleteSql = "DELETE FROM deg_prog WHERE degprogID = ?";
    if (executeSQL($deleteSql, "s", $degprogID)) {
        echo "<script type='text/javascript'>
                alert('Deleted successfully.');
                window.location.href = 'admin.php';
              </script>";
    }
}

// Add achievement function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_achievement') {
    $awardType = trim($_POST['awardType'] ?? '');
    $degprogID = trim($_POST['degprogID'] ?? '');
    $count = trim($_POST['count'] ?? '');

    list($yearLevel, $degprogID, $SchoolYear, $semester) = array_map('trim', explode(' ', $degprogID));

    if (empty($awardType) || empty($degprogID) || empty($yearLevel) || empty($SchoolYear) || empty($semester) || empty($count)) {
        echo "<script type='text/javascript'>
                alert('All fields are required.');
                window.location.href = 'admin.php';
            </script>";
        return;
    }

    $awardtypeID = fetchID("SELECT awardtypeID FROM award_type WHERE awardType = ?", $awardType, "s");
    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", $SchoolYear, $semester, "ss");
    $degID = fetchID("SELECT degID FROM college_degree WHERE yearLevel = ? AND degprogID = ? AND timeID = ?", $yearLevel, $degprogID, $timeID, "iss");

    if ($awardtypeID && $timeID && $degID) {
        $sql = "INSERT INTO student_awards (awardtypeID, degID, count) VALUES (?, ?, ?)";
        if (executeSQL($sql, "ssi", $awardtypeID, $degID, $count)) {
            echo "<script type='text/javascript'>
                    alert('Added successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        }
    }
}

// Delete achievement function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_achievement') {
    $achievement = $_POST['existingAchievements'];

    list($awardType, $yearLevel, $degprogID, $SchoolYear, $semester) = array_map('trim', explode(',', $achievement));

    $awardtypeID = fetchID("SELECT awardtypeID FROM award_type WHERE awardType = ?", $awardType, "s");
    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", $SchoolYear, $semester, "ss");
    $degID = fetchID("SELECT degID FROM college_degree WHERE yearLevel = ? AND degprogID = ? AND timeID = ?", $yearLevel, $degprogID, $timeID, "sss");

    if ($awardtypeID && $timeID && $degID) {
        $sql = "DELETE FROM student_awards WHERE awardtypeID = ? AND degID = ?";
        if (executeSQL($sql, "ii", $awardtypeID, $degID)) {
            echo "<script type='text/javascript'>
                    alert('Deleted successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        }
    }
}

// Add degree program information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_deginfo') {
    $yearLevel = $_POST['yearLevel'];
    $degprogID = $_POST['degprogID'];
    $SchoolYear = $_POST['SchoolYear'];
    $semester = $_POST['semester'];
    $count = $_POST['count'];

    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", $SchoolYear, $semester, "ss");
    $degID = $yearLevel . $degprogID . $timeID;

    if ($timeID) {
        $sql = "INSERT INTO college_degree (degID, yearLevel, degprogID, timeID, count) VALUES (?, ?, ?, ?, ?)";
        if (executeSQL($sql, "sssss", $degID, $yearLevel, $degprogID, $timeID, $count)) {
            echo "<script type='text/javascript'>
                    alert('Added successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        }
    }
}

// Add event function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_event') {
    $eventName = trim($_POST['eventName'] ?? '');
    $schoolYear = trim($_POST['SchoolYear'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $count = trim($_POST['count'] ?? '');

    if (empty($eventName) || empty($schoolYear) || empty($semester) || empty($count)) {
        echo "<script type='text/javascript'>
                alert('All fields are required.');
                window.location.href = 'admin.php';
            </script>";
        return;
    }

    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", $schoolYear, $semester, "ss");

    if ($timeID) {
        $sql = "INSERT INTO event (eventName, timeID, count) VALUES (?, ?, ?)";
        if (executeSQL($sql, "ssi", $eventName, $timeID, $count)) {
            echo "<script type='text/javascript'>
                    alert('Added successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        }
    }
}

// Delete event function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_event') {
    $eventName = $_POST['existingEvents'];

    $sql = "DELETE FROM event WHERE eventName = ?";
    if (executeSQL($sql, "s", $eventName)) {
        echo "<script type='text/javascript'>
                alert('Deleted successfully.');
                window.location.href = 'admin.php';
              </script>";
    }
}

// Add publication function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_publication') {
    $title = $_POST['title'];
    $schoolYear = $_POST['SchoolYear'];
    $semester = $_POST['semester'];
    $count = $_POST['count'];

    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", $schoolYear, $semester, "ss");

    if ($timeID) {
        $sql = "INSERT INTO publication (title, timeID, count) VALUES (?, ?, ?)";
        if (executeSQL($sql, "ssi", $title, $timeID, $count)) {
            echo "<script type='text/javascript'>
                    alert('Added successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        }
    }
}

// Delete publication function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_publication') {
    $title = $_POST['existingPub'];

    if (is_array($title)) {
        $title = $title[0];
    }

    $sql = "DELETE FROM publication WHERE title = ?";
    if (executeSQL($sql, "s", $title)) {
        echo "<script type='text/javascript'>
                alert('Deleted successfully.');
                window.location.href = 'admin.php';
              </script>";
    }
}

// Add faculty information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_faculty_info') {
    $rankTitle = $_POST['rankTitle'] ?? '';
    $educAttainmentDesc = $_POST['educAttainmentDesc'] ?? '';
    $SchoolYear = $_POST['SchoolYear'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $count = $_POST['count'] ?? 0;

    if (empty($rankTitle) || empty($educAttainmentDesc) || empty($SchoolYear) || empty($semester) || empty($count)) {
        echo "<script>
                alert('All fields are required');
                window.location.href = 'admin.php'; 
            </script>";
        return;
    }

    $rankID = fetchID("SELECT rankID FROM rank_title WHERE title = ?", $rankTitle, "s");
    $educAttainmentID = fetchID("SELECT educAttainmentID FROM educ_attainment WHERE attainment = ?", $educAttainmentDesc, "s");
    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", $SchoolYear, $semester, "ss");

    if ($rankID && $educAttainmentID && $timeID) {
        $sql = "INSERT INTO faculty (rankID, educAttainmentID, timeID, count) VALUES (?, ?, ?, ?)";
        if (executeSQL($sql, "sssi", $rankID, $educAttainmentID, $timeID, $count)) {
            echo "<script type='text/javascript'>
                    alert('Added successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        }
    }
}

// Delete faculty information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_faculty_info') {
    $facultyInfo = $_POST['existingfacultyInfo'];

    list($title, $attainment, $SchoolYear, $semester) = array_map('trim', explode(',', $facultyInfo));

    $rankID = fetchID("SELECT rankID FROM rank_title WHERE title = ?", $title, "s");
    $educAttainmentID = fetchID("SELECT educAttainmentID FROM educ_attainment WHERE attainment = ?", $attainment, "s");
    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", $SchoolYear, $semester, "ss");

    if ($rankID && $educAttainmentID && $timeID) {
        $sql = "DELETE FROM faculty WHERE rankID = ? AND educAttainmentID = ? AND timeID = ?";
        if (executeSQL($sql, "sss", $rankID, $educAttainmentID, $timeID)) {
            echo "<script type='text/javascript'>
                    alert('Deleted successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        }
    }
}

$conn->close();
?>
