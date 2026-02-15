let currentCategory = 'all';
let allHostels = [];

// Load hostels from backend
function loadHostels(category) {
    currentCategory = category;
    
    fetch(`get_hostels.php?category=${category}`)
        .then(response => response.json())
        .then(data => {
            allHostels = data;
            displayHostels(data);
        })
        .catch(error => {
            console.error('Error loading hostels:', error);
            document.getElementById('hostelsContainer').innerHTML = 
                '<p class="no-results">Error loading hostels. Please try again.</p>';
        });
}

// Display hostels
function displayHostels(hostels) {
    const container = document.getElementById('hostelsContainer');
    
    if (hostels.length === 0) {
        container.innerHTML = '<p class="no-results">No hostels found matching your criteria.</p>';
        return;
    }
    
    // Group by category
    const grouped = {};
    hostels.forEach(hostel => {
        if (!grouped[hostel.category]) {
            grouped[hostel.category] = [];
        }
        grouped[hostel.category].push(hostel);
    });
    
    let html = '';
    
    // Category titles
    const categoryTitles = {
        'tourist': 'These popular tourist hostels have a lot to offer',
        'boys': 'The popular boys hostels in Kathmandu',
        'girls': 'The popular girls hostels in Kathmandu'
    };
    
    for (let category in grouped) {
        html += `
            <div class="category-section">
                <h2 class="category-title">${categoryTitles[category] || category}</h2>
                <div class="hostel-grid">
        `;
        
        grouped[category].forEach(hostel => {
            const facilities = hostel.facilities.split(',').map(f => f.trim());
            const icon = category === 'tourist' ? '🏨' : category === 'boys' ? '👨' : '👩';
            
            html += `
                <div class="hostel-card">
                    <div class="hostel-icon">${icon}</div>
                    <span class="category-badge">${category.charAt(0).toUpperCase() + category.slice(1)}</span>
                    <h3 class="hostel-name">${hostel.name}</h3>
                    <div class="hostel-info">
                        <span class="info-icon">📍</span>
                        <span>${hostel.location}</span>
                    </div>
                    <div class="hostel-info">
                        <span class="info-icon">⭐</span>
                        <span class="rating">${hostel.rating}/5</span>
                    </div>
                    <div class="price">Starts From - ${hostel.price}</div>
                    <div class="facilities">
                        ${facilities.map(f => `<span class="facility-tag">${f}</span>`).join('')}
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

// Filter by category
function filterCategory(category) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Load hostels
    loadHostels(category);
    
    // Clear search
    document.getElementById('searchInput').value = '';
}

// Search hostels
function searchHostels() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    if (searchTerm === '') {
        displayHostels(allHostels);
        return;
    }
    
    const filtered = allHostels.filter(hostel => 
        hostel.name.toLowerCase().includes(searchTerm) ||
        hostel.location.toLowerCase().includes(searchTerm)
    );
    
    displayHostels(filtered);
}