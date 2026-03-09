document.addEventListener("DOMContentLoaded", function() {
    const steps = document.querySelectorAll(".form-step");
    let current = 0;

    
    document.querySelectorAll(".next-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            steps[current].classList.remove("active");
            current++;
            steps[current].classList.add("active");
        });
    });

    document.querySelectorAll(".prev-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            steps[current].classList.remove("active");
            current--;
            steps[current].classList.add("active");
        });
    });

    
    const depositSelect = document.getElementById("deposit_required");
    const depositField = document.getElementById("deposit_field");

    if(depositSelect) {
        depositSelect.addEventListener("change", function() {
            if (this.value === "yes") {
                depositField.classList.remove("hidden");
            } else {
                depositField.classList.add("hidden");
            }
        });
    }
});


const imageInput = document.getElementById("imageInput");
const imagePreview = document.getElementById("imagePreview");
if(imageInput) {
    imageInput.addEventListener("change", function() {
        imagePreview.innerHTML = "";
        Array.from(this.files).forEach(file => {
            const img = document.createElement("img");
            img.src = URL.createObjectURL(file);
            img.onclick = () => openModal(img.src, "image");
            imagePreview.appendChild(img);
        });
    });
}

const videoInput = document.getElementById("videoInput");
const videoPreview = document.getElementById("videoPreview");
if(videoInput) {
    videoInput.addEventListener("change", function() {
        videoPreview.innerHTML = "";
        Array.from(this.files).forEach(file => {
            const video = document.createElement("video");
            video.src = URL.createObjectURL(file);
            video.controls = true;
            video.onclick = () => openModal(video.src, "video");
            videoPreview.appendChild(video);
        });
    });
}

const modal = document.getElementById("mediaModal");
const modalContent = document.getElementById("modalContent");
const closeModal = document.querySelector(".close-modal");

function openModal(src, type) {
    modal.style.display = "block";
    modalContent.innerHTML = "";
    if (type === "image") {
        const img = document.createElement("img");
        img.src = src;
        modalContent.appendChild(img);
    } else {
        const video = document.createElement("video");
        video.src = src;
        video.controls = true;
        video.autoplay = true;
        modalContent.appendChild(video);
    }
}

if(closeModal) {
    closeModal.onclick = () => modal.style.display = "none";
}
window.onclick = e => { if (e.target === modal) modal.style.display = "none"; };