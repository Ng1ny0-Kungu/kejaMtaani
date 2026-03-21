window.toggleRatingModal = function() {
    console.log("Toggle triggered");
    const modal = document.getElementById('ratingModal');
    if (!modal) {
        console.error("Modal element 'ratingModal' not found!");
        return;
    }
    
    
    if (modal.style.display === "flex") {
        modal.style.display = "none";
    } else {
        modal.style.display = "flex";
    }
};


window.addEventListener('click', function(event) {
    const ratingModal = document.getElementById('ratingModal');
    const mediaModal = document.getElementById('mediaModal'); 
    
    if (event.target === ratingModal) {
        ratingModal.style.display = "none";
    }
    if (event.target === mediaModal) {
        if (typeof closeModal === "function") closeModal(); 
    }
});


document.addEventListener("DOMContentLoaded", function() {
    console.log("Tenant JS Initialized");

    
    const searchBtn = document.getElementById("searchBtn");
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const location = document.getElementById('searchLocation').value;
            const type = document.getElementById('propertyType').value;
            const min = document.getElementById('minPrice').value;
            const max = document.getElementById('maxPrice').value;
            
            window.location.href = `search_results.php?location=${encodeURIComponent(location)}&type=${encodeURIComponent(type)}&min=${encodeURIComponent(min)}&max=${encodeURIComponent(max)}`;
        });
    }

    
    const saveButtons = document.querySelectorAll(".btn-save");
    saveButtons.forEach(button => {
        button.addEventListener("click", () => {
            const propertyId = button.dataset.id;

            fetch("save_property.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "property_id=" + propertyId
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "saved") {
                    button.innerHTML = "Saved";
                } else if (data.status === "removed") {
                    button.innerHTML = "Save";
                }
            })
            .catch(err => console.error("Error saving property:", err));
        });
    });
});