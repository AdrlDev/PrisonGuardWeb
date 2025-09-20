let currentDeleteId = null;
let idNumber = null;

const video = document.getElementById("cameraFeed");
const canvas = document.getElementById("snapshotCanvas");
const progressText = document.getElementById("progressText");

let captureInterval;
let capturedFrames = [];
let modal;

// Attach delete button handler
document.querySelectorAll(".delete-visitor-btn").forEach(btn => {
    btn.addEventListener("click", function () {
        currentDeleteId = this.dataset.id;
        idNumber = this.dataset.idNumber;

        if (confirm("Are you sure you want to delete this visitor?")) {
            // Show scanner modal instead of deleteModal
            modal = new bootstrap.Modal(document.getElementById("scannerModal"));
            modal.show();

            // Start scan after short delay (so camera can initialize)
            setTimeout(startCamera, 1000);
        }
    });
});

// ---- Camera Functions ----
async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;

        capturedFrames = [];
        let frameCount = 0;

        // Capture 20 frames automatically
        captureInterval = setInterval(async () => {
            if (frameCount >= 20) {
                clearInterval(captureInterval);
                sendFramesToServer(capturedFrames);
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            canvas.getContext("2d").drawImage(video, 0, 0);

            const dataUrl = canvas.toDataURL("image/jpeg");
            const base64Data = dataUrl.split(",")[1];
            capturedFrames.push(base64Data);

            frameCount++;
            progressText.textContent = `Scanning... Captured ${frameCount}/20`;
        }, 300); // every 300ms
    } catch (err) {
        alert("Camera access denied: " + err.message);
    }
}

// Send frames to PHP proxy → FastAPI
async function sendFramesToServer(frames) {
    progressText.textContent = "Processing, please wait...";

    try {
        // First verify face
        const res = await fetch("../../classes/api/delete_enrolled_face.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ images_base64: frames, id_number: idNumber })
        });

        const data = await res.json();
        console.log("RESULT_ID", idNumber)
        console.log("RESULT", data)

        if (data.success) {
            progressText.textContent = "Face verified! Deleting visitor...";

            // Now perform actual delete
            const deleteRes = await fetch("../../classes/warden_visitor.php", {
                method: "POST",
                body: new URLSearchParams({ id: currentDeleteId })
            });

            const deleteData = await deleteRes.json();

            if (deleteData.success) {
                alert(deleteData.message);
                location.reload(); // Refresh table
            } else {
                alert("Error: " + deleteData.message);
            }

            // Close modal + stop camera
            modal.hide();
            stopCamera();
        } else {
            progressText.textContent = data.message;
            setTimeout(startCamera, 3000);
        }
    } catch (err) {
        alert("Scan failed: " + err.message);
        modal.hide();
        stopCamera();
    }
}


function stopCamera() {
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
    clearInterval(captureInterval);
}

// Stop camera when modal closes
document.getElementById("scannerModal").addEventListener("hidden.bs.modal", stopCamera);
