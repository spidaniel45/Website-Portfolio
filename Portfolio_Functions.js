// Wait for DOM to load
document.addEventListener("DOMContentLoaded", function () {
    console.log('✅ Portfolio functions loaded successfully');
    
    // Initialize all components
    initializeAboutSection();
    initializeModals();
});

// About section expand/collapse
function initializeAboutSection() {
    const box = document.querySelector(".About_Info");
    if (box) {
        box.addEventListener("click", function () {
            box.classList.toggle("expanded");
        });
    }
}

// Modal functionality
function initializeModals() {
    const downloadModal = document.getElementById('DownloadModal');
    const uploadModal = document.getElementById('UploadModal');
    
    const viewDocumentsBtn = document.getElementById('ViewDocumentsButton');
    const uploadNewBtn = document.getElementById('UploadNewButton');
    const openUploadBtn = document.getElementById('OpenUploadModalButton');
    
    const downloadClose = document.querySelector('#DownloadModal .close');
    const uploadClose = document.querySelector('.close-upload');

    // Open download modal
    if (viewDocumentsBtn && downloadModal) {
        viewDocumentsBtn.addEventListener('click', () => {
            downloadModal.style.display = 'flex';
        });
    }

    // Open upload modal from main button
    if (openUploadBtn && uploadModal) {
        openUploadBtn.addEventListener('click', () => {
            uploadModal.style.display = 'flex';
        });
    }

    // Open upload modal from download modal
    if (uploadNewBtn && uploadModal && downloadModal) {
        uploadNewBtn.addEventListener('click', () => {
            downloadModal.style.display = 'none';
            uploadModal.style.display = 'flex';
        });
    }

    // Close download modal
    if (downloadClose && downloadModal) {
        downloadClose.addEventListener('click', () => {
            downloadModal.style.display = 'none';
        });
    }

    // Close upload modal
    if (uploadClose && uploadModal) {
        uploadClose.addEventListener('click', () => {
            uploadModal.style.display = 'none';
        });
    }

    // Close modals on outside click
    window.addEventListener('click', (event) => {
        if (event.target === downloadModal) {
            downloadModal.style.display = 'none';
        }
        if (event.target === uploadModal) {
            uploadModal.style.display = 'none';
        }
    });
}