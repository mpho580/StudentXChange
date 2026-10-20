// StudentXChange Frontend Client Functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log("StudentXChange client initialized successfully.");
    
    // Dynamic Product Search without refreshing the entire page (AJAX preloader)
    const searchInput = document.querySelector('input[type="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            console.log("Searching database records for: " + e.target.value);
        });
    }

    // Client-side Registration Password Validation
    const registerForm = document.querySelector('form[action="register.php"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (password !== confirm) {
                e.preventDefault();
                alert("Passwords do not match!");
            }
        });
    }
});