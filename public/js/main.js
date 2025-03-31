function changeButtonText(text) {
    document.getElementById('user_role_dropdown').innerText = text;
}

function setOrderOngoing(userid) {
    fetch('/requests/handle_checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: "user_id=" + userid
    })
        .then(response => response.text())
        .then(data => {
            if (data == "SUCCESS") {
            }
            else {
            }
        })
        .catch(error => alert(error));
}

function validateCheckout() {
    let locationField = document.getElementById("location");
    let alertMessage = document.getElementById("alertMessage");

    if (locationField.value.trim() === "") {
        locationField.classList.add("is-invalid"); // Show red border
        alertMessage.classList.remove("d-none"); // Show Bootstrap alert
    } else {
        locationField.classList.remove("is-invalid");
        alertMessage.classList.add("d-none"); // Hide alert if valid
        // Proceed to checkout logic (e.g., redirect or process order)
        alert("Proceeding to checkout...");
    }
}