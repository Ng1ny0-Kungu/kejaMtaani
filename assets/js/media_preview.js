document.addEventListener("DOMContentLoaded", function () {

    const imageInput = document.getElementById("imageInput");
    const videoInput = document.getElementById("videoInput");
    const previewGrid = document.getElementById("previewGrid");

    if (!imageInput || !videoInput || !previewGrid) return;

    let imageFiles = [];
    let videoFiles = [];

    function updateInputFiles(input, filesArray) {
        const dataTransfer = new DataTransfer();
        filesArray.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }

    function renderPreview() {
        previewGrid.innerHTML = "";

        // Render images
        imageFiles.forEach((file, index) => {
            createPreviewCard(file, "image", index);
        });

        // Render videos
        videoFiles.forEach((file, index) => {
            createPreviewCard(file, "video", index);
        });
    }

    function createPreviewCard(file, type, index) {
        const reader = new FileReader();

        reader.onload = function (e) {

            const card = document.createElement("div");
            card.classList.add("media-card");

            let mediaElement;

            if (type === "image") {
                mediaElement = document.createElement("img");
            } else {
                mediaElement = document.createElement("video");
                mediaElement.controls = true;
            }

            mediaElement.src = e.target.result;
            mediaElement.style.width = "100%";
            mediaElement.style.borderRadius = "8px";

            const removeBtn = document.createElement("button");
            removeBtn.textContent = "Remove";
            removeBtn.classList.add("remove-btn");

            removeBtn.onclick = function () {
                if (type === "image") {
                    imageFiles.splice(index, 1);
                    updateInputFiles(imageInput, imageFiles);
                } else {
                    videoFiles.splice(index, 1);
                    updateInputFiles(videoInput, videoFiles);
                }
                renderPreview();
            };

            card.appendChild(mediaElement);
            card.appendChild(removeBtn);
            previewGrid.appendChild(card);
        };

        reader.readAsDataURL(file);
    }

    imageInput.addEventListener("change", function () {
        const newFiles = Array.from(this.files);

        newFiles.forEach(file => {
            imageFiles.push(file);
        });

        updateInputFiles(imageInput, imageFiles);
        renderPreview();
    });

    videoInput.addEventListener("change", function () {
        const newFiles = Array.from(this.files);

        newFiles.forEach(file => {
            videoFiles.push(file);
        });

        updateInputFiles(videoInput, videoFiles);
        renderPreview();
    });

});