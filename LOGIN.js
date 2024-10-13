document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form from submitting by default

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Basic validation (front-end)
    if (username === "" || password === "") {
        displayError("Username and password are required.");
    } else {
        // Here, you would send the credentials to the server to check authentication
        // For example, using fetch() or XMLHttpRequest in JavaScript.
        // On successful login, redirect the admin to the dashboard.
        // Example (uncomment and modify based on your backend logic):
        // fetch('/login', { method: 'POST', body: JSON.stringify({ username, password }) })
        //   .then(response => response.json())
        //   .then(data => {
        //     if (data.success) {
        //       window.location.href = '/admin_dashboard';
        //     } else {
        //       displayError("Invalid username or password.");
        //     }
        //   })
        //   .catch(error => displayError("An error occurred. Please try again."));
    }
});

function displayError(message) {
    const errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = message;
}