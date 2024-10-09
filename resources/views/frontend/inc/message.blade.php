@if (Session::has('success'))
    <div class="container">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>{{ Session::get('success') }}</strong>
            <button type="button" class="close" style="font-size: 20px; text-decoration: none; background: transparent; border: none;" aria-label="Close">
                &times;
            </button>
        </div>
    </div>
@endif
@if ($errors->any())
    <div class="container">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex justify-content-between align-items-center w-100">
                <span>{!! implode('', $errors->all('<div>:message</div>')) !!}</span>
                <button type="button" class="close" style="font-size: 20px; text-decoration: none; background: transparent; border: none;" aria-label="Close">
                    &times;
                </button>
            </div>
        </div>
    </div>
@endif

<style>
    /* CSS for smoother fade-out */
    .fade {
        transition: opacity 0.5s ease-out; /* Transition for opacity */
    }

    .fade.out {
        opacity: 0; /* Fade out */
    }
</style>

<script>
    // Function to dismiss alerts smoothly
    function dismissAlert(alertElement) {
        alertElement.classList.add('out'); // Add class to trigger fade-out

        // Wait for the transition to complete before hiding the element
        setTimeout(() => {
            alertElement.style.display = 'none'; // Hide the alert from layout
        }, 500); // Match this time with the duration of the fade-out
    }

    // Automatically dismiss alerts after 3 seconds
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => dismissAlert(alert), 3000); // Dismiss after 3 seconds
    });

    // Add event listener for close buttons
    document.querySelectorAll('.close').forEach(closeButton => {
        closeButton.addEventListener('click', (event) => {
            const alert = event.target.closest('.alert-dismissible');
            if (alert) {
                dismissAlert(alert); // Dismiss the alert when the close button is clicked
            }
        });
    });
</script>
