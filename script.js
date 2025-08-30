const NavBar = document.getElementById("nav-bar");
const btn = document.getElementById("show-hide");
const registerbtn = document.getElementById("register-button");
const searchInput = document.getElementById("search-input");
const cardContainer = document.getElementById("card-container");
const loader = document.getElementById("loader");

// Custom modal logic to replace alert() and confirm()
function showModal(message, onConfirm, onCancel) {
    const modal = document.getElementById('custom-modal');
    const modalMessage = document.getElementById('custom-modal-message');
    const okButton = document.getElementById('custom-modal-ok-btn');
    const cancelButton = document.getElementById('custom-modal-cancel-btn');

    modalMessage.textContent = message;

    okButton.onclick = () => {
        if (onConfirm) {
            // If onConfirm is a string, assume it's a function name
            if (typeof onConfirm === 'string') {
                window[onConfirm]();
            } else {
                onConfirm();
            }
        }
        modal.classList.add('hidden');
    };
    
    // Check if onCancel is provided to show/hide the cancel button
    if (onCancel) {
        cancelButton.classList.remove('hidden');
        cancelButton.onclick = () => {
            if (typeof onCancel === 'string') {
                window.location.href = onCancel;
            } else {
                onCancel();
            }
            modal.classList.add('hidden');
        };
    } else {
        cancelButton.classList.add('hidden');
    }

    modal.classList.remove('hidden');
}


function ShowHide() {
    document.getElementById("nav-bar").classList.toggle("show");
}

// Open login modal
document.querySelectorAll('.login-section a')[1].addEventListener('click', function (e) {
    e.preventDefault();
    document.getElementById("login-modal").classList.remove("hidden");
});

// Close modal
function closeLogin() {
    document.getElementById("login-modal").classList.add("hidden");
}


function registerShow() {
    document.getElementById("login-modal").classList.add("hidden");
    document.getElementById("register-modal").classList.remove("hidden");
}

// Close modal
function closeRegister() {
    document.getElementById("register-modal").classList.add("hidden");
}


function loginShow() {
    var modal = document.getElementById('login-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
    // Optionally hide register modal
    var regModal = document.getElementById('register-modal');
    if (regModal) {
        regModal.classList.add('hidden');
    }
}

// Debounce function to limit API calls
function debounce(func, delay) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

// Function to fetch and display search results
async function fetchAndDisplayResults(query) {
    // Show the loader while fetching data
    loader.style.display = 'block';

    try {
        const response = await fetch(`search.php?search=${encodeURIComponent(query)}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const results = await response.json();
        
        // Clear the current cards
        cardContainer.innerHTML = '';
        
        if (results.length > 0) {
            // Dynamically create and append new cards
            results.forEach(item => {
                const availabilityClass = item.availability === 'Available' ? 'available' : 'not-available';
                const cardHtml = `
                    <div class="card">
                        <img src="${item.image_path}" alt="${item.name}" />
                        <div class="card-content">
                            <h3>${item.name}</h3>
                            <div class="available-rating">
                                <p class="availability ${availabilityClass}">${item.availability}</p>
                                <div class="ratings">⭐⭐⭐⭐☆</div>
                            </div>
                        </div>
                        <div class="button-container">
                            <a href="index.php?action=rent_now&item_id=${item.id}" class="cart-btn">Make a rent</a>
                            <a href="index.php?action=add_to_cart&item_id=${item.id}" class="cart-btn"><i
                                class="fas fa-cart-plus"></i> Add to Cart</a>
                        </div>
                    </div>
                `;
                cardContainer.innerHTML += cardHtml;
            });
        } else {
            // Display a message if no results are found
            cardContainer.innerHTML = '<p style="text-align: center; color: #555;">No equipment found. Try a different search.</p>';
        }

    } catch (error) {
        console.error("Error fetching search results:", error);
        cardContainer.innerHTML = '<p style="text-align: center; color: red;">Failed to load equipment.</p>';
    } finally {
        // Hide the loader
        loader.style.display = 'none';
    }
}

// Add event listener to the search input with debouncing
searchInput.addEventListener('keyup', debounce(function() {
    const query = this.value;
    fetchAndDisplayResults(query);
}, 300)); // 300ms delay

// Initial load of all items when the page loads
window.onload = function() {
    fetchAndDisplayResults('');
    loader.style.display = 'none';
};


// Attach click events to card images for details modal
document.querySelectorAll('.card img').forEach(img => {
    img.addEventListener('click', function() {
        const card = this.closest('.card');
        const name = card.querySelector('h3').textContent;
        const availability = card.querySelector('.availability').textContent;
        const imageSrc = this.src;

        // Fill modal
        document.getElementById('detail-name').textContent = name;
        document.getElementById('detail-availability').textContent = availability;
        document.getElementById('detail-image').src = imageSrc;
        document.getElementById('detail-description').textContent =
            "This is a detailed description about " + name + ". (You can pull real description from DB if available)";

        // Show modal
        document.getElementById('equipment-detail').classList.remove('hidden');
    });
});


// Close detail modal
document.getElementById('close-detail').addEventListener('click', () => {
    document.getElementById('equipment-detail').classList.add('hidden');
});

