document.addEventListener('DOMContentLoaded', function() {
    // Éléments de filtre
    const searchInput = document.getElementById('recherche_texte');
    const typeSelect = document.getElementById('recherche_by_type');
    const vegetarianFilter = document.getElementById('filter-vegetarian');
    const wheelchairFilter = document.getElementById('filter-wheelchair');
    const openNowFilter = document.getElementById('filter-open-now');
    const filterForm = document.getElementById('filter-form');
    
    // Options pour les filtres en temps réel (si souhaité)
    const enableRealTimeFiltering = false;
    
    if (enableRealTimeFiltering) {
        // Fonction pour appliquer les filtres en temps réel (JavaScript côté client)
        function applyFiltersRealTime() {
            const searchText = searchInput.value.toLowerCase();
            const selectedType = typeSelect.value;
            const showVegetarian = vegetarianFilter.checked;
            const showWheelchair = wheelchairFilter.checked;
            const showOpenNow = openNowFilter.checked;
            
            const restaurants = document.querySelectorAll('.restaurant');
            
            restaurants.forEach(function(restaurant) {
                const restaurantName = restaurant.querySelector('h2').textContent.toLowerCase();
                const restaurantType = restaurant.getAttribute('data-type');
                const isVegetarian = restaurant.getAttribute('data-vegetarian') === 'true';
                const hasWheelchair = restaurant.getAttribute('data-wheelchair') === 'true';
                const isOpen = restaurant.getAttribute('data-open') === 'true';
                
                // Vérifier tous les critères de filtre
                const matchesSearch = restaurantName.includes(searchText);
                const matchesType = selectedType === 'tout' || restaurantType === selectedType;
                const matchesVegetarian = !showVegetarian || isVegetarian;
                const matchesWheelchair = !showWheelchair || hasWheelchair;
                const matchesOpenNow = !showOpenNow || isOpen;
                
                // Afficher ou masquer le restaurant en fonction des filtres
                if (matchesSearch && matchesType && matchesVegetarian && matchesWheelchair && matchesOpenNow) {
                    restaurant.parentNode.style.display = '';
                } else {
                    restaurant.parentNode.style.display = 'none';
                }
            });
        }
        
        // Ajouter des écouteurs d'événements pour tous les filtres
        searchInput.addEventListener('input', applyFiltersRealTime);
        typeSelect.addEventListener('change', applyFiltersRealTime);
        vegetarianFilter.addEventListener('change', applyFiltersRealTime);
        wheelchairFilter.addEventListener('change', applyFiltersRealTime);
        openNowFilter.addEventListener('change', applyFiltersRealTime);
    } else {
        // Approche de soumission de formulaire pour le filtrage côté serveur
        // Activer l'autosubmit si désiré
        const autoSubmitOnChange = true;
        
        if (autoSubmitOnChange) {
            // Soumettre automatiquement le formulaire lors de changements
            typeSelect.addEventListener('change', function() {
                filterForm.submit();
            });
            
            vegetarianFilter.addEventListener('change', function() {
                filterForm.submit();
            });
            
            wheelchairFilter.addEventListener('change', function() {
                filterForm.submit();
            });
            
            openNowFilter.addEventListener('change', function() {
                filterForm.submit();
            });
        }
    }
});