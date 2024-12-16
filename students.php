<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMPCS Dashboard - Students</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" charset="utf-8"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/e2809407eb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body class="bg-light">
<div id="overlay"></div>
  <div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <a href="#">Students</a>
    <a href="faculty.php">Faculty</a>
    <a href="admin.php">Admin</a>
  </div>
    <nav class="navbar maroon p-4">
        <a href="/#"><img src="images/header.png" style="max-height: 30px;"></a>
    </nav>
    <div class="m-4">
    <main class="p-5">
        <section>
            <a><i class="fas fa-bars" onclick="openNav()" style="cursor:pointer">&nbsp;</i>Analytics > <strong>Students</strong></a>
        </section>
        <hr>
        <?php include 'getyears.php'; ?>
        <form method="POST" id="filterButton">
            <div class="d-flex w-100 ml-4 form-inline align-items-center">
                <label class="indicator mr-2">From:</label>
                <label for="fromYear">Year</label>
                <select id="fromYear" class="ml-2" name="fromYear">
                  <?php foreach ($data['years'] as $year): ?>
                  <option value="<?php echo $year['SchoolYear']; ?>"><?php echo $year['SchoolYear']; ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="fromSemester">Semester</label>
                <select id="fromSemester" class="ml-2" name="fromSemester">
                  <option value="1">1st Semester</option>
                  <option value="2">2nd Semester</option>
                </select>
            </div>
            <div class="d-flex w-100 ml-4 form-inline align-items-center">
              <label class="indicator mr-2">To:</label>
              <label for="toYear">Year</label>
              <select id="toYear" class="ml-2" name="toYear">
                <?php foreach ($data['years'] as $year): ?>
                  <option value="<?php echo $year['SchoolYear']; ?>"><?php echo $year['SchoolYear']; ?></option>
                <?php endforeach; ?>
              </select>
              <label for="toSemester">Semester</label>
              <select id="toSemester" class="ml-2" name="toSemester">
                <option value="1">1st Semester</option>
                <option value="2">2nd Semester</option>
              </select>
            </div>
            <button class="btn btn-primary border-0 ml-2 mt-2 maroon" name="filter">Filter</button>
        </form>
    </main>
      <div class="card-columns m-4">
          <div class="p-3 chart-card m-2 card">
            <div class="card-header">Total Number of Enrollees per Degree Program</div>
            <div class="card-body">
              <canvas id="enrolleesCourseChart" class="w-100"></canvas>
              <p class="chart-description" style="display:none;">This chart shows the total number of enrollees per degree program.</p>
            </div>
          </div>
          <div class="p-3 chart-card m-2 card">
            <div class="card-header">Population of University Scholars between Degree Programs</div>
            <div class="card-body">
              <canvas id="USperDegProg" class="w-100"></canvas>
              <p class="chart-description" style="display:none;">This chart shows the population of university scholars between degree programs.</p>
            </div>
          </div>
          <div class="p-3 chart-card m-2 card">
            <div class="card-header">Population of College Scholars between Degree Programs</div>
            <div class="card-body">
              <canvas id="CSperDegProg" class="w-100"></canvas>
              <p class="chart-description" style="display:none;">This chart shows the population of college scholars between degree programs.</p>
            </div>
          </div>
          <div class="p-3 chart-card m-2 card">
            <div class="card-header">Population of Semestral Achievers per Semester</div>
            <div class="card-body">
              <canvas id="scholarsChart" class="w-100"></canvas>
              <p class="chart-description" style="display:none;">This chart shows the population of semestral achievers per semester.</p>
            </div>
          </div>
          <div class="p-3 chart-card m-2 card">
            <div class="card-header">Population of Enrollees per Year</div>
            <div class="card-body">
              <canvas id="enrolleesYearChart" class="w-100"></canvas>
              <p class="chart-description" style="display:none;">This chart shows the population of enrollees per year.</p>
            </div>
          </div>
            <div class="p-3 chart-card m-2 card">
              <div class="card-header">Population of Graduates with Distinctions </div>
              <div class="card-body"> 
                <canvas id="PopulationLaudes" class="w-100"></canvas>
                <p class="chart-description" style="display:none;">This chart shows the population of graduates with distinctions.</p>
              </div>
            </div>
          <div class="p-3 chart-card m-2 card">
            <div class="card-header">Students per Year Level</div>
            <div class="card-body">
              <canvas id="studentsPerYear" class="w-100"></canvas>
              <p class="chart-description" style="display:none;">This chart shows the number of students per year level.</p>
            </div>
          </div>
            <div class="p-3 chart-card m-2 card">
              <div class="card-header">Enrollment Data</div>
              <div class="card-body">
                <canvas id="enrollmentData" class="w-100"></canvas>
                <p class="chart-description" style="display:none;">This chart shows the enrollment data.</p>
              </div>
            </div>
            <div class="p-3 chart-card m-2 card">
              <div class="card-header">Student Participation in Events</div>
              <div class="card-body">
                <table class="table" id="eventTable">
                  <thead>
                    <tr>
                      <th>Event</th>
                      <th>Participation Count</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
      </div>
    </div>
      <footer class="maroon p-4">
          <p class="text-center text-white" style="font-family: 'Avenir';">© 2024 DMPCS</p>
      </footer>    
</body>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="script.js"></script>
<script src="students_script.js"></script>
</html>
