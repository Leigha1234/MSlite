document.getElementById('start-tour-btn').addEventListener('click', startTour);

// Global variables
let currentStep = 0;
const steps = [
    {
        target: '#feature-1',
        text: 'Step 1: Easily find any location using the search bar. Simply type in a name, address, or keyword to get accurate results. Click on a location to view more details or take action.'
    },
    {
        target: '#feature-2',
        text: 'Step 2: Use this page to add a new location to the system. Enter key details like name, address, and category. Ensure accuracy to keep the database updated and useful.'
    },
    {
        target: '#feature-3',
        text: 'Step 3: Access and generate detailed reports based on system data. View trends, analyze performance, and export reports for further insights. Use filters to refine your data.'
    },
    {
        target: '#feature-4',
        text: 'Step 4: Manage all incoming enquiries efficiently. View, track, and respond to questions or requests. Stay organized by sorting or filtering enquiries based on priority and status.'
    },
    {
        target: '#feature-5',
        text: 'Step 5: Customize your experience by managing system settings. Update preferences, adjust user permissions, and configure notifications to suit your needs.'
    }
];

function startTour() {
    const tourOverlay = document.getElementById('tour-overlay');

    tourOverlay.style.zIndex = '9999';
    tourOverlay.style.visibility = 'visible';
    tourOverlay.style.opacity = '1';

    showStep(currentStep);
}

function showStep(stepIndex) {
    const step = steps[stepIndex];
    if (!step) return;

    const targetElement = document.querySelector(step.target);
    const tooltip = document.getElementById('tour-tooltip');
    const tourText = document.getElementById('tour-text');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const closeBtn = document.getElementById('close-tour-btn');

    if (!targetElement) {
        console.error(`Element ${step.target} not found.`);
        return;
    }

    // Scroll into view fully
    targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Remove previous highlights and apply new one
    removeRedBorders();
    targetElement.classList.add('highlight');

    // Apply red border for emphasis
    targetElement.style.border = '2px solid red';

    // Show tooltip and update text
    tooltip.style.display = 'block';
    tourText.textContent = step.text;

    // Position tooltip dynamically
    const rect = targetElement.getBoundingClientRect();
    let top = rect.top + window.scrollY - 50;
    let left = rect.left + window.scrollX + rect.width / 2;

    // Prevent tooltip from going off-screen
    if (left + tooltip.offsetWidth > window.innerWidth) {
        left = window.innerWidth - tooltip.offsetWidth - 10;
    }
    if (left < 10) {
        left = 10;
    }
    if (top < 10) {
        top = rect.bottom + window.scrollY + 10;
    }

    tooltip.style.top = `${top}px`;
    tooltip.style.left = `${left}px`;

    // Button controls
    prevBtn.style.display = stepIndex > 0 ? 'inline-block' : 'none';
    nextBtn.textContent = stepIndex < steps.length - 1 ? 'Next' : 'Finish';

    nextBtn.onclick = function() {
        removeRedBorders();
        currentStep++;
        if (currentStep < steps.length) {
            showStep(currentStep);
        } else {
            endTour();
        }
    };

    prevBtn.onclick = function() {
        removeRedBorders();
        currentStep--;
        showStep(currentStep);
    };

    closeBtn.onclick = function() {
        removeRedBorders();
        endTour();
    };
}

// Function to remove red borders from all highlighted elements
function removeRedBorders() {
    document.querySelectorAll('.highlight').forEach(el => {
        el.classList.remove('highlight');
        el.style.border = 'none';
    });
}

function endTour() {
    const tourOverlay = document.getElementById('tour-overlay');

    // Restore scrolling
    document.body.style.overflow = 'auto';
    document.documentElement.style.overflow = 'auto';
    document.body.style.position = '';
    document.documentElement.style.position = '';

    tourOverlay.style.opacity = '0';
    setTimeout(() => {
        tourOverlay.style.visibility = 'hidden';
        tourOverlay.style.zIndex = '-1';
    }, 300);

    removeRedBorders(); 
    currentStep = 0;
}

// Handle keyboard shortcuts
document.addEventListener('keydown', function(event) {
    if (document.getElementById('tour-overlay').style.visibility === 'visible') {
        if (event.key === 'Enter' || event.key === 'ArrowRight') {
            document.getElementById('next-btn').click();
        }
        if (event.key === 'ArrowLeft') {
            document.getElementById('prev-btn').click();
        }
        if (event.key === 'Escape') {
            document.getElementById('close-tour-btn').click();
        }
    }
});