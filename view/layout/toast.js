document.addEventListener("DOMContentLoaded", function () {
    const toast = document.getElementById("toast-welcome");
    if (!toast) return;

    setTimeout(() => {
        toast.classList.add("show");
    }, 500);

    setTimeout(() => {
        cerrarToast();
    }, 8500);
});

function cerrarToast() {
    const toast = document.getElementById("toast-welcome");
    if (toast) {
        toast.classList.remove("show");
    }
}