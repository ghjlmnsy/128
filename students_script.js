document.addEventListener("DOMContentLoaded", function () {
  fetchChartData("getchartdata.php", renderCharts);
  fetchChartData("getevent.php", renderEvents);
});

function fetchChartData(localhost, callback) {
  fetch(localhost)
    .then((response) => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then((data) => callback(data))
    .catch((error) => console.error("Error fetching data:", error));
}

function renderCharts(data) {
  renderEnrolleesCourseChart(data.enrolleesCourseChart);
  renderEnrolleesYearChart(data.enrolleesYearChart);
  renderstudentsPerYear(data.studentsPerYear);
  renderScholarsChart(data.scholarsChart);
  renderUSperDegProg(data.USperDegProg);
  renderCSperDegProg(data.CSperDegProg);
  renderPopulationLaudes(data.PopulationLaudes);
  renderenrollmentData(data.enrollmentChartData);
}

let enrolleesCourseChart;

function renderEnrolleesCourseChart(chartData) {
  var ctx = document.getElementById("enrolleesCourseChart").getContext("2d");

  var datasets = chartData.map((item, index) => {
    return {
      label: item.degprogName,
      data: [item.totalEnrollees],
      backgroundColor: ["#8E1537", "#FFB81D", "#005740"][index % 3],
    };
  });

  enrolleesCourseChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ['Enrollees'],
      datasets: datasets,
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    },
  });
}

let enrolleesYearChart;
function renderEnrolleesYearChart(chartData) {
  console.log("chartData:", chartData); // Debugging line
  var labels = [...new Set(chartData.map((item) => item.SchoolYear + " - " + item.semester))];
  var dataSets = {};

  chartData.forEach((item) => {
    var label = item.SchoolYear + " - " + item.semester;
    var key = item.degprogID + " - " + item.degreeProgram;

    if (!dataSets[key]) {
      dataSets[key] = new Array(labels.length).fill(0);
    }

    var index = labels.indexOf(label);
    if (index !== -1) {
      dataSets[key][index] += Number(item.totalEnrollees);
    }
  });

  var datasets = Object.keys(dataSets).map((key, i) => {
    const colors = ["#8E1537", "#005740", "#FFB81D"];
    return {
      label: key,
      data: dataSets[key],
      backgroundColor: colors[i % colors.length],
      borderColor: colors[i % colors.length],
      borderWidth: 1,
      fill: false,
    };
  });

  var overallTotalEnrollees = chartData.reduce((sum, item) => sum + Number(item.totalEnrollees), 0);
  
  console.log("overallTotalEnrollees:", overallTotalEnrollees); // Debugging line
  
  datasets.push({
    label: "Total Enrollees",
    data: new Array(labels.length).fill(overallTotalEnrollees),
    borderColor: '#000000',
    backgroundColor: '#000000',
    borderWidth: 2,
    fill: false,
  });

  var ctx = document.getElementById("enrolleesYearChart").getContext("2d");
  enrolleesYearChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: datasets,
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      },
      scales: {
        y: {
          beginAtZero: true 
         }
      }
    },
  });
}


let studentsPerYear;

function renderstudentsPerYear(chartData) {
  // Extract unique year levels and degree programs from the chartData
  var yearLevels = [...new Set(chartData.map((item) => item.yearLevel))];
  var degreePrograms = [...new Set(chartData.map((item) => item.degprogName))];

  // Define colors for each degree program with full names
  var colors = {
    "Bachelor of Science in Data Science": "#FFB81D",
    "Bachelor of Science in  Applied Mathematics": "#8E1537",
    "Bachelor of Science in Computer Science": "#005740",
  };

  // Create datasets for each degree program
  var datasets = degreePrograms.map((degprogName) => {
    return {
      label: degprogName,
      backgroundColor: colors[degprogName] || "#FFFFFF",
      data: yearLevels.map((yearLevel) => {
        var item = chartData.find(
          (item) => item.yearLevel === yearLevel && item.degprogName === degprogName
        );
        console.log(`Year: ${yearLevel}, Program: ${degprogName}, Students: ${item ? item.totalStudents : 0}`);
        return item ? item.totalStudents : 0;
      }),
      options: {
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    };
  });

  // Log the datasets to debug
  console.log(datasets);

  // Create the chart
  var ctx = document.getElementById("studentsPerYear").getContext("2d");
  studentsPerYear = new Chart(ctx, {
    type: "bar",
    data: {
      labels: yearLevels,
      datasets: datasets,
    },
    options: {
      barValueSpacing: 20,
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    },
  });
}

let scholarsChart;

function renderScholarsChart(chartData) {
  var labels = [...new Set(chartData.map((item) => item.SchoolYear + " - " + item.semester))];
  var dataSets = {};

  chartData.forEach((item) => {
    var label = item.SchoolYear + " - " + item.semester;
    var key = item.awardType + " - " + item.degreeProgram;

    if (!dataSets[key]) {
      dataSets[key] = new Array(labels.length).fill(0);
    }

    var index = labels.indexOf(label);
    if (index !== -1) {
      dataSets[key][index] = Number(item.totalScholars);
    }
  });

  var datasets = Object.keys(dataSets).map((key, i) => {
    const colors = ["#8E1537", "#005740", "#FFB81D"];
    return {
      label: key,
      data: dataSets[key],
      backgroundColor: colors[i % colors.length],
      borderColor: colors[i % colors.length],
      borderWidth: 1,
      fill: false,
    };
  });

  // Calculate overall totals for College Scholar and University Scholar
  var overallCS = chartData.filter(item => item.awardType === "College Scholar").reduce((sum, item) => sum + Number(item.totalScholars), 0);
  var overallUS = chartData.filter(item => item.awardType === "University Scholar").reduce((sum, item) => sum + Number(item.totalScholars), 0);

  // Add overall totals to datasets
  datasets.push({
    label: "Overall College Scholar",
    data: new Array(labels.length).fill(overallCS),
    borderColor: '#000000',
    backgroundColor: '#000000',
    borderWidth: 2,
    fill: false,
  });

  datasets.push({
    label: "Overall University Scholar",
    data: new Array(labels.length).fill(overallUS),
    borderColor: '#3b3c3d',
    backgroundColor: '#3b3c3d',
    borderWidth: 2,
    fill: false,
  });

  var ctx = document.getElementById("scholarsChart").getContext("2d");
  scholarsChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: datasets,
    },
    options: {
      plugins: {
        legend: {
          position: 'bottom',
        },
      },
      responsive: true,
      scales: {
        x: {
          title: {
            display: true,
            text: 'School Year - Semester',
          },
        },
        y: {
          title: {
            display: true,
          },
          beginAtZero: true,
        },
      },
    },
  });
}


let USperDegProg;

function renderUSperDegProg(chartData) {
  // Define colors for each program
  var colors = {
    "Bachelor of Science in Computer Science": "#005740",
    "Bachelor of Science in Data Science": "#FFB81D",
    "Bachelor of Science in  Applied Mathematics": "#8E1537"
  };

  // Get unique degree programs
  var degreePrograms = [...new Set(chartData.map((item) => item.name))];

  // Create a dataset for each degree program
  var datasets = degreePrograms.map((program) => {
    var data = chartData.reduce((total, item) => {
      if (item.name === program) {
        total += item.UniversityScholars;
      }
      return total;
    }, 0);
    return {
      label: program,
      data: [data], // Wrap data in an array because Chart.js expects an array
      backgroundColor: colors[program] || "#000000",
      borderColor: "#ffffff",
      borderWidth: 5,
    };
  });

  var ctx = document.getElementById("USperDegProg").getContext("2d");
  USperDegProg = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ['University Scholars'], // Use a static label because there's only one data point per dataset
      datasets: datasets,
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      },
      scales: {
        x: {
          beginAtZero: true,
        },
        y: {
          beginAtZero: true
        }
      }
    },
  });
}
let CSperDegProg;

function renderCSperDegProg(chartData) {
  // Define colors for each program
  var colors = {
    "Bachelor of Science in Computer Science": "#005740",
    "Bachelor of Science in Data Science": "#FFB81D",
    "Bachelor of Science in  Applied Mathematics": "#8E1537"
  };

  // Get unique degree programs
  var degreePrograms = [...new Set(chartData.map((item) => item.name))];

  // Create a dataset for each degree program
  var datasets = degreePrograms.map((program) => {
    var data = chartData.reduce((total, item) => {
      if (item.name === program) {
        total += item.CollegeScholars;
      }
      return total;
    }, 0);
    return {
      label: program,
      data: [data], // Wrap data in an array because Chart.js expects an array
      backgroundColor: colors[program] || "#000000",
      borderColor: "#ffffff",
      borderWidth: 5,
    };
  });

  var ctx = document.getElementById("CSperDegProg").getContext("2d");
  CSperDegProg = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ['College Scholars'], // Use a static label because there's only one data point per dataset
      datasets: datasets,
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      },
      scales: {
        x: {
          beginAtZero: true,
        },
        y: {
          beginAtZero: true
        }
      }
    },
  });
}

let PopulationLaudes;

function renderPopulationLaudes(chartData) {
  var labels = [...new Set(chartData.map((item) => item.SchoolYear + " - " + item.semester))];
  var dataSets = {};

  // Fill the dataSets object with the corresponding values
  chartData.forEach((item) => {
    var label = item.SchoolYear + " - " + item.semester;
    var key = item.awardType + " - " + item.degreeProgram;

    if (!dataSets[key]) {
      dataSets[key] = new Array(labels.length).fill(0);
    }

    var index = labels.indexOf(label);
    if (index !== -1) {
      dataSets[key][index] = Number(item.totalRecipients);
    }
  });

  var datasets = Object.keys(dataSets).map((key, i) => {
    const colors = ["#8E1537", "#005740", "#FFB81D"];
    return {
      label: key,
      data: dataSets[key],
      backgroundColor: colors[i % colors.length],
      borderColor: colors[i % colors.length],
      borderWidth: 1,
      fill: false,
    };
  });


  // Calculate overall totals for College Scholar and University Scholar
  var overallCL = chartData.filter(item => item.awardType === "Cum Laude").reduce((sum, item) => sum + Number(item.totalRecipients), 0);
  var overallMCL = chartData.filter(item => item.awardType === "Magna cum Laude").reduce((sum, item) => sum + Number(item.totalRecipients), 0);
  var overallSCL = chartData.filter(item => item.awardType === "Summa cum Laude").reduce((sum, item) => sum + Number(item.totalRecipients), 0);

  // Add overall totals to datasets
  datasets.push({
    label: "Overall Cum Laude",
    data: new Array(labels.length).fill(overallCL),
    borderColor: '#ab9097',
    backgroundColor: '#ab9097',
    borderWidth: 2,
    fill: false,
  });

  datasets.push({
    label: "Overall Magna cum Laude",
    data: new Array(labels.length).fill(overallMCL),
    borderColor: '#8aaba2',
    backgroundColor: '#8aaba2',
    borderWidth: 2,
    fill: false,
  });

  datasets.push({
    label: "Overall Summa cum Laude",
    data: new Array(labels.length).fill(overallSCL),
    borderColor: '#857e6f',
    backgroundColor: '#857e6f',
    borderWidth: 2,
    fill: false,
  });

  var ctx = document.getElementById("PopulationLaudes").getContext("2d");
  PopulationLaudes = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: datasets,
    },
    options: {
      plugins: {
        legend: {
          position: 'bottom',
        },
      },
      responsive: true,
      scales: {
        x: {
          title: {
            display: true,
            text: 'School Year - Semester',
          },
        },
        y: {
          title: {
            display: true,
          },
          beginAtZero: true,
        },
      },
    },
  });
}

let enrollmentChartData;

function renderenrollmentData(enrollmentData) {
  const degreePrograms = [...new Set(enrollmentData.map(item => item.DegreeProgram))];
  const labels = [...new Set(enrollmentData.map(item => 'School Year ' + item.SchoolYear + ', Semester ' + item.semester))];

  const datasets = degreePrograms.map(program => {
    const programData = enrollmentData.filter(item => item.DegreeProgram === program);
    const data = labels.map(label => {
      const item = programData.find(d => 'School Year ' + d.SchoolYear + ', Semester ' + d.semester === label);
      return item ? item.totalEnrollees : null;
    });
    const backgroundColor = getBackgroundColor(program); // Define color dynamically
    return {
      label: program,
      backgroundColor: backgroundColor,
      data: data
    };
  });

  var ctx = document.getElementById('enrollmentData').getContext('2d');
  enrollmentChartData = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: datasets
    },
    options: {
      barValueSpacing: 20,
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      } 
    }
  });
}

function renderEvents(data) {
  var tableBody = '';
  data.events.forEach(function(event) {
    tableBody += '<tr><td>' + event.eventName + '</td><td>' + event.count + '</td></tr>';
  });

  $('#eventTable tbody').html(tableBody);
}

// Helper function to dynamically assign background color based on degree program
function getBackgroundColor(program) {
  switch (program) {
    case "Bachelor of Science in Data Science":
      return "#FFB81D";
    case "Bachelor of Science in  Applied Mathematics":
      return "#8E1537";
    case "Bachelor of Science in Computer Science":
      return "#005740";
    default:
      return "#000000"; // Default color if program not matched
  }
}

$(document).ready(function() {
  $('#filterButton').on('submit', function(e) {
      e.preventDefault();

      var fromYear = $('#fromYear').val();
      var toYear = $('#toYear').val();
      var fromSemester = $('#fromSemester').val();
      var toSemester = $('#toSemester').val();

      console.log("Inputted in Form", fromYear, toYear, fromSemester, toSemester);

      $.ajax({
          url: 'getchartdata.php',
          method: 'POST',
          data: {
              fromYear: fromYear,
              toYear: toYear,
              fromSemester: fromSemester,
              toSemester: toSemester
          },
          success: function(response) {
              var data = JSON.parse(response);
              rerenderCharts(data);
          }
      });

      $.ajax({
        url: 'getevent.php',
        method: 'POST',
        data: {
            fromYear: fromYear,
            toYear: toYear,
            fromSemester: fromSemester,
            toSemester: toSemester
        },
        success: function(data) {
            var data = JSON.parse(data);
            console.log(data);

            var tableBody = '';
            data.events.forEach(function(event) {
            tableBody += '<tr><td>' + event.eventName + '</td><td>' + event.count + '</td></tr>';
            });

            $('#eventTable tbody').html(tableBody);
        }
      })
});

function destroyCharts() {
  enrolleesCourseChart.destroy();
  enrolleesYearChart.destroy();
  studentsPerYear.destroy();
  scholarsChart.destroy();
  USperDegProg.destroy();
  CSperDegProg.destroy();
  PopulationLaudes.destroy();
  enrollmentChartData.destroy();
}

function rerenderCharts(data) {
  destroyCharts();
  renderCharts(data);
}});
