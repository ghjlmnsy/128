// Function to open the navigation sidebar by setting its width to 350px
function openNav() {
  document.getElementById("mySidenav").style.width = "350px";
}

// Function to close the navigation sidebar by setting its width to 0
function closeNav() {
  document.getElementById("mySidenav").style.width = "0";
}

// Function to validate Academic Year & Semester form
function validateAcademicYearForm() {
  const yearPattern = /^\d{4}-\d{4}$/;
  const yearInput = document.getElementById('newSchoolYear').value;
  if (!yearPattern.test(yearInput)) {
    alert('Please enter a valid academic year in the format YYYY-YYYY.');
    return false;
  }
  return true;
}

// Function to validate Degree Program form
function validateDegreeProgramForm() {
  const codeInput = document.getElementById('degprogID').value.trim();
  const nameInput = document.getElementById('name').value.trim();
  if (codeInput === '' || nameInput === '') {
    alert('Please fill out both the degree program code and name.');
    return false;
  }
  return true;
}

// Function to validate Achievements form
function validateAchievementsForm() {
  const countInput = document.getElementById('count').value.trim();
  if (countInput === '' || isNaN(countInput) || parseInt(countInput) <= 0) {
    alert('Please enter a valid population number.');
    return false;
  }
  return true;
}

// Function to validate Research/Publications form
function validateResearchForm() {
  const titleInput = document.getElementById('researchTitle').value.trim();
  const countInput = document.getElementById('researchCount').value.trim();
  if (titleInput === '' || isNaN(countInput) || countInput === '' || parseInt(countInput) <= 0) {
    alert('Please fill out the research name and a valid number of participants.');
    return false;
  }
  return true;
}

// Function to validate Faculty Information form
function validateFacultyInfoForm() {
  const countInput = document.getElementById('facultyCount').value.trim();
  if (isNaN(countInput) || countInput === '' || parseInt(countInput) <= 0) {
    alert('Please enter a valid population number.');
    return false;
  }
  return true;
}

// Runs when the document is fully loaded
$(document).ready(function() {
  // Check if the device is not a mobile device by testing the user agent
  if (!(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))) {
    
    // When a card element is clicked, add the 'zoomed' class and display the overlay
    $('.card').on('click', function() {
      // Disable zoom-in function for admin cards
      if (!$(this).closest('#timeDataSection, #studentDataSection, #facultyDataSection').length) {
        $(this).addClass('zoomed');
        $('#overlay').show();
        $(this).find('.chart-description').show();
      }
    });

    // Prevent canvas clicks inside the card from propagating to the card click event
    $('.card canvas').on('click', function(event) {
      event.stopPropagation();
    });

    // When the overlay is clicked, remove the 'zoomed' class from the card and hide the overlay
    $('#overlay').on('click', function() {
      $('.zoomed').removeClass('zoomed');
      $(this).hide();
      $('.chart-description').hide();
    });

    // Listen for the 'Escape' key to close any zoomed card and hide the overlay
    $(document).on('keyup', function(event) {
      if (event.keyCode === 27) { // 27 is the key code for the escape key
        $('.zoomed').removeClass('zoomed');
        $('#overlay').hide();
        $('.chart-description').hide();
      }
    });
  }

  // Attach validation functions to form submissions
  $('form[action="admin_op.php"]').on('submit', function(event) {
    const formId = $(this).attr('id');
    let isValid = true;

    switch (formId) {
      case 'academicYearForm':
        isValid = validateAcademicYearForm();
        break;
      case 'degreeProgramForm':
        isValid = validateDegreeProgramForm();
        break;
      case 'achievementsForm':
        isValid = validateAchievementsForm();
        break;
      case 'researchForm':
        isValid = validateResearchForm();
        break;
      case 'facultyInfoForm':
        isValid = validateFacultyInfoForm();
        break;
    }

    if (!isValid) {
      event.preventDefault();
    }
  });
});

document.getElementById('chooseForms').addEventListener('change', function() {
    const selectedValue = this.value;
    const sections = document.querySelectorAll('.data-section');

    console.log('Selected Value:', selectedValue); // Debugging line

    sections.forEach(section => {
        if (section.id.toLowerCase().includes(selectedValue)) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
});

// Initially show only Time Data
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.data-section');

    sections.forEach(section => {
        if (section.id.toLowerCase().includes('time')) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
});