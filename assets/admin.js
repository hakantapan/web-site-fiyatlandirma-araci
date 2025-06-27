;(($) => {
  // Declare variables before using them
  const ajaxurl = window.ajaxurl
  const morpheo_admin = window.morpheo_admin

  $(document).ready(() => {
    initializeAdmin()

    // Auto-refresh payment status every 30 seconds on payments page
    if (window.location.href.indexOf("morpheo-calculator-payments") > -1) {
      setInterval(() => {
        location.reload()
      }, 30000)
    }

    // Confirm before manual payment check
    $('form[method="post"]').on("submit", function (e) {
      if ($(this).find('input[name="check_payments"]').length > 0) {
        if (!confirm("Tüm bekleyen ödemeler kontrol edilecek. Devam etmek istiyor musunuz?")) {
          e.preventDefault()
        }
      }
    })

    // Copy payment URL to clipboard
    $(".payment-url").on("click", function () {
      const text = $(this).text()
      navigator.clipboard.writeText(text).then(() => {
        alert("Ödeme URL'si panoya kopyalandı!")
      })
    })

    // Highlight expired payments
    $(".expired-payment").each(function () {
      $(this).find("td").css("background-color", "#fef2f2")
    })

    // Auto-update stats every 5 minutes
    if ($(".stats-grid").length > 0) {
      setInterval(() => {
        $.get(window.location.href, (data) => {
          const newStats = $(data).find(".stats-grid")
          if (newStats.length > 0) {
            $(".stats-grid").html(newStats.html())
          }
        })
      }, 300000) // 5 minutes
    }
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

    // API Check buttons
    $(".api-check-btn").on("click", function (e) {
      e.preventDefault()
      const appointmentId = $(this).data("id")
      const email = $(this).data("email")
      checkSinglePayment(appointmentId, email, $(this))
    })

    // API Response modal
    $(document).on("click", ".view-api-response", function (e) {
      e.preventDefault()
      const email = $(this).data("email")
      showApiResponse(email)
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

  function checkSinglePayment(appointmentId, email, button) {
    const originalText = button.text()
    button.prop("disabled", true).text("🔄 Kontrol ediliyor...")

    $.post(
      ajaxurl,
      {
        action: "check_single_payment",
        appointment_id: appointmentId,
        email: email,
        nonce: morpheo_admin.nonce,
      },
      (response) => {
        if (response.success) {
          if (response.data.payment_info && response.data.payment_info.paid) {
            // Payment confirmed - reload page to show updated status
            showNotification("✅ " + response.data.message, "success")
            setTimeout(() => {
              location.reload()
            }, 2000)
          } else {
            // No payment found
            showNotification("⚠️ " + response.data.message, "warning")
            button.prop("disabled", false).text(originalText)

            // Add button to view raw API response
            if (!button.next(".view-api-response").length) {
              button.after(
                `<button class="button button-small view-api-response" data-email="${email}" style="margin-left: 5px;">📄 API Yanıt</button>`,
              )
            }
          }
        } else {
          showNotification("❌ Hata: " + response.data.message, "error")
          button.prop("disabled", false).text(originalText)
        }
      },
    ).fail(() => {
      showNotification("❌ AJAX hatası oluştu", "error")
      button.prop("disabled", false).text(originalText)
    })
  }

  function showApiResponse(email) {
    $.post(
      ajaxurl,
      {
        action: "get_api_response",
        email: email,
        nonce: morpheo_admin.nonce,
      },
      (response) => {
        if (response.success) {
          const data = response.data
          const modalContent = `
            <div class="api-response-modal">
              <h3>🔍 API Yanıt Detayları</h3>
              <div class="api-info">
                <p><strong>E-posta:</strong> ${email}</p>
                <p><strong>HTTP Durum:</strong> ${data.status_code}</p>
                <p><strong>API URL:</strong> <a href="${data.url}" target="_blank">${data.url}</a></p>
              </div>
              
              <h4>📄 Ham API Yanıtı:</h4>
              <textarea readonly style="width: 100%; height: 200px; font-family: monospace; font-size: 12px;">${data.response}</textarea>
              
              <h4>🔧 İşlenmiş Sonuç:</h4>
              <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;">${JSON.stringify(data.parsed, null, 2)}</pre>
              
              <div style="margin-top: 20px; text-align: center;">
                <button class="button button-primary modal-close">Kapat</button>
              </div>
            </div>
          `
          showModal(modalContent)
        } else {
          alert("API yanıtı alınamadı: " + response.data.message)
        }
      },
    )
  }

  function showNotification(message, type = "info") {
    const notificationClass =
      {
        success: "notice-success",
        warning: "notice-warning",
        error: "notice-error",
        info: "notice-info",
      }[type] || "notice-info"

    const notification = $(`
      <div class="notice ${notificationClass} is-dismissible" style="margin: 10px 0;">
        <p>${message}</p>
        <button type="button" class="notice-dismiss">
          <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
      </div>
    `)

    $(".wrap h1").after(notification)

    // Auto dismiss after 5 seconds
    setTimeout(() => {
      notification.fadeOut(() => notification.remove())
    }, 5000)

    // Manual dismiss
    notification.find(".notice-dismiss").on("click", () => {
      notification.fadeOut(() => notification.remove())
    })
  }

  function showModal(content) {
    const modal = $(`
            <div class="modal-overlay">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h3 class="modal-title">Detaylar</h3>
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
