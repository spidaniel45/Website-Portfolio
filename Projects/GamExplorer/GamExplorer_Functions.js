function showForm(formId) {
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("Active"));
    document.getElementById(formId).classList.add("Active");
}