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
  const form = document.getElementById('form-mde');

  /**
   * Modal for notifications
   * @type {bootstrap.Modal}
   */
  const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));

  /**
   * Submit buttons for different actions
   * @type {Object}
   */
  const buttons = {
    save: document.getElementById('button-form-save'),
    submit: document.getElementById('button-form-submit')
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

    if (!form.checkValidity()) {
      handleInvalidForm();
    } else {
      handleValidForm(action);
    }
  });

  /**
   * Handles form validation errors
   */
  function handleInvalidForm() {
    form.classList.add('was-validated');
    const firstInvalid = form.querySelector(':invalid');

    if (firstInvalid) {
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstInvalid.focus();
      showNotification('danger', 'Validation Error', 'Please check your inputs! Some required fields are not filled correctly.');
    }
  }

  /**
   * Handles successful form validation
   * @param {string} action - Type of submission ('save' or 'submit')
   */
  function handleValidForm(action) {
    if (action === 'save') {
      showNotification('info', 'Processing...', 'Dataset is being saved.');
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
      showNotification('success', 'Success!', 'Dataset saved successfully. The XML file download will start automatically.');
    } else if (action === 'submit') {
      showNotification('info', 'Processing...', 'Dataset is being submitted.');
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
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          showNotification('success', 'Success!', response.message);
        } else {
          showNotification('danger', 'Error!', response.message);
          console.error('Error details:', response.debug);
        }
      },
      error: function (xhr, status, error) {
        let errorMessage = 'Failed to submit dataset';
        try {
          const response = JSON.parse(xhr.responseText);
          errorMessage = response.message || errorMessage;
          console.error('Error details:', response.debug);
        } catch (e) {
          errorMessage += ': ' + error;
          console.error('Response:', xhr.responseText);
        }
        showNotification('danger', 'Error!', errorMessage);
      }
    });
  }

  /**
   * Shows a notification in the modal
   * @param {string} type - Notification type (success, danger, info)
   * @param {string} title - Modal title
   * @param {string} message - Notification message
   */
  function showNotification(type, title, message) {
    const modalTitle = document.getElementById('notificationModalLabel');
    const modalBody = document.getElementById('notificationModalBody');

    // Set modal content
    modalTitle.textContent = title;
    modalBody.innerHTML = `
      <div class="alert alert-${type} mb-0">
        ${message}
      </div>
    `;

    // Show modal
    notificationModal.show();

    // Auto-hide for success messages after 3 seconds
    if (type === 'success') {
      setTimeout(() => {
        notificationModal.hide();
      }, 3000);
    }
  }
});