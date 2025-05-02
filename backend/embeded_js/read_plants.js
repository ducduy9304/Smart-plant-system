document.addEventListener('DOMContentLoaded', () => {
    const plantList = document.getElementById('plant-list');
    const plantDetails = document.getElementById('plant-details'); // Details section
    const plantTitle = document.getElementById('plant-title'); // Title for the details section
    const humidityValue = document.getElementById('humidity-value'); // Humidity display
    const temperatureValue = document.getElementById('temperature-value'); // Temperature display

    let currentPlantId = null; // Track the currently displayed plant

    // Fetch plant and sensor data from the backend PHP file
    fetch('getData.php')
        .then(response => response.json())
        .then(data => {
            if (data.plants && data.plants.length > 0) {
                // Dynamically populate the plant list
                data.plants.forEach(plant => {
                    const plantItem = document.createElement('div');
                    plantItem.classList.add('item', 'online');
                    plantItem.innerHTML = `
                        <div class="icon">
                            <span class="material-symbols-outlined">forest</span>
                        </div>
                        <h3>${plant.name}</h3>
                        <button class="delete-plant-btn">X</button> <!-- Delete button -->
                    `;
                    plantList.appendChild(plantItem);

                    // Add click event to show details and update sensor values
                    plantItem.addEventListener('click', () => {
                        displayPlantDetails(plant);
                        currentPlantId = plant.plant_id; // Update the current plant ID
                        updateSensorValues(data.sensors); // Update sensor values
                    });

                    // Remove plant on clicking the delete button
                    const deleteButton = plantItem.querySelector('.delete-plant-btn');
                    deleteButton.addEventListener('click', (event) => {
                        event.stopPropagation(); // Prevent triggering the click event for the plant
                        plantList.removeChild(plantItem);
                        resetPlantDetailsIfDeleted(plant.name);
                    });
                });

                // Default to the first plant if no plant is clicked
                if (data.plants[0]) {
                    displayPlantDetails(data.plants[0]);
                    currentPlantId = data.plants[0].plant_id; // Default plant ID
                    updateSensorValues(data.sensors); // Initial update
                }
            } else {
                plantList.innerHTML = '<p>No plants found.</p>';
            }
        })
        .catch(error => console.error('Error fetching data:', error));

    // Function to display plant details in the insights section
    function displayPlantDetails(plant) {
        plantTitle.textContent = plant.name; // Update plant title
        plantDetails.innerHTML = `
            <div class="plant-image">
                <img src="plantpot.jpeg" alt="Plant Image">
            </div>
            <div class="plant-details">
                <p><strong>Location:</strong> ${plant.location}</p>
                <p><strong>Host:</strong> ${plant.host}</p>
                <p><strong>Mode:</strong> ${plant.mode}</p>
            </div>
        `;
    }

    // Function to update humidity and temperature values in the UI
    function updateSensorValues(sensors) {
        if (sensors && sensors.humidity && sensors.temperature) {
            humidityValue.textContent = `${sensors.humidity.value}%`;
            temperatureValue.textContent = `${sensors.temperature.value}°C`;
        } else {
            humidityValue.textContent = '--%'; // Default display
            temperatureValue.textContent = '--°C'; // Default display
        }
    }

    // Reset plant details if the displayed plant is deleted
    function resetPlantDetailsIfDeleted(deletedPlantName) {
        if (plantTitle.textContent === deletedPlantName) {
            plantTitle.textContent = 'Plant Details';
            plantDetails.innerHTML = `<p>Select a plant to view details.</p>`;
            humidityValue.textContent = '--%'; // Reset humidity
            temperatureValue.textContent = '--°C'; // Reset temperature
        }
    }

    // Fetch and update sensor values periodically to keep the UI updated
    function fetchSensorDataPeriodically() {
        setInterval(() => {
            fetch('getData.php')
                .then(response => response.json())
                .then(data => {
                    if (data.sensors) {
                        // If no specific plant is clicked, use the default (current) plant
                        updateSensorValues(data.sensors);
                    }
                })
                .catch(error => console.error('Error fetching updated data:', error));
        }, 5000); // Fetch every 5 seconds
    }

    // Call this function after DOM is loaded
    fetchSensorDataPeriodically();
});
