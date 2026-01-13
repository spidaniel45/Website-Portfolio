/* ==========================================
   PORTFOLIO FUNCTIONS - Main JavaScript File
   
   Purpose: Handles interactivity for the portfolio site
   - Modal windows (open/close)
   - Section navigation
   - About section expansion
   ========================================== */

// Wait for entire HTML document to load before running JavaScript
// This prevents errors from trying to access elements that don't exist yet
document.addEventListener("DOMContentLoaded", function () {
    console.log('✅ Portfolio functions loaded successfully');
    
    // Initialize all features
    initializeAboutSection();
    initializeModals();
    showOnlyActiveSection(); // Initial setup for section visibility
});


/* ==========================================
   ABOUT SECTION FUNCTIONALITY
   ========================================== */

/**
 * Adds click functionality to the About Info box
 * Toggles the "expanded" class when clicked
 */
function initializeAboutSection() {
    const box = document.querySelector(".About_Info");
    
    // Check if element exists before adding event listener
    // This prevents errors if the element is not on the page
    if (box) {
        box.addEventListener("click", function () {
            // Toggle adds class if not present, removes if present
            box.classList.toggle("expanded");
        });
    }
}


/* ==========================================
   MODAL WINDOW FUNCTIONALITY
   ========================================== */

/**
 * Sets up all modal windows and their controls
 * Handles opening, closing, and switching between modals
 */
function initializeModals() {
    // === GET REFERENCES TO MODAL ELEMENTS ===
    // getElementById is faster than querySelector for IDs
    const downloadModal = document.getElementById('DownloadModal');
    const uploadModal = document.getElementById('UploadModal');
    
    // === GET REFERENCES TO BUTTONS THAT OPEN MODALS ===
    const viewDocumentsBtn = document.getElementById('ViewDocumentsButton');
    const uploadNewBtn = document.getElementById('UploadNewButton');
    const openUploadBtn = document.getElementById('OpenUploadModalButton');
    
    // === GET REFERENCES TO CLOSE BUTTONS (X buttons) ===
    // querySelector is used here because we're selecting by CSS selector
    const downloadClose = document.querySelector('#DownloadModal .close');
    const uploadClose = document.querySelector('.close-upload');

    /* ------------------------------------------
       OPEN MODAL EVENTS
       ------------------------------------------ */
    
    // Open Download Modal when "View Documents" button is clicked
    // We check if both elements exist to prevent errors
    if (viewDocumentsBtn && downloadModal) {
        viewDocumentsBtn.addEventListener('click', () => {
            // Setting display to 'flex' makes modal visible and centered
            downloadModal.style.display = 'flex';
        });
    }

    // Open Upload Modal directly (if button exists in HTML)
    if (openUploadBtn && uploadModal) {
        openUploadBtn.addEventListener('click', () => {
            uploadModal.style.display = 'flex';
        });
    }

    // Switch from Download Modal to Upload Modal
    // This provides a seamless transition between viewing and uploading
    if (uploadNewBtn && uploadModal && downloadModal) {
        uploadNewBtn.addEventListener('click', () => {
            downloadModal.style.display = 'none'; // Close download modal
            uploadModal.style.display = 'flex';    // Open upload modal
        });
    }

    /* ------------------------------------------
       CLOSE MODAL EVENTS
       ------------------------------------------ */
    
    // Close Download Modal when X button is clicked
    if (downloadClose && downloadModal) {
        downloadClose.addEventListener('click', () => {
            // Setting display to 'none' hides the modal
            downloadModal.style.display = 'none';
        });
    }

    // Close Upload Modal when X button is clicked
    if (uploadClose && uploadModal) {
        uploadClose.addEventListener('click', () => {
            uploadModal.style.display = 'none';
        });
    }
    
    /* ------------------------------------------
       CLOSE MODAL BY CLICKING OUTSIDE
       ------------------------------------------ */
    
    // Close modal when clicking outside of modal content (on dark overlay)
    // This is a common UX pattern that users expect
    window.addEventListener('click', (event) => {
        // event.target is the element that was clicked
        // If it's the modal itself (not the content inside), close it
        if (event.target === downloadModal) {
            downloadModal.style.display = 'none';
        }
        if (event.target === uploadModal) {
            uploadModal.style.display = 'none';
        }
    });
}


/* ==========================================
   SECTION NAVIGATION FUNCTIONALITY
   ========================================== */

/**
 * Shows/hides content sections based on URL hash
 * Example: #about will show only the About section
 * No hash will show all sections
 * 
 * This creates a single-page navigation experience
 */
function showOnlyActiveSection() {
    // Get the hash from URL (e.g., #about, #skills, #documents)
    // window.location.hash returns the part after # in the URL
    const hash = window.location.hash;
    
    // Get all content sections on the page
    // querySelectorAll returns a NodeList of all matching elements
    const sections = document.querySelectorAll('.content-section');
    
    if (hash) {
        // If there's a hash in URL, we want to show only that section
        
        // First, hide all sections
        sections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Then show only the target section that matches the hash
        const targetSection = document.querySelector(hash);
        if (targetSection) {
            targetSection.style.display = 'block';
            scrollIntoView({ behavior: 'smooth' });
        }
    } else {
        // No hash means user is on the main page
        // Show all sections
        sections.forEach(section => {
            section.style.display = 'block';
        });
    }
}

// Run the function immediately when page loads
// This ensures correct sections are visible on page load
showOnlyActiveSection();

// Listen for hash changes in URL (when user clicks navigation links)
// This allows section switching without page reload (Single Page Application behavior)
// Example: Clicking a link with href="#about" will trigger this event
window.addEventListener('hashchange', showOnlyActiveSection);