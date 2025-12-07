// ========================================
// Global JavaScript Functions
// ========================================

// DOM Ready
document.addEventListener("DOMContentLoaded", () => {
  initializeElements()
  attachEventListeners()
})

// Initialize Elements
function initializeElements() {
  // User Type Selection (Register Page)
  const userTypeOptions = document.querySelectorAll('input[name="user_type"]')
  userTypeOptions.forEach((option) => {
    option.addEventListener("change", toggleDoctorFields)
  })

  // Profile Toggle
  const profileToggle = document.getElementById("profileToggle")
  if (profileToggle) {
    profileToggle.addEventListener("click", toggleProfileDropdown)
  }

  // Hamburger Menu
  const hamburger = document.getElementById("hamburger")
  if (hamburger) {
    hamburger.addEventListener("click", toggleMobileMenu)
  }

  // Tab System
  const tabs = document.querySelectorAll(".tab")
  tabs.forEach((tab) => {
    tab.addEventListener("click", handleTabClick)
  })
}

// Attach Event Listeners
function attachEventListeners() {
  // Login Form Validation
  const loginForm = document.getElementById("loginForm")
  if (loginForm) {
    loginForm.addEventListener("submit", validateLoginForm)
  }

  // Register Form Validation
  const registerForm = document.getElementById("registerForm")
  if (registerForm) {
    registerForm.addEventListener("submit", validateRegisterForm)
  }

  // Close dropdowns when clicking outside
  document.addEventListener("click", (event) => {
    const profileDropdown = document.getElementById("profileDropdown")
    const profileToggle = document.getElementById("profileToggle")

    if (profileDropdown && profileToggle) {
      if (!profileToggle.contains(event.target) && !profileDropdown.contains(event.target)) {
        profileDropdown.classList.remove("show")
      }
    }
  })
}

// ========================================
// Form Validation Functions
// ========================================

function validateLoginForm(e) {
  const email = document.getElementById("email").value.trim()
  const password = document.getElementById("password").value.trim()
  let isValid = true

  // Clear previous errors
  document.querySelectorAll(".error-message").forEach((el) => el.classList.remove("show"))

  // Email validation
  if (!email) {
    showError("email", "Email is required")
    isValid = false
  } else if (!isValidEmail(email)) {
    showError("email", "Please enter a valid email")
    isValid = false
  }

  // Password validation
  if (!password) {
    showError("password", "Password is required")
    isValid = false
  }

  if (!isValid) {
    e.preventDefault()
  }

  return isValid
}

function validateRegisterForm(e) {
  const fullName = document.getElementById("full_name").value.trim()
  const email = document.getElementById("email").value.trim()
  const phone = document.getElementById("phone").value.trim()
  const password = document.getElementById("password").value.trim()
  const confirmPassword = document.getElementById("confirm_password").value.trim()
  const terms = document.getElementById("terms").checked
  const userType = document.querySelector('input[name="user_type"]:checked').value

  let isValid = true

  // Full Name validation
  if (!fullName) {
    showError("full_name", "Full name is required")
    isValid = false
  } else if (fullName.length < 3) {
    showError("full_name", "Full name must be at least 3 characters")
    isValid = false
  }

  // Email validation
  if (!email) {
    showError("email", "Email is required")
    isValid = false
  } else if (!isValidEmail(email)) {
    showError("email", "Please enter a valid email")
    isValid = false
  }

  // Phone validation
  if (!phone) {
    showError("phone", "Phone number is required")
    isValid = false
  } else if (!isValidPhone(phone)) {
    showError("phone", "Please enter a valid phone number")
    isValid = false
  }

  // Password validation
  if (!password) {
    showError("password", "Password is required")
    isValid = false
  } else if (password.length < 8) {
    showError("password", "Password must be at least 8 characters")
    isValid = false
  }

  // Confirm password validation
  if (password !== confirmPassword) {
    showError("confirm_password", "Passwords do not match")
    isValid = false
  }

  // Doctor-specific validation
  if (userType === "doctor") {
    const specialization = document.getElementById("specialization").value
    const qualification = document.getElementById("qualification").value.trim()
    const experience = document.getElementById("experience_years").value
    const fee = document.getElementById("consultation_fee").value

    if (!specialization) {
      showError("specialization", "Specialization is required")
      isValid = false
    }
    if (!qualification) {
      showError("qualification", "Qualification is required")
      isValid = false
    }
    if (!experience) {
      showError("experience_years", "Years of experience is required")
      isValid = false
    }
    if (!fee) {
      showError("consultation_fee", "Consultation fee is required")
      isValid = false
    }
  }

  // Terms & Conditions validation
  if (!terms) {
    alert("Please agree to Terms & Conditions")
    isValid = false
  }

  if (!isValid) {
    e.preventDefault()
  }

  return isValid
}

function showError(fieldId, message) {
  const field = document.getElementById(fieldId)
  if (field && field.nextElementSibling && field.nextElementSibling.classList.contains("error-message")) {
    field.nextElementSibling.textContent = message
    field.nextElementSibling.classList.add("show")
    field.style.borderColor = "var(--danger-red)"
  }
}

function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

function isValidPhone(phone) {
  const re = /^[\d\s\-+$$$$]{10,}$/
  return re.test(phone)
}

// ========================================
// UI Interaction Functions
// ========================================

function toggleDoctorFields() {
  const userType = document.querySelector('input[name="user_type"]:checked').value
  const doctorFields = document.getElementById("doctorFields")
  const formUserType = document.getElementById("formUserType")

  if (doctorFields) {
    if (userType === "doctor") {
      doctorFields.style.display = "block"
    } else {
      doctorFields.style.display = "none"
    }
  }

  if (formUserType) {
    formUserType.value = userType
  }
}

function toggleProfileDropdown() {
  const dropdown = document.getElementById("profileDropdown")
  if (dropdown) {
    dropdown.classList.toggle("show")
  }
}

function toggleMobileMenu() {
  const navMenu = document.getElementById("navbarMenu")
  if (navMenu) {
    navMenu.classList.toggle("active")
  }
}

function handleTabClick(e) {
  const tabName = e.target.getAttribute("data-tab")
  const tabs = document.querySelectorAll(".tab")
  const contents = document.querySelectorAll(".tab-content")

  // Remove active class from all tabs and contents
  tabs.forEach((tab) => tab.classList.remove("active"))
  contents.forEach((content) => content.classList.remove("active"))

  // Add active class to clicked tab and corresponding content
  e.target.classList.add("active")
  const activeContent = document.getElementById(tabName)
  if (activeContent) {
    activeContent.classList.add("active")
  }
}

// ========================================
// Confirmation Modals
// ========================================

function confirmAction(message = "Are you sure you want to proceed?") {
  return confirm(message)
}

function confirmCancel(appointmentId) {
  if (confirmAction("Are you sure you want to cancel this appointment?")) {
    window.location.href = "../../actions/cancel_appointment.php?id=" + appointmentId
  }
}

function confirmReject(appointmentId) {
  if (confirmAction("Are you sure you want to reject this appointment request?")) {
    window.location.href = "../../actions/reject_appointment.php?id=" + appointmentId
  }
}

function confirmApprove(appointmentId) {
  if (confirmAction("Are you sure you want to approve this appointment?")) {
    window.location.href = "../../actions/approve_appointment.php?id=" + appointmentId
  }
}

// ========================================
// Date & Time Utilities
// ========================================

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  })
}

function formatTime(timeString) {
  const [hours, minutes] = timeString.split(":")
  const hour = Number.parseInt(hours)
  const period = hour >= 12 ? "PM" : "AM"
  const displayHour = hour % 12 || 12
  return `${displayHour}:${minutes} ${period}`
}

function validateDateNotPast(dateInput) {
  const selectedDate = new Date(dateInput.value)
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  if (selectedDate < today) {
    dateInput.setCustomValidity("Cannot book appointments in the past")
    return false
  } else {
    dateInput.setCustomValidity("")
    return true
  }
}

// ========================================
// Doctor Selection (Book Appointment)
// ========================================

function loadDoctorDetails(doctorId) {
  // This will be populated with doctor info when doctor is selected
  // For now, it's a placeholder for dynamic content loading
  const detailsContainer = document.getElementById("doctorDetailsContainer")
  if (detailsContainer && doctorId) {
    // Show loading state
    detailsContainer.innerHTML = "<p>Loading doctor details...</p>"

    // In production, fetch doctor details via AJAX
    // For now, show the container
    detailsContainer.style.display = "block"
  }
}

// ========================================
// Toast Notifications (Optional)
// ========================================

function showNotification(message, type = "info") {
  // Create toast element
  const toast = document.createElement("div")
  toast.className = `toast toast-${type}`
  toast.textContent = message
  toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: ${type === "success" ? "#10B981" : type === "error" ? "#EF4444" : "#0EA5E9"};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `

  document.body.appendChild(toast)

  // Remove after 3 seconds
  setTimeout(() => {
    toast.style.animation = "slideOut 0.3s ease"
    setTimeout(() => toast.remove(), 300)
  }, 3000)
}

// ========================================
// Utility Functions
// ========================================

function getQueryParam(param) {
  const urlParams = new URLSearchParams(window.location.search)
  return urlParams.get(param)
}

function redirectTo(url) {
  window.location.href = url
}
