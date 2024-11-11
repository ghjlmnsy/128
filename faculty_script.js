document.addEventListener("DOMContentLoaded", function () {
  fetchChartData("getchart_faculty.php", renderCharts);
});

function fetchChartData(localhost, callback) {
  fetch(localhost)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => callback(data));
  // .catch((error) => console.error("Error fetching data:", error));
}

// Main function to render all charts using the fetched data
function renderCharts(data) {
  renderRatioByRank(data.ratioByRank);
  renderRatioByEduc(data.ratioByEduc);
  renderNumberOfTotalFaculty(data.numberOfTotalFaculty);
  renderNumberOfPublications(data.numberOfPublications);
  renderFacultySembyRank(data.facultySembyRank);
  renderFacultyByEducAttainment(data.facultyByEducAttainment);
}

// Chart variables for handling re-rendering
var ratioByRankChart, ratioByEducChart, totalFacultyChart, numberOfPublicationsChart, facultySembyRankChart, facultyByEducAttainmentChart;

function renderRatioByRank(chartData) {
  var ctx = document.getElementById("ratioByRank").getContext("2d");
  var datasets = chartData.map((item, index) => {
    return {
      label: item.facultyRank,
      data: [item.rankCount],
      backgroundColor: ["#8E1537", "#FFB81D", "#005740", "#8E1537", "#C70039"][
        index % 5
      ],
    };
  });
  ratioByRankChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Ratio by Rank"],
      datasets: datasets,
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

function renderRatioByEduc(chartData) {
  var ctx = document.getElementById("ratioByEduc").getContext("2d");
  var datasets = chartData.map((item, index) => {
    return {
      label: item.educationalAttainment,
      data: [item.facultyCount],
      backgroundColor: ["#8E1537", "#FFB81D", "#005740", "#8E1537", "#C70039"][
        index % 5
      ],
    };
  });
  ratioByEducChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Ratio by Education"],
      datasets: datasets,
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

function renderNumberOfTotalFaculty(chartData) {
  var labels = chartData.map(
    (item) => item.SchoolYear + " Sesmester " + item.semester
  );
  var totalFaculty = chartData.map((item) => item.totalFaculty);
  var customColors = ["#8E1537", "#FFB81D", "#005740", "#808080"];
  var ctx = document.getElementById("numberOfTotalFaculty").getContext("2d");
  totalFacultyChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Total Faculty",
          data: totalFaculty,
          backgroundColor: customColors,
          borderColor: customColors,
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

function renderNumberOfPublications(chartData) {
  var labels = chartData.map(
    (item) => item.SchoolYear + " Semester " + item.semester
  );
  var counts = chartData.map((item) => item.totalPublications);
  var customColors = ["#8E1537", "#FFB81D", "#005740", "#808080"];
  var ctx = document.getElementById("numberOfPublications").getContext("2d");
  numberOfPublicationsChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Publications",
          data: counts,
          backgroundColor: customColors,
          borderColor: customColors,
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

function renderFacultySembyRank(chartData) {
  var labelSet = new Set();
  var datasets = {};
  var customColors = [
    "#8E1537",
    "#FFB81D",
    "#005740",
    "#C70039",
    "#0066cc",
    "#ff8c00",
  ];

  chartData.forEach((item, index) => {
    var semesterLabel = item.SchoolYear + " " + item.semester;
    labelSet.add(semesterLabel);
    if (!datasets[item.Rank]) {
      datasets[item.Rank] = {
        label: item.Rank,
        data: [],
        backgroundColor: customColors[index % customColors.length], // Use modulo to cycle through colors
        borderColor: customColors[index % customColors.length],
        borderWidth: 1,
      };
    }
  });

  // Convert the set of labels to an array
  var labels = Array.from(labelSet);
  for (var rank in datasets) {
    for (var i = 0; i < labels.length; i++) {
      datasets[rank].data.push(0); // Initialize all data points to 0
    }
  }

  // Fill in the data arrays with actual counts
  chartData.forEach((item) => {
    var semesterLabel = item.SchoolYear + " " + item.semester;
    var labelIndex = labels.indexOf(semesterLabel);
    datasets[item.Rank].data[labelIndex] = item.facultyCount;
  });

  var datasetsArray = Object.values(datasets);

  var ctx = document.getElementById("facultySembyRank").getContext("2d");
  facultySembyRankChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: datasetsArray,
    },
    options: {
      responsive: true,
      scales: {
        x: {
          title: {
            display: true,
            text: "Semesters",
          },
        },
        y: {
          title: {
            display: true,
            text: "",
          },
        },
      },
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

function renderFacultyByEducAttainment(chartData) {
  var labels = [
    ...new Set(chartData.map((item) => item.SchoolYear + " " + item.semester)),
  ];
  var datasets = {};
  var customColors = ["#8E1537", "#FFB81D", "#005740", "#C70039"];

  chartData.forEach((item) => {
    if (!datasets[item.EducationalAttainment]) {
      datasets[item.EducationalAttainment] = {
        label: item.EducationalAttainment,
        data: new Array(labels.length).fill(0),
        backgroundColor: customColors,
        borderColor: customColors,
        borderWidth: 1,
        fill: false,
      };
    }
    var labelIndex = labels.indexOf(item.SchoolYear + " " + item.semester);
    if (labelIndex !== -1) {
      datasets[item.EducationalAttainment].data[labelIndex] = item.facultyCount;
    }
  });

  var datasetsArray = Object.values(datasets);

  var ctx = document.getElementById("facultyByEducAttainment").getContext("2d");
  facultyByEducAttainmentChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: datasetsArray,
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

// Event listener for filter button, used to fetch data based on filter criteria
$(document).ready(function () {
  $("#filterButton").on("submit", function (e) {
    e.preventDefault();
    var fromYear = $("#fromYear").val();
    var toYear = $("#toYear").val();
    var fromSemester = $("#fromSemester").val();
    var toSemester = $("#toSemester").val();

    console.log("Inputted in Form", fromYear, toYear, fromSemester, toSemester);

    $.ajax({
      url: "getchart_faculty.php",
      method: "POST",
      data: {
        fromYear: fromYear,
        toYear: toYear,
        fromSemester: fromSemester,
        toSemester: toSemester,
      },
      success: function (response) {
        var data = JSON.parse(response);
        rerenderCharts(data);
      },
    });
  });
});

// Re-render all charts after data filter is applied
function rerenderCharts(data) {
  if (totalFacultyChart) totalFacultyChart.destroy();
  if (numberOfPublicationsChart) numberOfPublicationsChart.destroy();
  if (facultySembyRankChart) facultySembyRankChart.destroy();
  if (facultyByEducAttainmentChart) facultyByEducAttainmentChart.destroy();
  if (ratioByRankChart) ratioByRankChart.destroy();
  if (ratioByEducChart) ratioByEducChart.destroy();
  renderCharts(data);
}
