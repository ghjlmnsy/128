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

function fetchTimeID($schoolYear, $semester) {
    global $conn;
    $sql = "SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $schoolYear, $semester);
        $stmt->execute();
        $stmt->bind_result($timeID);
        if ($stmt->fetch()) {
            return $timeID;
        }
        $stmt->close();
    }
    return null;
}

// Add academic year and semester function
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_acad'])) {
        $newSchoolYear = $_POST['newSchoolYear'];
        $semester = $_POST['semester'];

        // Extract the last two digits of the ending year
        $parts = explode('-', $newSchoolYear);
        $lastTwoDigits = substr($parts[1], -2);  // Get last two digits of the end year
        $timeID = $lastTwoDigits . '-' . $semester;  // Concatenate to form timeID

        // Check if the timeID already exists to avoid duplicates
        $checkSql = "SELECT * FROM time_period WHERE timeID = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $timeID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            echo "<script type='text/javascript'>
                    alert('Academic year and semester combination already exists.');
                    window.location.href = 'admin.php';
                </script>";
        } else {
            // Insert new academic year, semester, and generated timeID
            $sql = "INSERT INTO time_period (timeID, SchoolYear, semester) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssi", $timeID, $newSchoolYear, $semester);
                if ($stmt->execute()) {
                    echo "<script type='text/javascript'>
                            alert('Added successfully.');
                            window.location.href = 'admin.php';
                          </script>";
                } else {
                    echo "<script type='text/javascript'>
                            alert('Error adding: " . $stmt->error . "');
                            window.location.href = 'admin.php';
                        </script>";
                }
                $stmt->close();
                             
            }
        }
        $checkStmt->close();
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
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
        exit;
    }    
    
    // Bind parameters to the prepared statement
    $stmt->bind_param("si", $schoolYear, $semester);

    // Execute the deletion query
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
                alert('Deleted successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script type='text/javascript'>
                    alert('Error deleting: " . $stmt->error . "');
                    window.location.href = 'admin.php';
                </script>";
    }
    // Close the statement
    $stmt->close();
}


// Add degree program function
if (isset($_POST['add_degree'])) {
    $degprogID = $_POST['degprogID'];
    $name = $_POST['name'];

    $sql = "INSERT INTO deg_prog (degprogID, name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $degprogID, $name);

    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
                alert('Added successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script type='text/javascript'>
                    alert('Error adding: " . $stmt->error . "');
                    window.location.href = 'admin.php';
            </script>";
    }
    $stmt->close();
}

// Delete degree program function
if (isset($_POST['delete_degree']) && isset($_POST['existingSY'])) {
    // Sanitize the input to prevent SQL Injection
    $degprogID = $conn->real_escape_string($_POST['existingSY']);
    
    // SQL to delete a degree program
    $deleteSql = "DELETE FROM deg_prog WHERE degprogID = '$degprogID'";
    
    if ($conn->query($deleteSql) === TRUE) {
        echo "<script type='text/javascript'>
                alert('Deleted successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script type='text/javascript'>
            alert('Error deleting: " . $conn->error . "');
            window.location.href = 'admin.php';
        </script>";
    }
}

// Add achievement function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_achievement') {
    // Retrieve form data and trim whitespace
    $awardType = isset($_POST['awardType']) ? trim($_POST['awardType']) : '';
    $degprogID = isset($_POST['degprogID']) ? trim($_POST['degprogID']) : '';
    $count = isset($_POST['count']) ? trim($_POST['count']) : '';

    // Use PHP explode function to separate yearLevel, degprogID, SchoolYear, semester
    list($yearLevel, $degprogID, $SchoolYear, $semester) = array_map('trim', explode(' ', $degprogID));

    // Ensure required fields are not empty
    if (empty($awardType) || empty($degprogID) || empty($yearLevel) || empty($SchoolYear) || empty($semester) || empty($count)) {
        echo "<script type='text/javascript'>
                alert('All fields are required.');
                window.location.href = 'admin.php';
            </script>";
        return;
    }

    // Step 1: Fetch awardtypeID based on award type
    $stmt = $conn->prepare("SELECT awardtypeID FROM award_type WHERE awardType = ?");
    $stmt->bind_param("s", $awardType);
    $stmt->execute();
    $stmt->bind_result($awardtypeID);
    if ($stmt->fetch()) {
        // Success: fetched awardtypeID
    } else {
        echo "No valid awardtypeID found for the given award type: $awardType.";
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 2: Select timeID column in time_period table that matches the given SchoolYear and semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    if ($stmt->fetch()) {
        // Success: fetched timeID
    } else {
        echo "No valid timeID found for the given SchoolYear: $SchoolYear and semester: $semester.";
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 3: Select degID column in college_degree table that matches the given yearLevel, degprogID, timeID
    $stmt = $conn->prepare("SELECT degID FROM college_degree WHERE yearLevel = ? AND degprogID = ? AND timeID = ?");
    $stmt->bind_param("iss", $yearLevel, $degprogID, $timeID);
    $stmt->execute();
    $stmt->bind_result($degID);
    if ($stmt->fetch()) {
        // Success: fetched degID
    } else {
        echo "No valid degID found for the given yearLevel: $yearLevel, degprogID: $degprogID, and timeID: $timeID.";
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 4: Insert the new achievement information into the student_awards table
    $stmt = $conn->prepare("INSERT INTO student_awards (awardtypeID, degID, count) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $awardtypeID, $degID, $count);
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
                alert('Added successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script type='text/javascript'>
                    alert('Error adding: " . $stmt->error . "');
                    window.location.href = 'admin.php';
                </script>";
    }
    $stmt->close();
}

// Delete achievement function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_achievement') {
    $achievement = $_POST['existingAchievements'];

    // Step 1: Use PHP explode function to separate awardType, yearLevel, degprogID, SchoolYear, semester
    list($awardType, $yearLevel, $degprogID, $SchoolYear, $semester) = array_map('trim', explode(',', $achievement));

    // Step 2: Select awardtypeID column in award_type table that matches the given awardType
    $stmt = $conn->prepare("SELECT awardtypeID FROM award_type WHERE awardType = ?");
    $stmt->bind_param("s", $awardType);
    $stmt->execute();
    $stmt->bind_result($awardtypeID);
    $stmt->fetch();
    $stmt->close();

    // Step 3: Select timeID column in time_period table that matches the given SchoolYear and semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    $stmt->fetch();
    $stmt->close();

    // Step 4: Select degID column in college_degree table that matches the given yearLevel, degprogID, timeID
    $stmt = $conn->prepare("SELECT degID FROM college_degree WHERE yearLevel = ? AND degprogID = ? AND timeID = ?");
    $stmt->bind_param("sss", $yearLevel, $degprogID, $timeID);
    $stmt->execute();
    $stmt->bind_result($degID);
    $stmt->fetch();
    $stmt->close();

    // Step 5: Delete row in student_awards table that matches the awardtypeID, degID
    $stmt = $conn->prepare("DELETE FROM student_awards WHERE awardtypeID = ? AND degID = ?");
    $stmt->bind_param("ii", $awardtypeID, $degID);
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
                alert('Deleted successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script type='text/javascript'>
                    alert('Error deleting: " . $stmt->error . "');
                    window.location.href = 'admin.php';
                </script>";
    }
    $stmt->close();
}

// Add degree program information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_deginfo') {
    $yearLevel = $_POST['yearLevel'];
    $degprogID = $_POST['degprogID'];
    $SchoolYear = $_POST['SchoolYear'];
    $semester = $_POST['semester'];
    $count = $_POST['count'];

    // Step 1: Select timeID column in time_period table that matches the given SchoolYear and semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    $stmt->fetch();
    $stmt->close();

    // Step 2: Create degID by concatenating yearLevel, degprogID, and timeID
    $degID = $yearLevel . $degprogID . $timeID;

    // Step 3: Insert the new degree information into the college_degree table
    $stmt = $conn->prepare("INSERT INTO college_degree (degID, yearLevel, degprogID, timeID, count) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $degID, $yearLevel, $degprogID, $timeID, $count);
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
                alert('Added successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script type='text/javascript'>
                alert('Error adding: " . $stmt->error . "');
                window.location.href = 'admin.php';
            </script>";
    }
    $stmt->close();
}

// Add event function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_event') {
    $eventName = isset($_POST['eventName']) ? trim($_POST['eventName']) : '';
    $schoolYear = isset($_POST['SchoolYear']) ? trim($_POST['SchoolYear']) : '';
    $semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';
    $count = isset($_POST['count']) ? trim($_POST['count']) : '';

    // Ensure required fields are not empty
    if (empty($eventName) || empty($schoolYear) || empty($semester) || empty($count)) {
        echo "<script type='text/javascript'>
                alert('All fields are required.');
                window.location.href = 'admin.php';
            </script>";
        return;
    }

    // Find the corresponding timeID for the selected SchoolYear and Semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $schoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    $stmt->fetch();
    $stmt->close();

    if ($timeID) {
        // Insert the new event into the event table
        $stmt = $conn->prepare("INSERT INTO event (eventName, timeID, count) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $eventName, $timeID, $count);
        if ($stmt->execute()) {
            echo "<script type='text/javascript'>
                    alert('Added successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        } else {
            echo "<script type='text/javascript'>
                    alert('Error adding event: " . $stmt->error . "');
                    window.location.href = 'admin.php';
                </script>";
        }
        $stmt->close();
    }
}

// Delete event function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_event') {
    // Retrieve the selected event name
    $eventName = $_POST['existingEvents'];

    // Prepare and execute the SQL statement to delete the event
    $stmt = $conn->prepare("DELETE FROM event WHERE eventName = ?");
    if ($stmt) {
        $stmt->bind_param("s", $eventName);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo "<script type='text/javascript'>
                    alert('Deleted successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        } else {
            echo "<script type='text/javascript'>
            alert('No event found with the name '$eventName'.');
            window.location.href = 'admin.php';
          </script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    }
}

// Add publication function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_publication') {
    $title = $_POST['title'];
    $schoolYear = $_POST['SchoolYear'];
    $semester = $_POST['semester'];
    $count = $_POST['count'];

    // Find the corresponding timeID for the selected SchoolYear and Semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $schoolYear, $semester);
        $stmt->execute();
        $stmt->bind_result($timeID);
        $stmt->fetch();
        $stmt->close();
    }

    if ($timeID) {
        // Insert the new publication into the publication table
        $stmt = $conn->prepare("INSERT INTO publication (title, timeID, count) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssi", $title, $timeID, $count);
            if ($stmt->execute()) {
                echo "<script type='text/javascript'>
                        alert('Added successfully.');
                        window.location.href = 'admin.php';
                      </script>";
            } else {
                echo "<script type='text/javascript'>
                        alert('Error adding: " . $stmt->error . "');
                        window.location.href = 'admin.php';
                    </script>";
            }
            $stmt->close();
        }
    }
}

// Delete publication function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_publication') {
    $title = $_POST['existingPub'];


    // Ensure $title is not an array
    if (is_array($title)) {
        $title = $title[0]; // If it's an array, get the first element
    }

    // Prepare and execute the SQL statement to delete the publication
    $stmt = $conn->prepare("DELETE FROM publication WHERE title = ?");
    if ($stmt) {
        $stmt->bind_param("s", $title);
        $stmt->execute();
        
        // Debug: Check if the statement executed
        if ($stmt->error) {
            echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
        }

        if ($stmt->affected_rows > 0) {
            echo "<script type='text/javascript'>
                    alert('Deleted successfully.');
                    window.location.href = 'admin.php';
                </script>";
        } else {
            echo "<script type='text/javascript'>
                    alert('No publication found with the title '" . htmlspecialchars($title) . "'.');
                    window.location.href = 'admin.php';
                </script>";
        }
        $stmt->close();
    } else {
        echo "<script>
                alert('Error preparing statement: " . $conn->error . "');
                window.location.href = 'admin.php'; 
            </script>";
    }
}

// Add faculty information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_faculty_info') {

    // Retrieve form data
    $rankTitle = $_POST['rankTitle'] ?? '';
    $educAttainmentDesc = $_POST['educAttainmentDesc'] ?? '';
    $SchoolYear = $_POST['SchoolYear'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $count = $_POST['count'] ?? 0;

    // Ensure required fields are not empty
    if (empty($rankTitle) || empty($educAttainmentDesc) || empty($SchoolYear) || empty($semester) || empty($count)) {
        echo "<script>
                alert('All fields are required');
                window.location.href = 'admin.php'; 
            </script>";
        return;
    }

    // Step 1: Fetch rankID based on rank title
    $stmt = $conn->prepare("SELECT rankID FROM rank_title WHERE title = ?");
    $stmt->bind_param("s", $rankTitle);
    $stmt->execute();
    $stmt->bind_result($rankID);
    if (!$stmt->fetch()) {
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 2: Fetch educAttainmentID based on educational attainment description
    $stmt = $conn->prepare("SELECT educAttainmentID FROM educ_attainment WHERE attainment = ?");
    $stmt->bind_param("s", $educAttainmentDesc);
    $stmt->execute();
    $stmt->bind_result($educAttainmentID);
    if (!$stmt->fetch()) {
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 3: Select timeID column in time_period table that matches the given SchoolYear and semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    if (!$stmt->fetch()) {
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 4: Insert the new faculty information into the faculty table
    $stmt = $conn->prepare("INSERT INTO faculty (rankID, educAttainmentID, timeID, count) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $rankID, $educAttainmentID, $timeID, $count);
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
                alert('Added successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        $error_message = "Error adding publication: " . $stmt->error;
        echo "<script type='text/javascript'>
                alert('Error adding: " . $stmt->error . "');
                window.location.href = 'admin.php';
            </script>";
    }
    $stmt->close();
}

// Delete faculty information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_faculty_info') {
    $facultyInfo = $_POST['existingfacultyInfo'];

    // Step 1: Use PHP explode function to separate title, attainment, SchoolYear, semester
    list($title, $attainment, $SchoolYear, $semester) = array_map('trim', explode(',', $facultyInfo));

    // Step 2: Select rankID column in rank_title table that matches the given title
    $stmt = $conn->prepare("SELECT rankID FROM rank_title WHERE title = ?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $stmt->bind_result($rankID);
    if (!$stmt->fetch()) {
        echo "No valid rankID found for the given title: $title.";
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 3: Select educAttainmentID column in educ_attainment table that matches the given attainment
    $stmt = $conn->prepare("SELECT educAttainmentID FROM educ_attainment WHERE attainment = ?");
    $stmt->bind_param("s", $attainment);
    $stmt->execute();
    $stmt->bind_result($educAttainmentID);
    if (!$stmt->fetch()) {
        echo "No valid educAttainmentID found for the given attainment: $attainment.";
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 4: Select timeID column in time_period table that matches the given SchoolYear and semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    if (!$stmt->fetch()) {
        echo "No valid timeID found for the given SchoolYear: $SchoolYear and semester: $semester.";
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();

    // Step 5: Delete row in faculty table that matches the rankID, educAttainmentID, and timeID
    $stmt = $conn->prepare("DELETE FROM faculty WHERE rankID = ? AND educAttainmentID = ? AND timeID = ?");
    $stmt->bind_param("sss", $rankID, $educAttainmentID, $timeID);
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
                alert('Deleted successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script type='text/javascript'>
                alert('Error deleting: " . $stmt->error . "');
                window.location.href = 'admin.php';
            </script>";
    }
    $stmt->close();
}

$conn->close();
?>
