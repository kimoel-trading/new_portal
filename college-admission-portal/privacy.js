function showVideo() {
    document.getElementById('noticeCard').classList.add('hidden');
    document.getElementById('videoSection').classList.remove('hidden');
}

function nextStep() {
    // Mark as seen in sessionStorage
    sessionStorage.setItem('hasSeenPrivacy', 'true');
    
    // Hide the privacy overlay and show the welcome page
    const overlay = document.getElementById('privacyOverlay');
    const mainContent = document.getElementById('mainContent');
    
    if (overlay) {
        overlay.classList.add('hidden');
    }
    
    if (mainContent) {
        mainContent.classList.remove('blurred');
    }
}

// ===== INITIALIZE PRIVACY OVERLAY =====
(function () {
    // Check if user has already seen the privacy notice (using sessionStorage)
    const hasSeenPrivacy = sessionStorage.getItem('hasSeenPrivacy');
    
    if (!hasSeenPrivacy) {
        // Show privacy overlay (already visible by default)
        // Main content is already blurred by default
        const overlay = document.getElementById('privacyOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
        }
    } else {
        // User has already seen it, hide overlay and remove blur
        const overlay = document.getElementById('privacyOverlay');
        const mainContent = document.getElementById('mainContent');
        
        if (overlay) {
            overlay.classList.add('hidden');
        }
        
        if (mainContent) {
            mainContent.classList.remove('blurred');
        }
    }
})();

// ===== SHOW LOGIN SUCCESS TOAST (same style as landing page) =====
(function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('login') === 'success') {
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            document.body.appendChild(toast);
        }
        toast.textContent = 'Login successful!';
        toast.className = 'toast success show';
        setTimeout(() => {
            toast.className = 'toast';
        }, 3000);
    }
})();