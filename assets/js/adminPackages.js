// Admin package form: filters hotel and guide dropdowns to only show options
// that belong to the selected destination. Disables both selects until a
// destination is chosen, and restores the correct filtered state on edit forms.



document.addEventListener('DOMContentLoaded', function () {
    // get elements
    const destinationSelect = document.getElementById('destination_id');
    const hotelSelect = document.getElementById('hotel_id');
    const guideSelect = document.getElementById('guide_id');

    // Guard clause: only run if the main selector exists
    if (!destinationSelect) return;

    function updateDropdowns(shouldReset = true) {
        const selectedDestination = destinationSelect.value;

        // Enable selects only if destination chosen
        const isValid = selectedDestination !== "";
        if (hotelSelect) hotelSelect.disabled = !isValid;
        if (guideSelect) guideSelect.disabled = !isValid;

        if (hotelSelect) {
            if (shouldReset) hotelSelect.value = "";
            hotelSelect.options[0].text = isValid ? "Select hotel" : "Select destination first";
            filterOptions(hotelSelect, selectedDestination);
        }

        if (guideSelect) {
            if (shouldReset) guideSelect.value = "";
            guideSelect.options[0].text = isValid ? "Select guide" : "Select destination first";
            filterOptions(guideSelect, selectedDestination);
        }
    }

    destinationSelect.addEventListener('change', () => updateDropdowns(true));

    /**
     * Filter options based on selected destination
     */
    function filterOptions(selectElement, destinationId) {
        Array.from(selectElement.options).forEach(option => {
            if (!option.value) return; // keep "Select ..." option
            // Use String comparison to ensure IDs match regardless of type
            const isMatch = String(option.dataset.destination) === String(destinationId);
            option.hidden = !isMatch;
            option.style.display = isMatch ? '' : 'none';
        });
    }

    // Trigger change event on load if a destination is already selected (e.g., on edit forms)
    if (destinationSelect.value) {
        updateDropdowns(false);
    }
});