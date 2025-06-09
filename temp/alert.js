const playmusic = document.getElementById("play");
const textInput = document.getElementById("text1");
const alertDiv = document.getElementById("alert");
const alertSound = document.getElementById("alertSound");
const p1 = document.getElementById("para");

function showAlert() {
    const text = textInput.value.trim(); // Get the trimmed value of the input
    
    if (text !== "") {
        p1.textContent = "New data added successfully!";
        alertDiv.classList.remove("hidden");
        alertSound.play();

        // Hide the alert after a certain duration (e.g., 5 seconds)
        setTimeout(function () {
            alertDiv.classList.add("hidden");
        }, 5000); // Adjust the duration as needed
    }else {
        p1.textContent = "Please Add a Data"; // Update paragraph text for no data
        alertDiv.classList.remove("hidden");
        
        // You may choose to play a different sound for errors here, if desired
        
        // Hide the alert after a certain duration (e.g., 5 seconds)
        setTimeout(function () {
            alertDiv.classList.add("hidden");
        }, 5000); // Adjust the duration as needed
    }
    
}

// Add an event listener to the button to trigger the alert and sound when clicked
playmusic.addEventListener("click", showAlert);
    