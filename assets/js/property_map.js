document.getElementById('getLocationBtn').addEventListener('click', function() {
    const status = document.getElementById('locationStatus');
    const mapPreview = document.getElementById('map-preview-container');
    const iframe = document.getElementById('map-iframe');

    if (navigator.geolocation) {
        status.textContent = "Detecting your coordinates...";
        
        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            
            const embedUrl = `https://www.google.com/maps?q=${lat},${lng}&z=15&output=embed`;
            iframe.src = embedUrl;

            
            mapPreview.style.display = 'block';
            
            status.textContent = "Location coordinates captured!";
            status.style.color = "#27ae60";
        }, () => {
            status.textContent = "Error: Please enable location permissions in your browser.";
            status.style.color = "#e74c3c";
        });
    }
});