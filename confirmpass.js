// Basic form validation
document.getElementById('registerForm').addEventListener('submit', function(event) {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm-password').value;
    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        event.preventDefault(); // Prevent form submission
    }
});

// ALTER TABLE accounts ADD verification VARCHAR(255)

// ALTER TABLE accounts ADD verified tinyint(1)