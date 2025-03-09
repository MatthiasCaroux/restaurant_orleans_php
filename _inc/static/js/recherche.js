document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-container input');
    const searchButton = document.querySelector('.search-container button');
    const searchInfo = document.getElementById('recherche');
    const typeSelect = document.getElementById('recherche_by_type');
    const vegetarianToggle = document.getElementById('filter-vegetarian');
    const wheelchairToggle = document.getElementById('filter-wheelchair');
    const restaurantLinks = document.querySelectorAll('.restaurant-link');

    function filterRestaurants() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = typeSelect.value.toLowerCase();
        const isVegetarianEnabled = vegetarianToggle.checked;
        const isWheelchairEnabled = wheelchairToggle.checked;
        let found = false;

        restaurantLinks.forEach(link => {
            const restaurantInfo = link.querySelector('.restaurant-info');
            const restaurantName = restaurantInfo.querySelector('h2').textContent.toLowerCase();
            const restaurantType = link.querySelector('.cacher').textContent.trim().toLowerCase();
            
            // Vérifier la présence des icônes pour les filtres
            const hasVegetarian = restaurantInfo.querySelector('.restaurant-attributes .fa-leaf') !== null;
            const hasWheelchair = restaurantInfo.querySelector('.restaurant-attributes .fa-wheelchair') !== null;

            const matchesSearch = searchTerm === "" || restaurantName.includes(searchTerm);
            const matchesType = selectedType === "tout" || restaurantType.includes(selectedType);
            const matchesVegetarian = !isVegetarianEnabled || hasVegetarian;
            const matchesWheelchair = !isWheelchairEnabled || hasWheelchair;

            if (matchesSearch && matchesType && matchesVegetarian && matchesWheelchair) {
                link.style.display = 'block';
                found = true;
            } else {
                link.style.display = 'none';
            }
        });

        searchInfo.textContent = found ? 
            'Trouvez des restaurants, hôtels et bien plus encore, près de chez vous ou n\'importe où dans le monde.' :
            'Aucun restaurant trouvé.';

        console.log('Recherche:', searchTerm);
        console.log('Type sélectionné:', selectedType);
        console.log('Match type:', found);
    }

    // Ajouter les écouteurs d'événements
    searchButton.addEventListener('click', filterRestaurants);
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            filterRestaurants();
        }
    });
    typeSelect.addEventListener('change', filterRestaurants);
    vegetarianToggle.addEventListener('change', filterRestaurants);
    wheelchairToggle.addEventListener('change', filterRestaurants);
});
