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

    // Select timeID column in time_period table that matches the given SchoolYear and semester
    $stmt = $conn->prepare("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?");
    $stmt->bind_param("ss", $SchoolYear, $semester);
    $stmt->execute();
    $stmt->bind_result($timeID);
    $stmt->fetch();
    $stmt->close();

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
                window.location.href = 'admin.php';
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
    // Retrieve form data
    $title = $_POST['title'];
    $schoolYear = $_POST['SchoolYear'];
    $semester = $_POST['semester'];
    $count = $_POST['count'];

    // Check if any of the required fields are empty
    if (empty($title) || empty($schoolYear) || empty($semester) || empty($count)) {
        echo "<script type='text/javascript'>
                alert('All fields are required.');
                window.location.href = 'admin.php';
              </script>";
        exit; // Stop further script execution
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

    // Check if a valid timeID was found
    if (isset($timeID) && $timeID) {
        // Insert the new publication into the publication table
        $stmt = $conn->prepare("INSERT INTO publication (title, timeID, count) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssi", $title, $timeID, $count);
            if ($stmt->execute()) {
                echo "<script type='text/javascript'>
                        alert('Publication added successfully.');
                        window.location.href = 'admin.php';
                      </script>";
            } else {
                echo "<script type='text/javascript'>
                        alert('Error adding publication: " . $stmt->error . "');
                        window.location.href = 'admin.php';
                    </script>";
            }
            $stmt->close();
        }
    } else {
        echo "<script type='text/javascript'>
                alert('Invalid School Year or Semester selected.');
                window.location.href = 'admin.php';
              </script>";
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

// Add faculty information
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_faculty_info') {
    $rankTitle = trim($_POST['rankTitle']);
    $educAttainmentDesc = trim($_POST['educAttainmentDesc']);
    $SchoolYear = trim($_POST['SchoolYear']);
    $semester = trim($_POST['semester']);
    $count = (int)trim($_POST['count']);

    $rankID = fetchID("SELECT rankID FROM rank_title WHERE title = ?", [$rankTitle], "s");
    $educAttainmentID = fetchID("SELECT educAttainmentID FROM educ_attainment WHERE attainment = ?", [$educAttainmentDesc], "s");
    $timeID = fetchTimeID($SchoolYear, $semester);

    if (!$rankID || !$educAttainmentID || !$timeID) {
        echo "<script>alert('Invalid data provided. Please check input values.'); window.location.href = 'admin.php';</script>";
        error_log("rankID: $rankID, educAttainmentID: $educAttainmentID, timeID: $timeID");
        exit();
    }

    $result = executeSQL("INSERT INTO faculty (rankID, educAttainmentID, timeID, count) VALUES (?, ?, ?, ?)", 
                          "sssi", [$rankID, $educAttainmentID, $timeID, $count]);
    
    if ($result) {
        echo "<script>alert('Added successfully.'); window.location.href = 'admin.php';</script>";
    } else {
        echo "<script>alert('Error adding faculty information.'); window.location.href = 'admin.php';</script>";
        error_log("Failed to execute SQL query.");
    }
}

// Delete faculty information function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_faculty_info') {
    $facultyInfo = $_POST['existingfacultyInfo'];

    // Split faculty information into components
    list($title, $attainment, $SchoolYear, $semester) = array_map('trim', explode(',', $facultyInfo));

    // Fetch IDs using the corrected number of arguments
    $rankID = fetchID("SELECT rankID FROM rank_title WHERE title = ?", [$title], "s");
    $educAttainmentID = fetchID("SELECT educAttainmentID FROM educ_attainment WHERE attainment = ?", [$attainment], "s");
    $timeID = fetchID("SELECT timeID FROM time_period WHERE SchoolYear = ? AND semester = ?", [$SchoolYear, $semester], "ss");

    // Proceed if all IDs are successfully retrieved
    if ($rankID && $educAttainmentID && $timeID) {
        $sql = "DELETE FROM faculty WHERE rankID = ? AND educAttainmentID = ? AND timeID = ?";
        if (executeSQL($sql, "sss", [$rankID, $educAttainmentID, $timeID])) {
            echo "<script type='text/javascript'>
                    alert('Deleted successfully.');
                    window.location.href = 'admin.php';
                  </script>";
        }
    }
}



$conn->close();
?>