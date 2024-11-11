/**
 * Form handling module for dataset submission
 * Handles both local saving and email submission of datasets
 * 
 */

document.addEventListener('DOMContentLoaded', function () {
  /**
   * Main form element containing the dataset metadata
   * @type {HTMLFormElement}
   */
  const form = document.getElementById('metaForm');

  /**
   * Submit buttons for different actions
   * @type {Object}
   */
  const buttons = {
    save: document.getElementById('saveAs'),
    submit: document.getElementById('submitButton')
  };

  // Enable buttons
  buttons.save.disabled = false;
  buttons.submit.disabled = false;

  /**
   * Form submit event handler
   * Determines action based on clicked button
   * @param {Event} e - Submit event object
   */
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Determine which button was clicked
    const clickedButton = document.activeElement;
    const action = clickedButton.dataset.action;

    clearAlerts();
    resetValidation();

    if (!form.checkValidity()) {
      handleInvalidForm();
    } else {
      handleValidForm(action);
    }
  });

  /**
   * Clears all existing alerts from the page
   */
  function clearAlerts() {
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
  }

  /**
   * Resets form validation state
   */
  function resetValidation() {
    form.classList.remove('was-validated');
  }

  /**
   * Handles form validation errors
   */
  function handleInvalidForm() {
    form.classList.add('was-validated');
    const firstInvalid = form.querySelector(':invalid');

    if (firstInvalid) {
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstInvalid.focus();
      showAlert('danger', 'Please check your inputs!', 'Some required fields are not filled correctly.');
    }
  }

  /**
   * Handles successful form validation
   * @param {string} action - Type of submission ('save' or 'submit')
   */
  function handleValidForm(action) {
    if (action === 'save') {
      showAlert('success', 'Success!', 'Dataset is being saved.');
      setTimeout(() => {
        const formData = new FormData(form);
        formData.append('action', 'save');

        // Create a hidden form and submit it
        const hiddenForm = document.createElement('form');
        hiddenForm.method = 'POST';
        hiddenForm.action = 'save/save_data.php';

        for (const [key, value] of formData.entries()) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = key;
          input.value = value;
          hiddenForm.appendChild(input);
        }

        document.body.appendChild(hiddenForm);
        hiddenForm.submit();
      }, 1000);
    } else if (action === 'submit') {
      showAlert('info', 'Processing...', 'Dataset is being submitted via email.');
      submitViaAjax();
    }
  }

  /**
   * Submits form data via AJAX for email submission
   */
  function submitViaAjax() {
    $.ajax({
      url: 'send_xml_file.php',
      type: 'POST',
      data: $(form).serialize(),
      success: function (response) {
        clearAlerts();
        showAlert('success', 'Success!', 'Dataset has been submitted via email.');
      },
      error: function (xhr, status, error) {
        clearAlerts();
        showAlert('danger', 'Error!', `Failed to submit dataset: ${error}`);
      }
    });
  }

  /**
   * Creates and shows an alert message
   * @param {string} type - Alert type (success, danger, info)
   * @param {string} title - Alert title
   * @param {string} message - Alert message
   */
  function showAlert(type, title, message) {
    const alertElement = document.createElement('div');
    alertElement.className = `alert alert-${type} alert-dismissible fade show`;
    alertElement.setAttribute('role', 'alert');
    alertElement.innerHTML = `
      <strong>${title}</strong> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    const mainContent = document.querySelector('main');
    mainContent.insertBefore(alertElement, mainContent.firstChild);

    // Auto-remove alert after 5 seconds
    setTimeout(() => alertElement.remove(), 5000);
  }
});