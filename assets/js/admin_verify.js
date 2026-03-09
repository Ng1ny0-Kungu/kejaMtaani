function openModal(src) {
    document.getElementById("mediaModal").style.display = "block";
    document.getElementById("modalImage").src = src;
}

function closeModal() {
    document.getElementById("mediaModal").style.display = "none";
}