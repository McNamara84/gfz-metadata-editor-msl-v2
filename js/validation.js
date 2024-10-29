document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('metaForm');
  const saveButton = document.getElementById('saveAs');

  // Remove disabled attribute from save button
  saveButton.disabled = false;

  // Add click event listener to save button
  saveButton.addEventListener('click', function (e) {
    e.preventDefault(); // Prevent immediate form submission

    // Remove any existing alert messages
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // Remove existing validation classes
    form.classList.remove('was-validated');

    // Trigger HTML5 form validation
    if (!form.checkValidity()) {
      // Add Bootstrap's was-validated class to show validation feedback
      form.classList.add('was-validated');

      // Find first invalid input and scroll to it
      const firstInvalid = form.querySelector(':invalid');
      if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus();

        // Create and show error message
        const alertMessage = document.createElement('div');
        alertMessage.className = 'alert alert-danger alert-dismissible fade show';
        alertMessage.setAttribute('role', 'alert');
        alertMessage.innerHTML = `
          <strong>Please check your inputs!</strong> Some required fields are not filled correctly.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insert alert at the top of the main content
        const mainContent = document.querySelector('main');
        mainContent.insertBefore(alertMessage, mainContent.firstChild);

        // Remove alert after 5 seconds
        setTimeout(() => alertMessage.remove(), 5000);
      }
    } else {
      // If form is valid, show success message and submit
      const successAlert = document.createElement('div');
      successAlert.className = 'alert alert-success alert-dismissible fade show';
      successAlert.setAttribute('role', 'alert');
      successAlert.innerHTML = `
        <strong>Success!</strong> Dataset is being submitted.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;

      const mainContent = document.querySelector('main');
      mainContent.insertBefore(successAlert, mainContent.firstChild);

      // Submit the form after a brief delay to show the success message
      setTimeout(() => {
        form.submit();
      }, 1000);
    }
  });
});