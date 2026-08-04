<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Server Settings</title> {{-- Changed title for better clarity --}}
    {{-- Assuming you have a meta tag for CSRF token if needed for other forms/posts --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-900 min-h-screen text-gray-100"> {{-- Added text-gray-100 for overall body text color --}}

<div class="max-w-6xl mx-auto px-4 py-8"> {{-- Added padding and mx-auto for centering --}}
    {{-- Main Heading --}}
    <h1 class="text-4xl font-bold text-center mb-8 text-white">Server Status Overview</h1> {{-- Centered heading --}}

    <div class="space-y-4" id="servers-container">
        </div>
</div>

    <!-- Spinner Container -->
    <div id="spinnerContainer" class="spinner-container">
        <div class="spinner"></div>
    </div>



                <style>


                            /* Your existing CSS styles */

                    /* Spinner Container */
                    .spinner-container {
                        display: none; /* Hidden by default */
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        z-index: 1000;
                    }

                    /* Spinner Style */
                    .spinner {
                        width: 17.6px;
                        height: 17.6px;
                        border-radius: 17.6px;
                        box-shadow: 44px 0px 0 0 rgba(0, 0, 0, 0.2), 35.6px 26px 0 0 rgba(0, 0, 0, 0.4), 13.64px 41.8px 0 0 rgba(0, 0, 0, 0.6), -13.64px 41.8px 0 0 rgba(0, 0, 0, 0.8), -35.6px 26px 0 0 #000000;
                        animation: spinner-b87k6z 1.4s infinite linear;
                    }

                    @keyframes spinner-b87k6z {
                        to {
                            transform: rotate(360deg);
                        }
                    }

                        /* Responsive layout - makes the two columns/boxes stack on top of each other instead of next to each other, on small screens */
                        @media (max-width: 600px) {
                            nav, article {
                                width: 100%;
                                height: auto;
                            }
                        }




                </style>



        <script>



        function myFunc1() {
            // Show the spinner
            document.getElementById('spinnerContainer').style.display = 'block';

            // Change the iframe source after a short delay to simulate loading
            setTimeout(function () {
                document.getElementsByName('myFrame')[0].src = ok;

                // Hide the spinner after the iframe has loaded
                document.getElementsByName('myFrame')[0].onload = function () {
                    document.getElementById('spinnerContainer').style.display = 'none';
                };
            }, 500); // Adjust the delay as needed
        }



    </script>

<script>
    window.onload = function () {


        //myFunc1();


        // Define the URL using Laravel's route helper, just like with $.ajax
        const serversStatusUrl = "{{ route('statuses') }}"; // Make sure 'statuses' is a named route

        fetch(serversStatusUrl) // Use the dynamically generated URL
            .then(response => {
                // Always check if the response is OK (status 200-299)
                if (!response.ok) {
                    // If not OK, throw an error to be caught by .catch()
                    return response.text().then(text => {
                        throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                    });
                }
                return response.json();
            })
.then(data => {
    const container = document.getElementById('servers-container');
    // Clear any previous content or loading messages
    container.innerHTML = '';

    if (data.length === 0) {
        container.innerHTML = `<p class="text-gray-400 text-center p-4">No server data available.</p>`;
        return;
    }

                data.forEach(server => {
                    const statusColors = {
                        running: 'bg-green-100 text-green-800',
                        stopped: 'bg-red-100 text-red-800',
                        maintenance: 'bg-yellow-100 text-yellow-800'
                    };

                    // Determine if the server is in maintenance mode or running
                    const isDisabled = server.status === 'maintenance' || server.status === 'running';
                    const buttonDisabled = isDisabled ? 'disabled' : '';
                    const buttonClasses = isDisabled ? 'bg-gray-500 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600';

                    const serverCard = `
                    <div class="bg-gray-800 rounded-lg shadow-md p-4 flex items-center justify-between">
                        <div class="flex items-center space-x-4 flex-1">
                            <label class="text-lg font-semibold text-gray-200 w-24">${server.name} </label>
                            <input type="text" value="${server.ip}" class="border border-gray-600 bg-gray-700 text-white rounded px-3 py-2 w-64" readonly>
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${statusColors[server.status] ?? 'bg-gray-200 text-gray-800'}">
                                ${server.status.charAt(0).toUpperCase() + server.status.slice(1)}
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <button class="${buttonClasses} text-white px-4 py-2 rounded transition" data-server-name="${server.name}" ${buttonDisabled}>Start</button>
                        </div>
                    </div>`;

                    container.innerHTML += serverCard;
                });

    
                container.querySelectorAll('button[data-server-name]').forEach(button => {
                button.addEventListener('click', (event) => {
                const serverName = event.target.dataset.serverName;
                alert(`Attempting to start ${serverName} server...`);


        const baseUrl = "{{ route('function1') }}";
        const serversStatusUrl = `${baseUrl}?serverName=${serverName}`; 
        window.location.reload();
        


        fetch(serversStatusUrl) // Use the dynamically generated URL
            .then(response => {
                // Always check if the response is OK (status 200-299)
                if (!response.ok) {

                alert('failed');
                    // If not OK, throw an error to be caught by .catch()
                    return response.text().then(text => {
                        throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                    });
                }

                 alert('passed');
                window.location.reload();
                return response.json();
            })
                    .then(data => {
                        console.log("Data successfully fetched:", data);
                        // Perform any operations with 'data' here

                        // Then, reload the page
                        window.location.reload();
                    })

                    });
                });
            })
            .catch(error => {
                console.error('Failed to load server data:', error);
                const container = document.getElementById('servers-container');
                container.innerHTML = `<p class="text-red-500 text-center p-4">Error loading server data: ${error.message}. Please try again later.</p>`;
            });
    };
</script>

</body>
</html>