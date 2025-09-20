
    // Account data storage - empty by default
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

    // Edit mode state
    let isEditMode = false;

    // Function to trigger file upload
    function triggerFileUpload() {
        if (isEditMode) {
            document.getElementById('photoUpload').click();
        }
    }

    // Function to handle photo upload
    function handlePhotoUpload(event) {
        if (!isEditMode) return;
        
        const file = event.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                return;
            }

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const avatarIcon = document.getElementById('avatarIcon');
                const avatarImage = document.getElementById('avatarImage');
                
                // Hide icon and show image
                avatarIcon.classList.add('hidden');
                avatarImage.classList.remove('hidden');
                avatarImage.src = e.target.result;
                
                // Store photo data
                accountData.profilePhoto = e.target.result;
                
                console.log('Photo uploaded successfully');
            };
            reader.readAsDataURL(file);
        }
    }

    // Function to toggle edit mode
    function toggleEditMode() {
        const updateBtn = document.getElementById('updateBtn');
        const uploadBtn = document.getElementById('uploadBtn');
        const photoUpload = document.getElementById('photoUpload');
        const inputs = document.querySelectorAll('.form-input');

        if (!isEditMode) {
            // Enable edit mode
            isEditMode = true;
            
            // Enable all form inputs
            inputs.forEach(input => {
                input.disabled = false;
            });
            
            // Enable photo upload
            photoUpload.disabled = false;
            uploadBtn.classList.remove('disabled');
            
            // Change button to Save
            updateBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save';
            updateBtn.classList.add('edit-mode');
            updateBtn.onclick = saveAccountData;
            
            console.log('Edit mode enabled');
            
        } else {
            // This will be handled by saveAccountData function
        }
    }

    // Function to save account data
    function saveAccountData() {
        const form = document.getElementById('accountForm');
        const formData = new FormData(form);
        
        // Update account data object with current form values
        for (let [key, value] of formData.entries()) {
            accountData[key] = value;
        }
        
        console.log('Account data saved:', accountData);
        
        // Here you would typically send the updated data to your backend
        // Example: await fetch('/api/update-warden-account', { 
        //     method: 'PUT', 
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify(accountData) 
        // });
        
        // Disable edit mode
        disableEditMode();
        
        // Show success message
        showSaveSuccess();
        
        return true;
    }

    // Function to disable edit mode
    function disableEditMode() {
        const updateBtn = document.getElementById('updateBtn');
        const uploadBtn = document.getElementById('uploadBtn');
        const photoUpload = document.getElementById('photoUpload');
        const inputs = document.querySelectorAll('.form-input');

        isEditMode = false;
        
        // Disable all form inputs
        inputs.forEach(input => {
            input.disabled = true;
        });
        
        // Disable photo upload
        photoUpload.disabled = true;
        uploadBtn.classList.add('disabled');
        
        // Change button back to Edit
        updateBtn.innerHTML = '<i class="bi bi-pencil-square"></i> Edit';
        updateBtn.classList.remove('edit-mode');
        updateBtn.onclick = toggleEditMode;
        
        console.log('Edit mode disabled');
    }

    // Function to show save success message
    function showSaveSuccess() {
        const updateBtn = document.getElementById('updateBtn');
        const originalText = updateBtn.innerHTML;
        
        updateBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Saved!';
        updateBtn.disabled = true;
        
        setTimeout(() => {
            updateBtn.innerHTML = '<i class="bi bi-pencil-square"></i> Edit';
            updateBtn.disabled = false;
        }, 2000);
    }

    // Function to load existing account data (empty by default)
    function loadRegisteredAccountData() {
        // Load empty form fields
        document.getElementById('firstName').value = accountData.firstName || '';
        document.getElementById('lastName').value = accountData.lastName || '';
        document.getElementById('middleName').value = accountData.middleName || '';
        document.getElementById('gender').value = accountData.gender || '';
        document.getElementById('phoneNumber').value = accountData.phoneNumber || '';
        document.getElementById('birthday').value = accountData.birthday || '';
        document.getElementById('age').value = accountData.age || '';
        document.getElementById('email').value = accountData.email || '';
        
        // No profile photo by default
        const avatarIcon = document.getElementById('avatarIcon');
        const avatarImage = document.getElementById('avatarImage');
        
        if (accountData.profilePhoto) {
            avatarIcon.classList.add('hidden');
            avatarImage.classList.remove('hidden');
            avatarImage.src = accountData.profilePhoto;
        } else {
            avatarIcon.classList.remove('hidden');
            avatarImage.classList.add('hidden');
        }
        
        console.log('Empty form initialized');
    }

    // Function to calculate age from birthday
    function calculateAge(birthday) {
        if (!birthday) return '';
        
        const today = new Date();
        const birthDate = new Date(birthday);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Load empty form on page load
        loadRegisteredAccountData();

        // Birthday change handler to auto-calculate age (only in edit mode)
        document.getElementById('birthday').addEventListener('change', function(e) {
            if (isEditMode) {
                const age = calculateAge(e.target.value);
                if (age) {
                    document.getElementById('age').value = age;
                }
            }
        });

        // Form validation for edit mode
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (isEditMode) {
                    // Remove any previous validation styling
                    this.style.borderColor = '';
                    
                    // Basic validation
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        this.style.borderColor = '#ef4444';
                    } else {
                        this.style.borderColor = '#22c55e';
                    }
                }
            });

            input.addEventListener('focus', function() {
                if (isEditMode) {
                    this.style.borderColor = '#4f46e5';
                    this.style.backgroundColor = 'white';
                }
            });

            input.addEventListener('blur', function() {
                if (isEditMode) {
                    this.style.backgroundColor = '#f3f4f6';
                } else {
                    this.style.backgroundColor = '#e5e7eb';
                }
            });
        });
    });

    // Export functions for external use
    window.AccountSystem = {
        toggleEditMode,
        saveAccountData,
        loadRegisteredAccountData,
        handlePhotoUpload,
        triggerFileUpload
    };
