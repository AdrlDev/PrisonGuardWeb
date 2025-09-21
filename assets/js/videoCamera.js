const video = document.getElementById("cameraFeed");
const canvas = document.getElementById("snapshotCanvas");
const progressText = document.getElementById("progressText");

let captureInterval;
let capturedFrames = [];
let modal;

let currentPage = 1;
const limit = 5; // rows per page

let idNumber = null;

// Show modal and start scanning automatically
document.getElementById("scanVisitorBtn").addEventListener("click", () => {
    const scannerModalEl = document.getElementById("scannerModal");
    modal = bootstrap.Modal.getOrCreateInstance(scannerModalEl);
    modal.show();
    startCamera();
});

document.getElementById("savePurposeBtn").addEventListener("click", () => {
    const purpose = document.getElementById("purposeSelect").value;
    if (!purpose) {
        alert("⚠ Please select a purpose of visit.");
        return;
    }
    checkVisitors(idNumber, purpose);

    const purposeModalEl = document.getElementById("purposeModal");
    const purposeModal = bootstrap.Modal.getOrCreateInstance(purposeModalEl);
    purposeModal.hide();
});

// Listen for modal close (user clicks 'x' or outside modal)
document.getElementById("scannerModal").addEventListener("hide.bs.modal", async () => {
    console.log("Modal closed. Cancelling scan...");
    stopCamera(); // stop camera immediately

    try {
        const response = await fetch("../../classes/api/scan.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "cancel" }) // send cancel action
        });
        const data = await response.json();
        console.log("Cancel scan response:", data);
    } catch (err) {
        console.error("Error cancelling scan:", err);
    }
});

loadVisitorLogs(currentPage);
loadVisitorStats();

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
        const response = await fetch("../../classes/api/scan.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ images_base64: frames })
        });

        const data = await response.json();

        console.log("SCAN_RESULT", data)

        let success = false;

        if (data.status === "ok" && data.name) {
            success = true;

            //add this the php
            idNumber = data.id_number;

            checkVisitorInLog(idNumber).then(log => {
                if (log) {
                    checkVisitors(idNumber, log.purpose);
                } else {
                    new bootstrap.Modal(document.getElementById("purposeModal")).show();
                }
            });

        } else if (data.status === "unknown") {
            alert("⚠ Unknown face detected! Please try again.")
        } else {
            alert(data.message || "Scan finished, no match.")
        }

        if (success) {
            // ✅ Close modal when success
            modal.hide();
            stopCamera();
        } else {
            // 🔄 Retry scanning again
            setTimeout(startCamera, 1000);
        }
    } catch (err) {
        console.error("Scan error:", err);
        alert("Error connecting to scanner.Please try again.");
        modal.hide();
        stopCamera();
    }
}

async function checkVisitors(idNumber, purpose) {
    fetch("../../classes/visitors.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ idNumber: idNumber })
    })
        .then(res => res.json())
        .then(visitorData => {
            console.log("Visitor found in DB:", visitorData);
            if (visitorData.status === "ok") {
                processVisitors(visitorData.visitor, purpose)
            } else {
                alert("⚠ Face recognized but visitor not found in database!");
            }
        })
        .catch(err => console.error("Visitor lookup error:", err));
}

async function processVisitors(visitor, purpose) {
    // First try IN
    fetch("../../classes/visitors_log.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            visitorsFullName: `${visitor.firstName} ${visitor.middleName ?? ""} ${visitor.lastName}`,
            visitorsIdNumber: visitor.idNumber,
            inmateToVisit: visitor.inmate,
            relationshipToInmate: visitor.relationship,
            status: "IN",
            purpose: purpose   // ✅ add purpose
        })
    })
        .then(res => res.json())
        .then(data => {
            console.log("Log result:", data);

            if (data.status === "ok" && data.action === "IN") {
                alert(`✅ ${visitor.firstName} ${visitor.lastName} checked IN`);
            } else if (data.status === "error" && data.message.includes("already checked IN")) {
                // If already IN, try OUT
                fetch("../../classes/visitors_log.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        visitorsFullName: `${visitor.firstName} ${visitor.middleName ?? ""} ${visitor.lastName}`,
                        visitorsIdNumber: visitor.idNumber,
                        inmateToVisit: visitor.inmate,
                        relationshipToInmate: visitor.relationship,
                        status: "OUT",
                        purpose: purpose
                    })
                })
                    .then(res => res.json())
                    .then(outData => {
                        if (outData.status === "ok" && outData.action === "OUT") {
                            alert(`👋 ${visitor.firstName} ${visitor.lastName} checked OUT`);
                        } else {
                            alert(`⚠ ${outData.message}`);
                        }
                    });
            } else {
                alert(`⚠ ${data.message}`);
            }

            loadVisitorLogs(currentPage);
            loadVisitorStats();
        })
        .catch(err => console.error("Log error:", err));
}

async function checkVisitorInLog(visitorsIdNumber) {
    try {
        const response = await fetch(`../../classes/visitors_log.php?visitorIdNumber=${encodeURIComponent(visitorsIdNumber)}&active=1`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        });

        const data = await response.json();

        if (data.status === "ok" && data.log) {
            console.log("Visitor IN Logs:", data.log);
            return data.log; // return array of logs
        } else {
            console.warn("Error:", data.message);
            return null;
        }
    } catch (error) {
        console.error("Fetch error:", error);
        return null;
    }
}

// Fetch visitor logs and update table
function loadVisitorLogs(page = 1, selectedDate = null) {
    const dateParam = selectedDate
        ? `&date=${selectedDate}`
        : `&date=${new Date().toISOString().slice(0, 10)}`; // default today YYYY-MM-DD

    fetch(`../../classes/visitors_log.php?page=${page}&limit=${limit}${dateParam}&t=${Date.now()}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok") {
                const tbody = document.querySelector("tbody");
                tbody.innerHTML = "";

                if (data.logs.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="8" class="no-visitors">No Visitors</td></tr>`;
                } else {
                    data.logs.forEach(log => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${log.visitorsFullName}</td>
                                <td>${log.visitorsIdNumber}</td>
                                <td>${log.inmateToVisit || '-'}</td>
                                <td>${log.relationshipToInmate || '-'}</td>
                                <td>${log.purpose || '-'}</td>
                                <td>
                                    ${log.status === 'IN'
                                ? `${formatDate(log.timeIn)}`
                                : log.status === 'OUT'
                                    ? `${formatDate(log.timeOut)}`
                                    : log.status
                            }
                                </td>
                                <td>${formatTime(log.timeIn)}</td>
                                <td>${formatTime(log.timeOut)}</td>
                            </tr>`;
                    });
                }

                console.log("API response:", data);
                // Render pagination
                renderPagination(data.page, data.totalPages, selectedDate);
            }
        })
        .catch(err => console.error("Error fetching visitors:", err));
}

function renderPagination(page, totalPages, selectedDate = null) {
    const pagination = document.getElementById("pagination");

    // Make sure totalPages is a number
    totalPages = Number(totalPages);

    // Hide pagination if no pages
    if (!totalPages || totalPages <= 0) {
        pagination.innerHTML = "";   // 🔥 clear buttons
        pagination.style.display = "none";
        return;
    }

    pagination.style.display = "block"; // show if pages exist
    pagination.innerHTML = "";

    // Ensure proper JS-safe string handling
    const safeDate = selectedDate ? `'${selectedDate}'` : 'null';

    // Previous button
    const prevDisabled = page === 1 ? "disabled" : "";
    pagination.innerHTML += `
        <button class="btn btn-sm btn-outline-primary me-1" ${prevDisabled} 
            onclick="loadVisitors(${page - 1}, ${safeDate})">Previous</button>`;

    // Page number buttons
    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `
            <button class="btn btn-sm ${i === page ? 'btn-primary' : 'btn-outline-primary'} me-1"
                onclick="loadVisitors(${i}, ${safeDate})">${i}</button>`;
    }

    // Next button
    const nextDisabled = page === totalPages ? "disabled" : "";
    pagination.innerHTML += `
        <button class="btn btn-sm btn-outline-primary" ${nextDisabled} 
            onclick="loadVisitors(${page + 1}, ${safeDate})">Next</button>`;
}

function loadVisitors(page, selectedDate = null) {
    currentPage = page;
    loadVisitorLogs(page, selectedDate);
}

function loadVisitorStats() {
    fetch('../../classes/visitors_log.php?stats=1&t=${Date.now()}')
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok") {
                document.getElementById("today-visitor").textContent = data.today;
                document.getElementById("this-week-visitor").textContent = data.week;
                document.getElementById("this-month-visitor").textContent = data.month;
                document.getElementById("overall").textContent = data.overall;
            }
        })
        .catch(err => console.error("Error fetching stats:", err));
}


function stopCamera() {
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
    clearInterval(captureInterval);
}

function formatTime(datetime) {
    if (!datetime) return '-';
    const dateObj = new Date(datetime);
    return dateObj.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatDate(datetime) {
    if (!datetime) return '-';
    const dateObj = new Date(datetime);
    return dateObj.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }); // e.g., September 20, 2025
}

// Open modal on button click
document.getElementById("selectDateBtn").addEventListener("click", function () {
    new bootstrap.Modal(document.getElementById("datePickerModal")).show();
});

// Handle date selection
document.getElementById("applyDateBtn").addEventListener("click", function () {
    const selectedDate = document.getElementById("modalDatePicker").value;
    if (selectedDate) {
        loadVisitorLogs(currentPage, selectedDate);
    }
    bootstrap.Modal.getInstance(document.getElementById("datePickerModal")).hide();
});
