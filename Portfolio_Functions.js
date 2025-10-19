document.addEventListener("DOMContentLoaded", function () {
    const box = document.querySelector(".About_Info");
    box.addEventListener("click", function () {
    box.classList.toggle("expanded");
    });
  });

const downloadModal = document.getElementById('DownloadModal');
const uploadModal = document.getElementById('UploadModal');

const viewDocumentsBtn = document.getElementById('ViewDocumentsButton');
const uploadNewBtn = document.getElementById('UploadNewButton');
const openUploadBtn = document.getElementById('OpenUploadModalButton');

const downloadClose = document.querySelector('#DownloadModal .close');
const uploadClose = document.querySelector('.close-upload');

if (viewDocumentsBtn && downloadModal) {
    viewDocumentsBtn.onclick = () => {
        downloadModal.style.display = 'flex';
    };
}

if (openUploadBtn && uploadModal) {
    openUploadBtn.onclick = () => {
        uploadModal.style.display = 'block';
    };
}

if (downloadClose && downloadModal) {
    downloadClose.onclick = () => {
        downloadModal.style.display = 'none';
    };
}

if (uploadClose && uploadModal) {
    uploadClose.onclick = () => {
        uploadModal.style.display = 'none';
    };
}

window.onclick = (event) => {
    if (event.target === downloadModal) {
        downloadModal.style.display = 'none';
    }
    if (event.target === uploadModal) {
        uploadModal.style.display = 'none';
    }
};

console.log('✅ Portfolio functions loaded successfully');