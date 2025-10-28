document.addEventListener("DOMContentLoaded", function () {
    console.log('✅ Portfolio functions loaded successfully');
    
    // Initialize all features
    initializeAboutSection();
    initializeModals();
    showOnlyActiveSection();
});

function initializeAboutSection() {
    const box = document.querySelector(".About_Info");

    if (box) {
        box.addEventListener("click", function () {
            box.classList.toggle("expanded");
        });
    }
}

function initializeModals() {
    const downloadModal = document.getElementById('DownloadModal');
    const uploadModal = document.getElementById('UploadModal');
    
    const viewDocumentsBtn = document.getElementById('ViewDocumentsButton');
    const uploadNewBtn = document.getElementById('UploadNewButton');
    const openUploadBtn = document.getElementById('OpenUploadModalButton');

    const downloadClose = document.querySelector('#DownloadModal .close');
    const uploadClose = document.querySelector('.close-upload');

    if (viewDocumentsBtn && downloadModal) {
        viewDocumentsBtn.addEventListener('click', () => {
            downloadModal.style.display = 'flex';
        });
    }

    if (openUploadBtn && uploadModal) {
        openUploadBtn.addEventListener('click', () => {
            uploadModal.style.display = 'flex';
        });
    }
    
    if (uploadNewBtn && uploadModal && downloadModal) {
        uploadNewBtn.addEventListener('click', () => {
            downloadModal.style.display = 'none'; 
            uploadModal.style.display = 'flex'; 
        });
    }

    if (downloadClose && downloadModal) {
        downloadClose.addEventListener('click', () => {
            downloadModal.style.display = 'none';
        });
    }

    if (uploadClose && uploadModal) {
        uploadClose.addEventListener('click', () => {
            uploadModal.style.display = 'none';
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target === downloadModal) {
            downloadModal.style.display = 'none';
        }
        if (event.target === uploadModal) {
            uploadModal.style.display = 'none';
        }
    });
}

function showOnlyActiveSection() {
    const hash = window.location.hash;
    const sections = document.querySelectorAll('.content-section');
    
    if (hash) {
        sections.forEach(section => {
            section.style.display = 'none';
        });
        
        const targetSection = document.querySelector(hash);
        if (targetSection) {
            targetSection.style.display = 'block';
        }
    } else {
        sections.forEach(section => {
            section.style.display = 'block';
        });
    }
}

showOnlyActiveSection();

window.addEventListener('hashchange', showOnlyActiveSection);
