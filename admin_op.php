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

// Helper function to execute SQL queries with prepared statements
function executeSQL($sql, $types, $params) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return false;
    }

    // Ensure $params is an array
    if (!is_array($params)) {
        $params = [$params];
    }

    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Helper function to fetch a single column value from the database
function fetchID($sql, $params, $type) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return null;
    }
    
    // Ensure $params is an array; if not, convert it to one
    if (!is_array($params)) {
        $params = [$params];
    }
    
    $stmt->bind_param($type, ...$params); // Unpack only if it's an array
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    return $id; // Return the fetched id or null if not found
}


// Fetch timeID
function fetchTimeID($schoolYear, $semester) {
    return fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", [$schoolYear, $semester], "ss");
}

// Add academic year and semester
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_acad'])) {
    $newSchoolYear = $_POST['newSchoolYear'];
    $semester = $_POST['semester'];

    if (!preg_match('/^\d{4}-\d{4}$/', $newSchoolYear)) {
        echo "<script>alert('Invalid academic year format. Please use YYYY-YYYY.'); window.location.href = 'admin.php';</script>";
        exit();
    }

    $parts = explode('-', $newSchoolYear);
    $lastTwoDigits = substr($parts[1], -2);
    $timeID = $lastTwoDigits . '-' . $semester;

    if (fetchID("SELECT timeID FROM time_period WHERE timeID = ?", [$timeID], "s")) {
        echo "<script>alert('Academic year and semester combination already exists.'); window.location.href = 'admin.php';</script>";
    } else {
        if (executeSQL("INSERT INTO time_period (timeID, SchoolYear, semester) VALUES (?, ?, ?)", "sss", [$timeID, $newSchoolYear, $semester])) {
            echo "<script>alert('Added successfully.'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Error adding record.'); window.location.href = 'admin.php';</script>";
        }
    }    
}

// Delete academic year
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_acad'])) {
    list($schoolYear, $semester) = array_map('trim', explode(",", $_POST['existingSY']));

    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", [$schoolYear, $semester], "ss");

    if ($timeID) {
        // Check if the timeID is referenced in other tables
        $dependencyCheckQueries = [
            "SELECT COUNT(*) AS count FROM college_degree WHERE timeID = ?",
            "SELECT COUNT(*) AS count FROM event WHERE timeID = ?",
            "SELECT COUNT(*) AS count FROM faculty WHERE timeID = ?",
            "SELECT COUNT(*) AS count FROM publication WHERE timeID = ?"
        ];

        $hasDependencies = false;

        foreach ($dependencyCheckQueries as $query) {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                echo "<script type='text/javascript'>
                        alert('Database error: Unable to prepare statement.');
                        window.location.href = 'admin.php';
                      </script>";
                exit;
            }

            $stmt->bind_param("s", $timeID);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $hasDependencies = true;
                break;
            }
        }

        if ($hasDependencies) {
            echo "<script type='text/javascript'>
                    alert('Cannot delete this academic year as it has associated data.');
                    window.location.href = 'admin.php';
                  </script>";
        } else {
            if (executeSQL("DELETE FROM time_period WHERE SchoolYear = ? AND semester = ?", "ss", [$schoolYear, $semester])) {
                echo "<script type='text/javascript'>
                        alert('Deleted successfully.');
                        window.location.href = 'admin.php';
                      </script>";
            } else {
                echo "<script type='text/javascript'>
                        alert('Error deleting the academic year.');
                        window.location.href = 'admin.php';
                      </script>";
            }
        }
    } else {
        echo "<script type='text/javascript'>
                alert('Academic year not found.');
                window.location.href = 'admin.php';
              </script>";
    }
}


// Add degree program
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_degree'])) {
    $degprogID = $_POST['degprogID'];
    $name = $_POST['name'];

    // Check if either degree code or name is missing and display the specific error
    if (empty($degprogID) && empty($name)) {
        echo "<script>alert('All fields are required.'); window.location.href = 'admin.php';</script>";
    } elseif (empty($degprogID)) {
        echo "<script>alert('Degree program code is required.'); window.location.href = 'admin.php';</script>";
    } elseif (empty($name)) {
        echo "<script>alert('Degree program name is required.'); window.location.href = 'admin.php';</script>";
    } else {
        // Check if the degree program already exists
        if (fetchID("SELECT degprogID FROM deg_prog WHERE degprogID = ?", $degprogID, "s")) {
            echo "<script>alert('Degree program already exists.'); window.location.href = 'admin.php';</script>";
        }
         else {
            // If it does not exist, insert it
            if (executeSQL("INSERT INTO deg_prog (degprogID, name) VALUES (?, ?)", "ss", [$degprogID, $name])) {
                echo "<script>alert('Degree program added successfully.'); window.location.href = 'admin.php';</script>";
            } else {
                echo "<script>alert('Error adding degree program.'); window.location.href = 'admin.php';</script>";
            }
        }
    }
}

// Delete degree program
if (isset($_POST['delete_degree']) && isset($_POST['existingSY'])) {
    $degprogID = $conn->real_escape_string($_POST['existingSY']);

    // Check for dependencies in all relevant tables
    $dependencyCheckQueries = [
        "SELECT COUNT(*) AS count FROM college_degree WHERE degprogID = ?",
        "SELECT COUNT(*) AS count FROM faculty WHERE timeID = ?",
        "SELECT COUNT(*) AS count FROM event WHERE timeID = ?"
    ];

    $hasDependencies = false;

    foreach ($dependencyCheckQueries as $query) {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "<script type='text/javascript'>
                    alert('Database error: Unable to prepare statement.');
                    window.location.href = 'admin.php';
                  </script>";
            exit;
        }

        if (strpos($query, 'timeID') !== false) {
            $stmt->bind_param("s", $degprogID); 
        } else {
            $stmt->bind_param("s", $degprogID); 
        }

        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $hasDependencies = true;
            break;
        }
    }

    if ($hasDependencies) {
        echo "<script type='text/javascript'>
                alert('Cannot delete this degree program as it has associated data.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        $deleteQuery = "DELETE FROM deg_prog WHERE degprogID = ?";
        $stmt = $conn->prepare($deleteQuery);
        if (!$stmt) {
            echo "<script type='text/javascript'>
                    alert('Error preparing delete statement.');
                    window.location.href = 'admin.php';
                  </script>";
            exit;
        }

        $stmt->bind_param("s", $degprogID);
        if ($stmt->execute()) {
            echo "<script type='text/javascript'>
                    alert('Deleted successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        } else {
            echo "<script type='text/javascript'>
                    alert('Error deleting the degree program.');
                    window.location.href = 'admin.php';
                  </script>";
        }
        $stmt->close();
    }
}

// Add achievement function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_achievement') {
    $awardType = trim($_POST['awardType']);
    $degprogID = trim($_POST['degprogID']);
    $count = trim($_POST['count']);

    // Ensure degprogID is in correct format
    $degprogArray = explode(',', $degprogID);
    if (count($degprogArray) !== 4) {
        echo "<script type='text/javascript'>
                alert('Invalid degprogID format. It should be in the format: yearLevel, degprogID, SchoolYear, semester.');
                window.history.back();
              </script>";
        return;
    }

    list($yearLevel, $degprogID, $SchoolYear, $semester) = array_map('trim', $degprogArray);

    // Validate required fields
    if (empty($awardType) || empty($degprogID) || empty($yearLevel) || empty($SchoolYear) || empty($semester) || empty($count)) {
        echo "<script type='text/javascript'>
                alert('All fields are required.');
                window.history.back();
              </script>";
        return;
    }

    // Ensure 'count' is a valid positive integer
    if (!is_numeric($count) || (int)$count <= 0) {
        echo "<script type='text/javascript'>
                alert('Count must be a positive number.');
                window.history.back();
              </script>";
        return;
    }

    // Restrict awardType based on year level and semester
    $validAwardsYear4Sem2 = ['cum laude', 'magna cum laude', 'summa cum laude'];
    $validAwardsOther = ['university scholar', 'college scholar'];

    if ($yearLevel == 4 && $semester == 2) {
        if (!in_array(strtolower($awardType), $validAwardsYear4Sem2)) {
            echo "<script type='text/javascript'>
                    alert('Only \"cum laude\", \"magna cum laude\", or \"summa cum laude\" can be added for Year Level 4, Semester 2.');
                    window.history.back();
                  </script>";
            return;
        }
    } else {
        if (!in_array(strtolower($awardType), $validAwardsOther)) {
            echo "<script type='text/javascript'>
                    alert('Only \"university scholar\" or \"college scholar\" can be added for other levels.');
                    window.history.back();
                  </script>";
            return;
        }
    }

    // Fetch awardTypeID based on award type
    $stmt = $conn->prepare("SELECT awardTypeID FROM award_type WHERE awardType = ?");
    $stmt->bind_param("s", $awardType);
    $stmt->execute();
    $stmt->bind_result($awardTypeID);
    if (!$stmt->fetch()) {
        echo "<script type='text/javascript'>
                alert('No valid award type found for: $awardType.');
                window.history.back();
              </script>";
        $stmt->close();
        return;
    }
    $stmt->close();

    // Select timeID from time_period table for the given SchoolYear and semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    if (!$stmt->fetch()) {
        echo "<script type='text/javascript'>
                alert('No valid time period found for SchoolYear: $SchoolYear and Semester: $semester.');
                window.history.back();
              </script>";
        $stmt->close();
        return;
    }
    $stmt->close();

    // Fetch degID from college_degree for the given yearLevel, degprogID, and timeID
    $stmt = $conn->prepare("SELECT degID FROM college_degree WHERE yearLevel = ? AND degprogID = ? AND timeID = ?");
    $stmt->bind_param("iss", $yearLevel, $degprogID, $timeID);
    $stmt->execute();
    $stmt->bind_result($degID);
    if (!$stmt->fetch()) {
        echo "<script type='text/javascript'>
                alert('No valid degree program found for Year Level: $yearLevel, Degree Program: $degprogID, and Time Period.');
                window.history.back();
              </script>";
        $stmt->close();
        return;
    }
    $stmt->close();

    // Check if the achievement already exists
    $stmt = $conn->prepare("SELECT count FROM student_awards WHERE awardTypeID = ? AND degID = ?");
    $stmt->bind_param("ss", $awardTypeID, $degID);
    $stmt->execute();
    $stmt->bind_result($existingCount);
    if ($stmt->fetch()) {
        // If the record exists, update the count
        $newCount = $existingCount + (int)$count;
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE student_awards SET count = ? WHERE awardTypeID = ? AND degID = ?");
        $stmt->bind_param("iss", $newCount, $awardTypeID, $degID);
        if ($stmt->execute()) {
            echo "<script type='text/javascript'>
                    alert('Achievement count updated successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        } else {
            echo "<script type='text/javascript'>
                    alert('Error updating achievement: " . $stmt->error . "');
                    window.history.back();
                  </script>";
        }
    } else {
        // If the record doesn't exist, insert a new achievement
        $stmt->close();
        
        $stmt = $conn->prepare("INSERT INTO student_awards (awardTypeID, degID, count) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $awardTypeID, $degID, $count);
        if ($stmt->execute()) {
            echo "<script type='text/javascript'>
                    alert('Achievement added successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        } else {
            echo "<script type='text/javascript'>
                    alert('Error adding achievement: " . $stmt->error . "');
                    window.history.back();
                  </script>";
        }
    }
    $stmt->close();
}

// Delete achievement function 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_achievement') {
    $achievement = $_POST['existingAchievements'];

    // Validate input
    if (empty($achievement) || count(explode(',', $achievement)) !== 5) {
        echo "<script>alert('Invalid achievement information provided.'); window.history.back();</script>";
        error_log("Invalid achievement format: $achievement");
        exit();
    }

    // Extract achievement details
    list($awardType, $yearLevel, $degprogID, $SchoolYear, $semester) = array_map('trim', explode(',', $achievement));

    // Fetch IDs
    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", [$SchoolYear, $semester], "ss");
    $awardtypeID = fetchID("SELECT awardTypeID FROM award_type WHERE awardType = ?", [$awardType], "s");
    $degID = fetchID(
        "SELECT degID FROM college_degree WHERE yearLevel = ? AND degprogID = ? AND timeID = ?", 
        [$yearLevel, $degprogID, $timeID], 
        "iss"
    );

    // Validate IDs
    if (!$timeID || !$awardtypeID || !$degID) {
        echo "<script>alert('Achievement information not found.'); window.history.back();</script>";
        error_log("Failed to fetch IDs: timeID=$timeID, awardtypeID=$awardtypeID, degID=$degID");
        exit();
    }

    // Check if achievement exists
    $stmt = $conn->prepare("SELECT 1 FROM student_awards WHERE awardTypeID = ? AND degID = ?");
    $stmt->bind_param("ss", $awardtypeID, $degID);
    $stmt->execute();
    if (!$stmt->fetch()) {
        echo "<script>alert('Achievement not found. Nothing to delete.'); window.history.back();</script>";
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Delete achievement
    $stmt = $conn->prepare("DELETE FROM student_awards WHERE awardTypeID = ? AND degID = ?");
    $stmt->bind_param("ss", $awardtypeID, $degID);
    if ($stmt->execute()) {
        echo "<script>alert('Achievement deleted successfully.'); window.location.href = 'admin.php';</script>";
    } else {
        echo "<script>alert('Error deleting achievement: " . $stmt->error . "'); window.history.back();</script>";
        error_log("Delete error: " . $stmt->error);
    }
    $stmt->close();
}
// Add degree program information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_deginfo') {
    $yearLevel = trim($_POST['yearLevel']);
    $degprogID = trim($_POST['degprogID']);
    $SchoolYear = trim($_POST['SchoolYear']);
    $semester = trim($_POST['semester']);
    $count = trim($_POST['count']);

    // Check if any of the required fields are empty
    if (empty($yearLevel) || empty($degprogID) || empty($SchoolYear) || empty($semester) || empty($count)) {
        echo "<script type='text/javascript'>
                alert('All fields are required.');
                window.history.back();
              </script>";
        exit(); // Stop further execution
    }

    // Select timeID column in time_period table that matches the given SchoolYear and semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    $stmt->fetch();
    $stmt->close();

    if (!$timeID) {
        echo "<script type='text/javascript'>
                alert('Invalid School Year or Semester.');
                window.history.back();
              </script>";
        exit();
    }

    // Create degID by concatenating yearLevel, degprogID, and timeID
    $degID = $yearLevel . $degprogID . $timeID;

    // Insert the new degree information into the college_degree table
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
                window.history.back();
              </script>";
    }
    $stmt->close();
}

// Delete degree program information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_deginfo') {
    $existingDPI = $_POST['existingDPI'];

    // Split the existing degree program information into components
    list($yearLevel, $degprogID, $SchoolYear, $semester) = array_map('trim', explode(',', $existingDPI));

    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    $stmt->fetch();
    $stmt->close();

    if (!$timeID) {
        echo "<script type='text/javascript'>
                alert('Invalid data: Time Period not found.');
                window.location.href = 'admin.php';
              </script>";
        exit();
    }

    $degID = $yearLevel . $degprogID . $timeID;

    $stmt = $conn->prepare("DELETE FROM college_degree WHERE degID = ?");
    $stmt->bind_param("s", $degID);

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

// Add event function
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_event') {
//     $eventName = isset($_POST['eventName']) ? trim($_POST['eventName']) : '';
//     $schoolYear = isset($_POST['SchoolYear']) ? trim($_POST['SchoolYear']) : '';
//     $semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';
//     $count = isset($_POST['count']) ? trim($_POST['count']) : '';

//     Ensure required fields are not empty
//     if (empty($eventName) || empty($schoolYear) || empty($semester) || empty($count)) {
//         echo "<script type='text/javascript'>
//                 alert('All fields are required.');
//                 window.location.href = 'admin.php';
//             </script>";
//         return;
//     }

//     Find the corresponding timeID for the selected SchoolYear and Semester
//     $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
//     $stmt->bind_param("ss", $schoolYear, $semester);
//     $stmt->execute();
//     $stmt->bind_result($timeID);
//     $stmt->fetch();
//     $stmt->close();

//     if ($timeID) {
//         Insert the new event into the event table
//         $stmt = $conn->prepare("INSERT INTO event (eventName, timeID, count) VALUES (?, ?, ?)");
//         $stmt->bind_param("ssi", $eventName, $timeID, $count);
//         if ($stmt->execute()) {
//             echo "<script type='text/javascript'>
//                     alert('Added successfully.');
//                     window.location.href = 'admin.php';
//                   </script>";
//         } else {
//             echo "<script type='text/javascript'>
//                     alert('Error adding event: " . $stmt->error . "');
//                     window.location.href = 'admin.php';
//                 </script>";
//         }
//         $stmt->close();
//     }
// }

// Delete event function
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_event') {
//     $eventName = $_POST['existingEvents'];

//     $sql = "DELETE FROM event WHERE eventName = ?";
//     if (executeSQL($sql, "s", $eventName)) {
//         echo "<script type='text/javascript'>
//                 alert('Deleted successfully.');
//                 window.location.href = 'admin.php';
//               </script>";
//     }
// }

// Add publication function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_publication') {
    $title = trim($_POST['title']);
    $schoolYear = trim($_POST['SchoolYear']);
    $semester = trim($_POST['semester']);
    $count = trim($_POST['count']);
    $is_indexed = isset($_POST['index']) ? (int)$_POST['index'] : 0; // 1 for Indexed, 0 for Non-Indexed

    // Validate inputs
    if (empty($title) || empty($schoolYear) || empty($semester) || !is_numeric($count) || (int)$count <= 0) {
        echo "<script type='text/javascript'>
                alert('Please fill in all fields and ensure count is a positive number.');
                window.history.back();
              </script>";
        exit;
    }

    // Find the corresponding timeID
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $schoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    $stmt->fetch();
    $stmt->close();

    if (!$timeID) {
        echo "<script type='text/javascript'>
                alert('Invalid School Year or Semester.');
                window.history.back();
              </script>";
        exit;
    }

    // Insert publication into the database
    $stmt = $conn->prepare("INSERT INTO publication (title, timeID, count, is_indexed) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $timeID, $count, $is_indexed);
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>
                alert('Publication added successfully.');
                window.location.href = 'admin.php';
              </script>";
    } else {
        echo "<script type='text/javascript'>
                alert('Error: " . $stmt->error . "');
                window.history.back();
              </script>";
    }
    $stmt->close();
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

// Add faculty information
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'add_faculty_info') {
    $rankTitle = trim($_POST['rankTitle']);
    $educAttainmentDesc = trim($_POST['educAttainmentDesc']);
    $SchoolYear = trim($_POST['SchoolYear']);
    $semester = trim($_POST['semester']);
    $count = trim($_POST['count']);

    // Validate form inputs
    if (empty($count)) {
        echo "<script type='text/javascript'>
                alert('Please enter a population number.');
                window.history.back();
            </script>";
        exit;
    }

    // Find the corresponding timeID for the selected SchoolYear and Semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $schoolYear, $semester);
        $stmt->execute();
        $stmt->bind_result($timeID);
        $stmt->fetch();
        $stmt->close();
    }

    $rankID = fetchID("SELECT rankID FROM rank_title WHERE title = ?", [$rankTitle], "s");
    $educAttainmentID = fetchID("SELECT educAttainmentID FROM educ_attainment WHERE attainment = ?", [$educAttainmentDesc], "s");
    $timeID = fetchTimeID($SchoolYear, $semester);

    if (!$rankID || !$educAttainmentID || !$timeID) {
        echo "<script>alert('Invalid data provided. Please check input values.'); window.location.href = 'admin.php';</script>";
        error_log("rankID: $rankID, educAttainmentID: $educAttainmentID, timeID: $timeID");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO faculty (rankID, educAttainmentID, timeID, count) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssi", $rankID, $educAttainmentID, $timeID, $count);
        if ($stmt->execute()) {
            echo "<script>alert('Added successfully.'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Error adding faculty information: " . $stmt->error . "'); window.location.href = 'admin.php';</script>";
            error_log("Failed to execute SQL query: " . $stmt->error);
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement for faculty information insertion.'); window.location.href = 'admin.php';</script>";
        error_log("Failed to prepare SQL statement.");
    }
}


// Delete faculty information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_faculty_info') {
    $facultyInfo = $_POST['existingfacultyInfo'];

    if (empty($facultyInfo) || count(explode(',', $facultyInfo)) !== 4) {
        echo "<script>alert('Invalid faculty information provided.'); window.history.back();</script>";
        error_log("Invalid facultyInfo format: $facultyInfo");
        exit();
    }

    list($title, $attainment, $SchoolYear, $semester) = array_map('trim', explode(',', $facultyInfo));

    $rankID = fetchID("SELECT rankID FROM rank_title WHERE title = ?", [$title], "s");
    $educAttainmentID = fetchID("SELECT educAttainmentID FROM educ_attainment WHERE attainment = ?", [$attainment], "s");
    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", [$SchoolYear, $semester], "ss");

    if (!$rankID || !$educAttainmentID || !$timeID) {
        echo "<script>alert('Faculty information not found.'); window.history.back();</script>";
        error_log("Failed to fetch IDs: rankID=$rankID, educAttainmentID=$educAttainmentID, timeID=$timeID");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM faculty WHERE rankID = ? AND educAttainmentID = ? AND timeID = ?");
    if ($stmt) {
        $stmt->bind_param("sss", $rankID, $educAttainmentID, $timeID);
        if ($stmt->execute()) {
            echo "<script>alert('Deleted successfully.'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Error deleting faculty information: " . $stmt->error . "'); window.history.back();</script>";
            error_log("Delete error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        echo "<script>alert('Failed to prepare the delete statement.'); window.history.back();</script>";
        error_log("Failed to prepare delete statement.");
    }
}

// Add student status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_status'])) {
    $status = $_POST['status'];
    $schoolYear = $_POST['SchoolYear'];
    $semester = $_POST['semester'];
    $count = $_POST['count'];

    $timeID = fetchTimeID($schoolYear, $semester);

    if ($timeID) {
        if (executeSQL("INSERT INTO student_status (status, timeID, count) VALUES (?, ?, ?)", "ssi", [$status, $timeID, $count])) {
            echo "<script>alert('Added successfully.'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Error adding record.'); window.location.href = 'admin.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid School Year or Semester.'); window.location.href = 'admin.php';</script>";
    }
}

// Delete student status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_status'])) {
    $statusID = $_POST['statusID'];

    if (executeSQL("DELETE FROM student_status WHERE statusID = ?", "i", [$statusID])) {
        echo "<script>alert('Deleted successfully.'); window.location.href = 'admin.php';</script>";
    } else {
        echo "<script>alert('Error deleting record.'); window.location.href = 'admin.php';</script>";
    }
}
