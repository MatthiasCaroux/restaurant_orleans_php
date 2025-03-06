document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-container input');
    const searchButton = document.querySelector('.search-container button');
    const searchInfo = document.getElementById('recherche');

    searchButton.addEventListener('click', function() {
        const searchTerm = searchInput.value.toLowerCase();
        let found = false;

        restaurantLinks.forEach(link => {
            const restaurantName = link.querySelector('h2').textContent.toLowerCase();
            if (restaurantName.includes(searchTerm)) {
                link.style.display = 'block';
                found = true;
            } else {
                link.style.display = 'none';
            }
        });

        if (!found) {
            searchInfo.textContent = 'Aucun restaurant trouvé.';
        } else {
            searchInfo.textContent = 'Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n\'importe où dans le monde.';
        }
    });

    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            searchButton.click();
        }
    });
});

const typeSelect = document.getElementById('recherche_by_type');
const restaurantLinks = document.querySelectorAll('.restaurant-link');
typeSelect.addEventListener('change', function() {
    const selectedType = typeSelect.value.toLowerCase();
    let found = false;

    restaurantLinks.forEach(link => {
        const restaurantType = link.querySelector('.cacher').textContent.toLowerCase();
        if (restaurantType.includes(selectedType)) {
            link.style.display = 'block';
            found = true;
        } else {
            link.style.display = 'none';
        }
    });

    if (!found) {
        searchInfo.textContent = 'Aucun restaurant trouvé.';
    } else {
        searchInfo.textContent = 'Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n\'importe où dans le monde.';
    }
});