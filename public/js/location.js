const API_KEY = "AIzaSyDBM2Uks3o02p1Vx9PAntKYvb-smBVzhCI";

// Automatically get's user's current location
function getUserLocation() {
    if ("geolocation" in navigator) {

        navigator.geolocation.getCurrentPosition(async function (position) {

            let lat = position.coords.latitude;
            let lng = position.coords.longitude;

            // Show User's Current Location automatically.
            let address = await getReadableAddress(lat, lng);
            document.getElementById("location").value = address;
            toggleSubmitButton();
        }, function (error) {
            console.error("Error getting location:", error);
            alert("Unable to retrieve location. Please allow location access.");
        });
    } else {
        alert("Geolocation is not supported by your browser. Please enter location manually.");
    }
}

// Converts the long lat from the navigator to a readable address for the user to verify.
async function getReadableAddress(lat, lng) {
    const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${API_KEY}`;
    let response = await fetch(url);
    let data = await response.json();
    return data.status === "OK" ? data.results[0].formatted_address : "Address not found";
}

// Convert on server side when user manually enters
async function convertAddressToLatLng() {
    let address = document.getElementById("location").value;

    if (!address) return alert("Please enter an address");

    const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${API_KEY}`;
    let response = await fetch(url);
    let data = await response.json();

    if (data.status === "OK") {
        let location = data.results[0].geometry.location;
        return [location.lat, location.lng];
    } else {
        alert("Address not found");
    }
}

async function sendToServer() {

    let address = document.getElementById("location").value;

    if (!address) return alert("No location data available!");

    let [lat, lng] = await convertAddressToLatLng();

    let response = await fetch("/requests/process_save_location.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ latitude: lat, longitude: lng, address: address })
    });

    let result = await response.json();

    if (result.redirect) {
        window.location.href = result.redirect;
    } else {
        alert("Something went wrong!");
    }
}

async function saveLocation() {

    let address = document.getElementById("location").value;

    if (!address) return alert("No location data available!");

    let [lat, lng] = await convertAddressToLatLng();

    let response = await fetch("/requests/process_save_location.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ latitude: lat, longitude: lng, address: address })
    });

    let result = await response.json();

    if (result.message) {
        let locationButton = document.getElementById("location-button");
        locationButton.disabled = true;
    }
    else {
        alert("Something went wrong!");
    }
}


function toggleSubmitButton() {
    let inputField = document.getElementById("location");
    let locationButton = document.getElementById("location-button");

    if (inputField.value.trim() !== "") {
        locationButton.disabled = false;
    } else {
        locationButton.disabled = true;
    }
}

function getDeliveryRiderLocation() {
    if ("geolocation" in navigator) {

        navigator.geolocation.getCurrentPosition(async function (position) {

            let lat = position.coords.latitude;
            let lng = position.coords.longitude;

            // Show User's Current Location automatically.
            let address = await getReadableAddress(lat, lng);

            let response = await fetch("/requests/process_save_location.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ latitude: lat, longitude: lng, address: address })
            });

            let result = await response.json();

            if (result.message) {
                alert("Location Saved Successfully!")
            }
            else {
                alert("Something went wrong!");
            }


        }, function (error) {
            console.error("Error getting location:", error);
            alert("Unable to retrieve location. Please allow location access.");
        });
    } else {
        alert("Geolocation is not supported by your browser. Please enter location manually.");
    }
}

function SaveDeliveryRiderLocation(order) {

    navigator.geolocation.getCurrentPosition(
        (position) => {
            let lat = position.coords.latitude;
            let lng =  position.coords.longitude;
            // Store Delivery Rider's Current Location
            fetch('/requests/process_deliverer_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `latitude=${lat}&longitude=${lng}&order_id=` + order
            })
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(error => alert(error));
        },
        (error) => {
            console.error("Error getting location:", error);
        }
    );
}
