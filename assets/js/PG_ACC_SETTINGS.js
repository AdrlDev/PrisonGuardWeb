let accountData = {
  firstName: '',
  lastName: '',
  middleName: '',
  gender: '',
  phoneNumber: '',
  birthday: '',
  age: '',
  email: '',
  profilePhoto: null
};

let isEditMode = false;

// Upload handling
function triggerFileUpload() {
  if (isEditMode) document.getElementById('photoUpload').click();
}

function handlePhotoUpload(event) {
  if (!isEditMode) return;
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) return alert('Please select a valid image file.');
  if (file.size > 5 * 1024 * 1024) return alert('File must be < 5MB.');

  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('avatarIcon').classList.add('hidden');
    const img = document.getElementById('avatarImage');
    img.classList.remove('hidden');
    img.src = e.target.result;
    accountData.profilePhoto = e.target.result;
  };
  reader.readAsDataURL(file);
}

// Toggle edit mode
function toggleEditMode() {
  const btn = document.getElementById('updateBtn');
  const uploadBtn = document.getElementById('uploadBtn');
  const photoUpload = document.getElementById('photoUpload');
  const inputs = document.querySelectorAll('.form-input');

  isEditMode = true;
  inputs.forEach(i => i.disabled = false);
  photoUpload.disabled = false;
  uploadBtn.classList.remove('disabled');

  btn.innerHTML = '<i class="bi bi-check-circle"></i> Save';
  btn.classList.add('edit-mode');
  btn.onclick = saveAccountData;
}

// Save account
function saveAccountData() {
  const form = new FormData(document.getElementById('accountForm'));
  form.forEach((val, key) => accountData[key] = val);

  disableEditMode();
  showSaveSuccess();
}

// Disable edit mode
function disableEditMode() {
  const btn = document.getElementById('updateBtn');
  const uploadBtn = document.getElementById('uploadBtn');
  const photoUpload = document.getElementById('photoUpload');
  const inputs = document.querySelectorAll('.form-input');

  isEditMode = false;
  inputs.forEach(i => i.disabled = true);
  photoUpload.disabled = true;
  uploadBtn.classList.add('disabled');

  btn.innerHTML = '<i class="bi bi-pencil-square"></i> Edit';
  btn.classList.remove('edit-mode');
  btn.onclick = toggleEditMode;
}

// Success feedback
function showSaveSuccess() {
  const btn = document.getElementById('updateBtn');
  btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Saved!';
  btn.disabled = true;
  setTimeout(() => {
    btn.innerHTML = '<i class="bi bi-pencil-square"></i> Edit';
    btn.disabled = false;
  }, 2000);
}

// Load form
function loadRegisteredPGData() {
  document.getElementById('firstName').value = accountData.firstName;
  document.getElementById('lastName').value = accountData.lastName;
  document.getElementById('middleName').value = accountData.middleName;
  document.getElementById('gender').value = accountData.gender;
  document.getElementById('phoneNumber').value = accountData.phoneNumber;
  document.getElementById('birthday').value = accountData.birthday;
  document.getElementById('age').value = accountData.age;
  document.getElementById('email').value = accountData.email;

  const icon = document.getElementById('avatarIcon');
  const img = document.getElementById('avatarImage');
  if (accountData.profilePhoto) {
    icon.classList.add('hidden');
    img.classList.remove('hidden');
    img.src = accountData.profilePhoto;
  }
}

// Age calculation
function calculateAge(birthday) {
  if (!birthday) return '';
  const today = new Date(), bdate = new Date(birthday);
  let age = today.getFullYear() - bdate.getFullYear();
  const m = today.getMonth() - bdate.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < bdate.getDate())) age--;
  return age;
}

// Init
document.addEventListener('DOMContentLoaded', () => {
  loadRegisteredPGData();
  document.getElementById('birthday').addEventListener('change', e => {
    if (isEditMode) document.getElementById('age').value = calculateAge(e.target.value);
  });
});

// Expose
window.AccountSystem = { toggleEditMode, saveAccountData, loadRegisteredPGData, handlePhotoUpload, triggerFileUpload };
