let enrollCaptureInterval;
let enrollCapturedFrames = [];

window.showEnrollmentModal = async function showModal() {
  await startFaceEnrollment();
}

const form = document.getElementById("visitorForm");

// prevent default submit → start enrollment first
form.addEventListener("submit", (e) => {
  e.preventDefault(); // stop form refresh
  startFaceEnrollment();
});

function stopEnrollCamera(videoEl) {
  if (videoEl.srcObject) {
    videoEl.srcObject.getTracks().forEach(track => track.stop());
    videoEl.srcObject = null;
  }
  clearInterval(enrollCaptureInterval);
}

document.getElementById("faceEnrollModal").addEventListener("hide.bs.modal", async () => {
  console.log("Modal closed. Cancelling enrollment...");
  stopEnrollCamera(document.getElementById("enrollCameraFeed"));

  try {
    const response = await fetch("../../classes/api/enroll.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "cancel" })
    });
    const data = await response.json();
    console.log("Cancel enroll response:", data);
  } catch (err) {
    console.error("Error cancelling enroll:", err);
  }
});

async function startFaceEnrollment() {
  // Show face enrollment modal
  const faceModal = new bootstrap.Modal(document.getElementById("faceEnrollModal"));
  faceModal.show();

  const video = document.getElementById("enrollCameraFeed");
  const canvas = document.getElementById("enrollSnapshotCanvas");
  const progressText = document.getElementById("enrollProgressText");

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    video.srcObject = stream;

    enrollCapturedFrames = [];
    let frameCount = 0;

    // Capture 20 frames automatically
    enrollCaptureInterval = setInterval(() => {
      if (frameCount >= 20) {
        clearInterval(enrollCaptureInterval);
        stream.getTracks().forEach(track => track.stop()); // stop camera
        sendEnrollmentFrames(enrollCapturedFrames, faceModal);
        return;
      }

      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      canvas.getContext("2d").drawImage(video, 0, 0);

      const dataUrl = canvas.toDataURL("image/jpeg");
      const base64Data = dataUrl.split(",")[1];
      enrollCapturedFrames.push(base64Data);

      frameCount++;
      progressText.textContent = `Capturing... ${frameCount}/20`;
    }, 300);
  } catch (err) {
    alert("Camera access denied: " + err.message);
  }
}

async function sendEnrollmentFrames(frames, faceModal) {
  const firstName = document.getElementById('firstName').value;
  const lastName = document.getElementById('lastName').value;
  const middleName = document.getElementById('middleName').value;
  const idNumber = document.getElementById('idNumber').value;

  const fullName = [firstName, middleName, lastName].filter(Boolean).join(" ");

  console.log("SCAN", fullName + " ID_NUMBER: " + idNumber)

  const progressText = document.getElementById("enrollProgressText");
  progressText.textContent = "Processing enrollment...";

  try {
    const response = await fetch("../../classes/api/enroll.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        name: fullName,
        id_number: idNumber,
        images_base64: frames
      })
    });

    const result = await response.json();
    console.log("Enroll Response:", result);

    if (result.success || result.status === "ok") {
      faceModal.hide();

      // ✅ Add hidden input so PHP sees submitForApproval
      if (!document.getElementById("hiddenSubmit")) {
        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "submitForApproval";
        hidden.value = "1";
        hidden.id = "hiddenSubmit";
        form.appendChild(hidden);
      }

      // 👉 Now safely submit the form
      form.submit();
    } else {
      alert("⚠ Enrollment failed: " + (result.error || result.message || "Unknown error"));

      // 🔄 Restart scanning if failed
      progressText.textContent = "Retrying enrollment...";
      setTimeout(() => {
        startFaceEnrollment(); // restart scanning
      }, 1500);
    }
  } catch (error) {
    console.error("Error enrolling:", error);
    alert("Error connecting to enrollment API. Retrying...");

    // 🔄 Restart scanning after error
    progressText.textContent = "Retrying enrollment...";
    setTimeout(() => {
      startFaceEnrollment();
    }, 1500);
  }
}
