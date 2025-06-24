;(($) => {
  // Declare variables before using them
  const ajaxurl = window.ajaxurl
  const morpheo_admin = window.morpheo_admin

  $(document).ready(() => {
    initializeAdmin()
  })

  function initializeAdmin() {
    // Export functionality
    $("#export-csv").on("click", (e) => {
      e.preventDefault()
      exportData("csv")
    })

    $("#export-excel").on("click", (e) => {
      e.preventDefault()
      exportData("excel")
    })

    // View details modal
    $(".view-details").on("click", function (e) {
      e.preventDefault()
      const resultId = $(this).data("id")
      showResultDetails(resultId)
    })

    // Close modal
    $(document).on("click", ".modal-close, .modal-overlay", function (e) {
      if (e.target === this) {
        closeModal()
      }
    })

    // Appointment status updates
    $(".status-select").on("change", function () {
      const appointmentId = $(this).data("id")
      const newStatus = $(this).val()
      updateAppointmentStatus(appointmentId, newStatus)
    })

    // Bulk actions
    $("#bulk-action-apply").on("click", (e) => {
      e.preventDefault()
      const action = $("#bulk-action-selector").val()
      const selectedItems = $(".bulk-select:checked")
        .map(function () {
          return $(this).val()
        })
        .get()

      if (selectedItems.length === 0) {
        alert("Please select items to perform bulk action.")
        return
      }

      if (confirm(`Are you sure you want to ${action} ${selectedItems.length} items?`)) {
        performBulkAction(action, selectedItems)
      }
    })

    // Search functionality
    $("#search-results").on(
      "input",
      debounce(function () {
        const searchTerm = $(this).val()
        searchResults(searchTerm)
      }, 300),
    )
  }

  function exportData(format) {
    const url = ajaxurl + "?action=export_morpheo_data&format=" + format + "&nonce=" + morpheo_admin.nonce
    window.open(url, "_blank")
  }

  function showResultDetails(resultId) {
    $.post(
      ajaxurl,
      {
        action: "get_morpheo_result_details",
        result_id: resultId,
        nonce: morpheo_admin.nonce,
      },
      (response) => {
        if (response.success) {
          showModal(response.data.html)
        } else {
          alert("Error loading details: " + response.data.message)
        }
      },
    )
  }

  function showModal(content) {
    const modal = $(`
            <div class="modal-overlay">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h3 class="modal-title">Calculator Result Details</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                </div>
            </div>
        `)

    $("body").append(modal)
    modal.fadeIn(200)
  }

  function closeModal() {
    $(".modal-overlay").fadeOut(200, function () {
      $(this).remove()
    })
  }

  function updateAppointmentStatus(appointmentId, newStatus) {
    $.post(
      ajaxurl,
      {
        action: "update_morpheo_appointment_status",
        appointment_id: appointmentId,
        status: newStatus,
        nonce: morpheo_admin.nonce,
      },
      (response) => {
        if (response.success) {
          location.reload()
        } else {
          alert("Error updating status: " + response.data.message)
        }
      },
    )
  }

  function performBulkAction(action, items) {
    $.post(
      ajaxurl,
      {
        action: "bulk_action_morpheo",
        bulk_action: action,
        items: items,
        nonce: morpheo_admin.nonce,
      },
      (response) => {
        if (response.success) {
          location.reload()
        } else {
          alert("Error performing bulk action: " + response.data.message)
        }
      },
    )
  }

  function searchResults(searchTerm) {
    $.post(
      ajaxurl,
      {
        action: "search_morpheo_results",
        search: searchTerm,
        nonce: morpheo_admin.nonce,
      },
      (response) => {
        if (response.success) {
          $("#results-table-body").html(response.data.html)
        }
      },
    )
  }

  function debounce(func, wait) {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  }
})(window.jQuery)
