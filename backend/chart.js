document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('dualAxisChart').getContext('2d');

    // Initialize the chart with empty data
    const dualAxisChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [], // To be dynamically updated
            datasets: [
                {
                    label: 'Humidity (%)',
                    data: [], // To be dynamically updated
                    yAxisID: 'y-axis-humidity',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: false,
                },
                {
                    label: 'Temperature (°C)',
                    data: [], // To be dynamically updated
                    yAxisID: 'y-axis-temperature',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: false,
                },
            ],
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Time',
                        color: '#333',
                        font: {
                            size: 16,
                            weight: 'bold',
                        },
                    },
                    ticks: {
                        color: '#333',
                        font: {
                            size: 14,
                        },
                    },
                },
                'y-axis-humidity': {
                    type: 'linear',
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Humidity (%)',
                        color: '#333',
                        font: {
                            size: 16,
                            weight: 'bold',
                        },
                    },
                    ticks: {
                        color: '#333',
                        font: {
                            size: 14,
                        },
                    },
                    beginAtZero: true,
                },
                'y-axis-temperature': {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Temperature (°C)',
                        color: '#333',
                        font: {
                            size: 16,
                            weight: 'bold',
                        },
                    },
                    ticks: {
                        color: '#333',
                        font: {
                            size: 14,
                        },
                    },
                    beginAtZero: true,
                },
            },
            responsive: true,
        },
    });

    // Function to fetch the latest data and update the chart
    function fetchAndUpdateChart() {
        fetch('getData_chart.php')
            .then((response) => response.json())
            .then((data) => {
                if (data.sensors) {
                    // Extract humidity and temperature data
                    const humidityData = data.sensors.humidity.reverse(); // Reverse to chronological order
                    const temperatureData = data.sensors.temperature.reverse(); // Reverse to chronological order

                    // Extract time labels (assume both sensors have matching timestamps)
                    const labels = humidityData.map((entry) => {
                        const date = new Date(entry.time_stamp);
                        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); // Time only
                    });

                    // Extract sensor values
                    const humidityValues = humidityData.map((entry) => parseFloat(entry.value));
                    const temperatureValues = temperatureData.map((entry) => parseFloat(entry.value));

                    // Update chart data
                    dualAxisChart.data.labels = labels;
                    dualAxisChart.data.datasets[0].data = humidityValues; // Update humidity dataset
                    dualAxisChart.data.datasets[1].data = temperatureValues; // Update temperature dataset
                    dualAxisChart.update(); // Refresh the chart
                }
            })
            .catch((error) => console.error('Error fetching chart data:', error));
    }

    // Fetch data initially and then every 5 seconds
    fetchAndUpdateChart();
    setInterval(fetchAndUpdateChart, 5000); // Update every 5 seconds
});
