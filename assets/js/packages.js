//This class handles the live search on the packages page
//sends a request to the API as the user types and updates the package cards without reloading 
// the page


//wait until the page is fully loaded before running anything
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('search-input');
    const packagesGrid = document.getElementById('packages-grid');

    //get the base path from the hidden input we set in the template
    const basePath = document.getElementById('base-path').value;

    //debounce timer — waits 300ms after the user stops typing before sending the request
    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const query = this.value.trim();

            //send a fetch request to our API endpoint with the search query
            fetch(basePath + '/api/packages/search?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(packages => renderPackages(packages));
        }, 300);
    });

    //rebuild the package cards from the JSON data returned by the API
    function renderPackages(packages) {
        if (packages.length === 0) {
            packagesGrid.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5 text-muted">
                        <p class="fs-5 mb-2">No packages found.</p>
                        <a href="${basePath}/packages" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                    </div>
                </div>`;
            return;
        }

        //loop through each package and build its card HTML
        packagesGrid.innerHTML = packages.map(p => `
            <div class="col-md-6 col-xl-4">
                <div class="card package-card h-100">
                    <img src="${p.image_url || 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800'}"
                         class="card-img-top package-card-img" alt="${p.title}" loading="lazy">
                    <div class="card-body d-flex flex-column">
                        <p class="package-destination">${p.city}</p>
                        <h5 class="card-title package-title">${p.title}</h5>
                        <p class="card-text text-muted small flex-grow-1">${p.description}</p>
                        <div class="d-flex gap-3 my-2">
                            <span class="small text-muted">${p.duration_days} days</span>
                            <span class="small text-muted">${p.available_slots} slots left</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div>
                                <div class="price-label">From</div>
                                <div class="price-value">$${p.price}<span class="price-per">/person</span></div>
                            </div>
                            <a href="${basePath}/packages/${p.id}" class="btn btn-teal btn-sm">View Deal</a>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
});