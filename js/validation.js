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
   * Button elements for different submission types
   * @type {Object}
   */
  const buttons = {
    save: document.getElementById('saveAs'),
    submit: document.getElementById('submitButton')
  };

  // Enable buttons
  buttons.save.disabled = false;
  buttons.submit.disabled = false;

  // Add event listeners
  buttons.save.addEventListener('click', e => handleFormSubmission(e, 'save'));
  buttons.submit.addEventListener('click', e => handleFormSubmission(e, 'submit'));

  /**
   * Main form submission handler
   * @param {Event} e - Click event object
   * @param {string} action - Type of submission ('save' or 'submit')
   */
  function handleFormSubmission(e, action) {
    e.preventDefault();
    clearAlerts();
    resetValidation();

    if (!form.checkValidity()) {
      handleInvalidForm();
    } else {
      handleValidForm(action);
    }
  }

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
        form.submit();
      }, 1000);
    } else {
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