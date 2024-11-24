<?php include 'fetch.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMPCS Dashboard - Admin Page</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/e2809407eb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Add jQuery -->
</head>

<body class="bg-light">
    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="students.php">Students</a>
        <a href="faculty.php">Faculty</a>
        <a href="#">Admin</a>
    </div>
    <nav class="navbar maroon p-4">
        <a href="/#"><img src="images/header.png" style="max-height: 30px;"></a>
    </nav>
    <div class="m-4">
        <main class="p-5">
            <section>
                <a><i class="fas fa-bars" onclick="openNav()" style="cursor:pointer">&nbsp;</i>Analytics >
                    <strong>ADMIN</strong></a>
            </section>
            <hr>
            <form method="POST" id="filterForms">
                <div class="d-flex w-100 ml-4 form-inline align-items-center">
                    <label class="indicator mr-2">Filter: </label>
                    <select id="chooseForms" class="ml-2" name="chooseForms">
                        <option value="time">Time Data</option>
                        <option value="student">Student Data</option>
                        <option value="faculty">Faculty Data</option>
                    </select>
                </div>
            </form>
            <br>
            <br>
            <div id="timeDataSection" class="data-section">
                <div class="card-columns">
                    <div class="card p-3 m-3">
                        <div class="card-header">Update Academic Year & Semester</div>
                        <div class="card-body">
                            <form id="academicYearForm" action="admin_op.php" method="post">
                                <div class="form-group">
                                    <label for="SchoolYear">Academic Year (Format: 2023-2024):</label>
                                    <input type="text" class="form-control" id="newSchoolYear" name="newSchoolYear" placeholder="XXXX-XXXX">
                                </div>
                                <div class="form-group">
                                    <label for="semester">Semester:</label>
                                    <select class="form-control" id="semester" name="semester">
                                        <?php
                                        foreach ($semesters as $semester) {
                                            echo "<option value='$semester'>$semester</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary green border-0" name="add_acad">Add</button>
                            </form>
                            <br /><br />
                            <form method="POST" action="admin_op.php" onsubmit="return confirmDelete()">
                                <div class="form-group">
                                    <label for="existingSY">Existing Academic Year:</label>
                                    <select class="form-control" id="existingSY" name="existingSY">
                                        <?php foreach ($time_period_info as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        } ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary maroon border-0" name="delete_acad">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div class="card p-3 m-3">
                        <div class="card-header">Update Degree Program</div>
                        <div class="card-body">
                            <form id="degreeProgramForm" action="admin_op.php" method="post">
                                <div class="form-group">
                                    <label for="degprogID">Degree Program Code:</label>
                                    <input type="text" class="form-control" id="degprogID" name="degprogID" placeholder="Enter degree program code">
                                    <br />
                                    <label for="name">Degree Program Name:</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter degree program name">
                                </div>
                                <button type="submit" class="btn btn-primary green border-0" name="add_degree">Add</button>
                            </form>
                            <br /><br />
                            <form method="POST" action="admin_op.php" onsubmit="return confirmDelete()">
                                <div class="form-group">
                                    <label for="existingSY">Existing Degree Programs:</label>
                                    <select class="form-control" id="existingSY" name="existingSY">
                                        <?php foreach ($deg_programs as $degprogID) {
                                            echo "<option value='$degprogID'>$degprogID</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary maroon border-0" name="delete_degree">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div id="studentDataSection" class="data-section">
                <div class="card-columns">
                    <div class="card p-3 m-3">
                        <div class="card-header">Update Achievements</div>
                        <div class="card-body">
                            <form id="achievementsForm" action="admin_op.php" method="post">
                                <div class="form-group">
                                    <label for="awardType">Achievement:</label>
                                    <select class="form-control" id="awardType" name="awardType">
                                        <?php
                                        foreach ($awardtypes as $awardType) {
                                            echo "<option value='$awardType'>$awardType</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="degprogID">Degree Program:</label>
                                    <select class="form-control" id="degprogID" name="degprogID">
                                        <?php
                                        foreach ($degree_programs as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="count">Population:</label>
                                    <input type="number" class="form-control" id="count" name="count" placeholder="Enter population number">
                                </div>
                                <button type="submit" class="btn btn-primary green border-0" name="action" value="add_achievement">Add</button>
                                <br /><br />
                                <div class="form-group">
                                    <label for="existingAchievements">Existing Achievements:</label>
                                    <select class="form-control" id="existingAchievements" name="existingAchievements">
                                        <?php
                                        foreach ($achievements as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary maroon border-0" name="action" value="delete_achievement" onclick="return confirmDelete()">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div class="card p-3 m-3">
                        <div class="card-header">Update Degree Program Information</div>
                        <div class="card-body">
                            <form id="degreeProgramInfoForm" action="admin_op.php" method="post">
                                <input type="hidden" id="degID" name="degID" value="<?php echo isset($degID) ? $degID : ''; ?>">
                                <div class="form-group">
                                    <label for="degprogID">Degree Program:</label>
                                    <select class="form-control" id="degprogID" name="degprogID">
                                        <?php
                                        foreach ($deg_programs as $degprogID) {
                                            echo "<option value='$degprogID'>$degprogID</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="yearLevel">Year Level:</label>
                                    <select class="form-control" id="yearLevel" name="yearLevel">
                                        <?php
                                        foreach ($yrLevel as $level) {
                                            echo "<option value='{$level}'>{$level}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="SchoolYear">Year:</label>
                                    <select class="form-control" id="SchoolYear" name="SchoolYear">
                                        <?php
                                        foreach ($academic_years as $year) {
                                            echo "<option value='$year'>$year</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="semester">Semester:</label>
                                    <select class="form-control" id="semester" name="semester">
                                        <?php
                                        foreach ($semesters as $semester) {
                                            echo "<option value='$semester'>$semester</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="count">Population:</label>
                                    <input type="number" class="form-control" id="count" name="count" placeholder="Enter population number">
                                </div>
                                <button type="submit" class="btn btn-primary green border-0" name="action" value="add_deginfo">Add</button>
                            </form>
                            <br /><br />
                            <form method="POST" action="admin_op.php" onsubmit="return confirmDelete()">
                                <div class="form-group">
                                    <label for="existingDPI">Existing Degree Program Information:</label>
                                    <select class="form-control" id="existingDPI" name="existingDPI">
                                        <?php
                                        foreach ($degree_exist as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary maroon border-0" name="action" value="delete_deginfo">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div id="facultyDataSection" class="data-section">
                <div class="card-columns">
                    <div class="card p-3 m-3">
                        <div class="card-header">Update Research/Publications</div>
                        <div class="card-body">
                            <form id="researchForm" action="admin_op.php" method="post" onsubmit="return validateResearchForm()">
                                <div class="form-group">
                                    <label for="title">Research Name:</label>
                                    <input type="text" class="form-control" id="researchTitle" name="title" placeholder="Enter Research Name">
                                </div>
                                <div class="form-group">
                                    <label for="SchoolYear">Year:</label>
                                    <select class="form-control" id="SchoolYear" name="SchoolYear">
                                        <?php
                                        foreach ($academic_years as $year) {
                                            echo "<option value='$year'>$year</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="semester">Semester:</label>
                                    <select class="form-control" id="semester" name="semester">
                                        <?php
                                        foreach ($semesters as $semester) {
                                            echo "<option value='$semester'>$semester</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="count">PARTICIPANTS:</label>
                                    <input type="number" class="form-control" id="researchCount" name="count" placeholder="Enter Number of Participants">
                                </div>
                                <button type="submit" class="btn btn-primary green border-0" name="action" value="add_publication">Add</button>
                            </form>
                            <br /><br />
                            <form method="POST" action="admin_op.php" onsubmit="return confirmDelete()">
                                <div class="form-group">
                                    <label for="existingPub">Existing Publications:</label>
                                    <select class="form-control" id="existingPub" name="existingPub">
                                        <?php
                                        foreach ($pub_title as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary maroon border-0" name="action" value="delete_publication">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div class="card p-3 m-3">
                        <div class="card-header">Update Faculty Information</div>
                        <div class="card-body">
                            <form id="facultyInfoForm" action="admin_op.php" method="post" onsubmit="return validateFacultyInfoForm()">
                                <div class="form-group">
                                    <label for="rankTitle">Rank Title:</label>
                                    <select class="form-control" id="rankTitle" name="rankTitle" required>
                                        <?php
                                        foreach ($ranks as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="educAttainmentDesc">Educational Attainment Description:</label>
                                    <select class="form-control" id="educAttainmentDesc" name="educAttainmentDesc" required>
                                        <?php
                                        foreach ($educational_attainments as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="SchoolYear">Year:</label>
                                    <select class="form-control" id="SchoolYear" name="SchoolYear" required>
                                        <?php
                                        foreach ($academic_years as $year) {
                                            echo "<option value='$year'>$year</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="semester">Semester:</label>
                                    <select class="form-control" id="semester" name="semester" required>
                                        <?php
                                        foreach ($semesters as $semester) {
                                            echo "<option value='$semester'>$semester</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="count">Population:</label>
                                    <input type="number" class="form-control" id="facultyCount" name="count" placeholder="Enter Population Number" required>
                                </div>
                                <button type="submit" class="btn btn-primary green border-0" name="action" value="add_faculty_info">Add</button>
                            </form>
                            <br /><br />
                            <form action="admin_op.php" method="post" onsubmit="return confirmDelete()">
                                <div class="form-group">
                                    <label for="existingfacultyInfo">Existing Faculty Information:</label>
                                    <select class="form-control" id="existingfacultyInfo" name="existingfacultyInfo" required>
                                        <?php
                                        foreach ($faculty_info as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary maroon border-0" name="action" value="delete_faculty_info">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <footer class="maroon p-4">
        <p class="text-center text-white" style="font-family: 'Avenir Black';">© 2024 DMPCS</p>
    </footer>
    <script>
        document.getElementById('existingSY').addEventListener('change', function() { // Corrected id here
            document.getElementById('timeID').value = this.value;
        });

        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }

        function validateResearchForm() {
            var title = document.getElementById('researchTitle').value.trim();
            var count = document.getElementById('researchCount').value.trim();
            
            if (title === '') {
                alert('Please fill out the research name.');
                return false;
            }
            
            if (count === '' || isNaN(count) || parseInt(count) <= 0) {
                alert('Please enter a valid number of participants.');
                return false;
            }
            
            return true;
        }

        function validateFacultyInfoForm() {
            var count = document.getElementById('facultyCount').value.trim();
            
            if (count === '' || isNaN(count) || parseInt(count) <= 0) {
                alert('Please enter a valid population number.');
                return false;
            }
            
            return true;
        }
    </script>
    <script src="script.js"></script>
    <script src="students_script.js"></script>
</body>

</html>
