// js/wiki.js
console.log("wiki.js loaded");

function toggleCategory(categoryId) {
    var list = document.getElementById("category-" + categoryId);
    if (list) {
        list.style.display = (list.style.display === "none" || list.style.display === "") ? "block" : "none";
    }
}

function openCreateCategoryModal() {
    document.getElementById("category-modal").style.display = "block";
    document.getElementById("category-modal-title").textContent = "Create Category";
    document.getElementById("category_id").value = "";
    document.getElementById("category_name").value = "";
}

function openEditCategoryModal(catId, catName) {
    document.getElementById("category-modal").style.display = "block";
    document.getElementById("category-modal-title").textContent = "Edit Category";
    document.getElementById("category_id").value = catId;
    document.getElementById("category_name").value = catName;
}

function openCreateTabModal(categoryId = null) {
    document.getElementById("tab-modal").style.display = "block";
    if (categoryId) {
        document.getElementById("tab_category_id").value = categoryId;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

window.onclick = function(event) {
    var modals = document.getElementsByClassName("modal");
    for (var i = 0; i < modals.length; i++) {
        if (event.target == modals[i]) {
            modals[i].style.display = "none";
        }
    }
}
