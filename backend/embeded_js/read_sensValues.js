document.addEventListener('DOMContentLoaded', () => {
    const humidityElement = document.getElementById('humidity-value');
    const temperatureElement = document.getElementById('temperature-value');

    // Fetch data from the PHP file
    function fetchSensorData() {
        fetch('getData.php')
            .then(response => response.json())
            .then(data => {
                if (data.sensors) {
                    const humidity = data.sensors.humidity;
                    const temperature = data.sensors.temperature;

                    // Update the humidity and temperature values
                    humidityElement.textContent = `${humidity.value}%`;
                    temperatureElement.textContent = `${temperature.value}°C`;
                } else {
                    console.error('Sensor data not found in response:', data);
                }
            })
            .catch(error => {
                console.error('Error fetching sensor data:', error);
            });
    }

    // Fetch sensor data every 10 seconds
    fetchSensorData();
    setInterval(fetchSensorData, 10000);
});
