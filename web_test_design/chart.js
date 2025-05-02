const ctx = document.getElementById('dualAxisChart').getContext('2d');
const dualAxisChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['12:00', '12:30', '13:00', '13:30', '14:00', '14:30'], // Example time data
        datasets: [
            {
                label: 'Humidity (%)',
                data: [60, 65, 70, 68, 72, 75], // Example humidity data
                yAxisID: 'y-axis-humidity',
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: false,
            },
            {
                label: 'Temperature (°C)',
                data: [22, 23, 21, 22, 24, 23], // Example temperature data
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
                    color: '#333', // Darker color for the title
                    font: {
                        size: 16, // Larger font size for the title
                        weight: 'bold'
                    }
                },
                ticks: {
                    color: '#333', // Darker color for tick labels
                    font: {
                        size: 14, // Larger font size for tick labels
                    }
                }
            },
            'y-axis-humidity': {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Humidity (%)',
                    color: '#333', // Darker color for the title
                    font: {
                        size: 16, // Larger font size for the title
                        weight: 'bold'
                    }
                },
                ticks: {
                    color: '#333', // Darker color for tick labels
                    font: {
                        size: 14, // Larger font size for tick labels
                    }
                },
                beginAtZero: true,
            },
            'y-axis-temperature': {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Temperature (°C)',
                    color: '#333', // Darker color for the title
                    font: {
                        size: 16, // Larger font size for the title
                        weight: 'bold'
                    }
                },
                ticks: {
                    color: '#333', // Darker color for tick labels
                    font: {
                        size: 14, // Larger font size for tick labels
                    }
                },
                beginAtZero: true,
            },
        },
        responsive: true,
    },
});
