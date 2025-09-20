
// Variables to store the modal instances
let registrationModal;
let cameraModal;

// Initialize modals when document is ready
document.addEventListener('DOMContentLoaded', function () {
  registrationModal = new bootstrap.Modal(document.getElementById('registrationModal'));
  cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'));

  // Custom open camera button handler
  document.getElementById('openCameraBtn').addEventListener('click', function () {
    // Show camera modal without hiding registration modal
    cameraModal.show();
  });
});

// Function to fill the registration form with scanned data 
async function fillRegistrationForm() {
  const cameraFirstName = document.getElementById('cameraFirstName').value;
  const cameraLastName = document.getElementById('cameraLastName').value;
  const cameraMiddleName = document.getElementById('cameraMiddleName').value;
  const cameraIdType = document.getElementById('cameraIdType').value;
  const cameraIdNumber = document.getElementById('cameraIdNumber').value;

  const fullName = `${cameraFirstName} ${cameraMiddleName} ${cameraLastName}`.trim();

  // 🔹 Call enroll.php to register user
  try {
    const response = await fetch("../classes/api/enroll.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        name: fullName,
        id_number: cameraIdNumber
      })
    });

    const result = await response.json();
    console.log("Enroll Response:", result);

    // ✅ Check if scan/enroll was successful
    if (result.success) {
      alert("Scan successful! User enrolled.");

      // 🔹 Only fill the registration form if success
      if (cameraFirstName) document.getElementById('firstName').value = cameraFirstName;
      if (cameraLastName) document.getElementById('lastName').value = cameraLastName;
      if (cameraMiddleName) document.getElementById('middleName').value = cameraMiddleName;
      if (cameraIdType) document.getElementById('idType').value = cameraIdType;
      if (cameraIdNumber) document.getElementById('idNumber').value = cameraIdNumber;

    } else {
      alert("Scan failed: " + (result.error || "Unknown error"));
    }

  } catch (error) {
    console.error("Error enrolling:", error);
    alert("Error enrolling user.");
  }

  // Close camera modal but keep registration modal open
  cameraModal.hide();
}
