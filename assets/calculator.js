;(($) => {
  let currentStep = 1
  const totalSteps = 6
  let calculatorData = {}
  let selectedDate = null
  let selectedTime = null
  const morpheo_ajax = window.morpheo_ajax // Declare morpheo_ajax variable

  $(document).ready(() => {
    initializeCalculator()
    setupEventListeners()
    updateProgress()
  })

  function initializeCalculator() {
    // Initialize theme
    const savedTheme = localStorage.getItem("morpheo-calculator-theme") || "dark"
    $(".morpheo-calculator").addClass(savedTheme + "-theme")
    updateThemeIcon(savedTheme)

    // Initialize first step
    showStep(1)

    // Initialize range sliders
    $(".range-slider").each(function () {
      updateRangeValue(this)
    })
  }

  function setupEventListeners() {
    // Theme toggle
    $(".theme-toggle").on("click", toggleTheme)

    // Navigation buttons
    $(".btn-next").on("click", nextStep)
    $(".btn-prev").on("click", prevStep)
    $(".btn-calculate").on("click", calculatePrice)
    $(".btn-book-appointment").on("click", showAppointmentModal)

    // Form inputs
    $(".option-card").on("click", selectOption)
    $(".feature-item").on("click", toggleFeature)
    $(".range-slider").on("input", function () {
      updateRangeValue(this)
    })

    // Modal events
    $(".modal-close, .appointment-modal").on("click", function (e) {
      if (e.target === this) {
        closeAppointmentModal()
      }
    })

    // Calendar and time selection
    $(document).on("click", ".calendar-day:not(.disabled)", selectDate)
    $(document).on("click", ".time-slot:not(.disabled)", selectTime)
    $(document).on("click", ".btn-confirm-appointment", confirmAppointment)

    // Contact form
    $(".contact-form").on("submit", handleContactSubmit)
  }

  function showStep(step) {
    $(".calculator-step").removeClass("active")
    $('.calculator-step[data-step="' + step + '"]').addClass("active")
    currentStep = step
    updateProgress()

    // Scroll to top of calculator
    $(".morpheo-calculator")[0].scrollIntoView({ behavior: "smooth" })
  }

  function updateProgress() {
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100
    $(".progress-fill").css("width", progress + "%")
    $(".progress-text").text(`Adım ${currentStep} / ${totalSteps}`)
  }

  function nextStep() {
    if (validateCurrentStep()) {
      if (currentStep < totalSteps) {
        showStep(currentStep + 1)
      }
    }
  }

  function prevStep() {
    if (currentStep > 1) {
      showStep(currentStep - 1)
    }
  }

  function validateCurrentStep() {
    const currentStepElement = $('.calculator-step[data-step="' + currentStep + '"]')

    // Check required radio buttons
    const requiredRadios = currentStepElement.find('input[type="radio"][required]')
    if (requiredRadios.length > 0) {
      let hasSelection = false
      requiredRadios.each(function () {
        if ($(this).is(":checked")) {
          hasSelection = true
          return false
        }
      })
      if (!hasSelection) {
        alert("Lütfen bir seçenek seçin.")
        return false
      }
    }

    // Check required inputs
    const requiredInputs = currentStepElement.find("input[required], select[required]")
    for (let i = 0; i < requiredInputs.length; i++) {
      const input = requiredInputs[i]
      if (!input.value.trim()) {
        alert("Lütfen tüm gerekli alanları doldurun.")
        input.focus()
        return false
      }
    }

    return true
  }

  function selectOption() {
    const $card = $(this)
    const $radio = $card.find('input[type="radio"]')

    // Remove selection from siblings
    $card.siblings().removeClass("selected")

    // Add selection to current card
    $card.addClass("selected")
    $radio.prop("checked", true)
  }

  function toggleFeature() {
    const $item = $(this)
    const $checkbox = $item.find('input[type="checkbox"]')

    $item.toggleClass("selected")
    $checkbox.prop("checked", !$checkbox.prop("checked"))
  }

  function updateRangeValue(slider) {
    const $slider = $(slider)
    const value = $slider.val()
    const $valueDisplay = $slider.siblings(".range-value")

    if ($slider.attr("id") === "page-count") {
      $valueDisplay.text(value + " sayfa")
    } else {
      $valueDisplay.text(value)
    }
  }

  function calculatePrice() {
    // Show loading animation
    showLoadingAnimation()

    // Collect form data
    collectFormData()

    // Simulate calculation process with realistic timing
    const steps = [
      { text: "📊 Proje türü analiz ediliyor...", delay: 800 },
      { text: "📄 Sayfa sayısı hesaplanıyor...", delay: 600 },
      { text: "🎨 Tasarım karmaşıklığı değerlendiriliyor...", delay: 900 },
      { text: "⚙️ Özellikler analiz ediliyor...", delay: 700 },
      { text: "💰 Fiyat aralığı belirleniyor...", delay: 1000 },
    ]

    let currentStepIndex = 0
    let totalProgress = 0

    function processNextStep() {
      if (currentStepIndex < steps.length) {
        const step = steps[currentStepIndex]

        // Update current step
        $(".loading-step").removeClass("active completed")
        $(".loading-step").eq(currentStepIndex).addClass("active")

        // Update progress
        totalProgress = ((currentStepIndex + 1) / steps.length) * 100
        $(".loading-progress-bar").css("width", totalProgress + "%")

        setTimeout(() => {
          // Mark current step as completed
          $(".loading-step").eq(currentStepIndex).removeClass("active").addClass("completed")
          $(".loading-step").eq(currentStepIndex).find(".loading-step-icon").text("✅")

          currentStepIndex++
          processNextStep()
        }, step.delay)
      } else {
        // All steps completed, calculate and show results
        setTimeout(() => {
          const pricing = calculatePricing()
          hideLoadingAnimation()
          showResults(pricing)
        }, 500)
      }
    }

    processNextStep()
  }

  function showLoadingAnimation() {
    const loadingHTML = `
            <div class="loading-overlay">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <div class="loading-title">💡 Teklifiniz Hazırlanıyor</div>
                    <ul class="loading-steps">
                        <li class="loading-step">
                            <span class="loading-step-icon">📊</span>
                            <span>Proje türü analiz ediliyor...</span>
                        </li>
                        <li class="loading-step">
                            <span class="loading-step-icon">📄</span>
                            <span>Sayfa sayısı hesaplanıyor...</span>
                        </li>
                        <li class="loading-step">
                            <span class="loading-step-icon">🎨</span>
                            <span>Tasarım karmaşıklığı değerlendiriliyor...</span>
                        </li>
                        <li class="loading-step">
                            <span class="loading-step-icon">⚙️</span>
                            <span>Özellikler analiz ediliyor...</span>
                        </li>
                        <li class="loading-step">
                            <span class="loading-step-icon">💰</span>
                            <span>Fiyat aralığı belirleniyor...</span>
                        </li>
                    </ul>
                    <div class="loading-progress">
                        <div class="loading-progress-bar"></div>
                    </div>
                </div>
            </div>
        `

    $("body").append(loadingHTML)
    setTimeout(() => {
      $(".loading-overlay").addClass("active")
    }, 100)
  }

  function hideLoadingAnimation() {
    $(".loading-overlay").removeClass("active")
    setTimeout(() => {
      $(".loading-overlay").remove()
    }, 300)
  }

  function collectFormData() {
    calculatorData = {
      website_type: $('input[name="website_type"]:checked').val(),
      page_count: $("#page-count").val(),
      features: getSelectedFeatures(),
      design_complexity: $('input[name="design_complexity"]:checked').val(),
      timeline: $('input[name="timeline"]:checked').val(),
      technical_seo: $('input[name="technical_seo"]:checked').val(),
      management_features: $('input[name="management_features"]:checked').val(),
      security_features: $('input[name="security_features"]:checked').val(),
      ecommerce_modules: $('input[name="ecommerce_modules"]:checked').val(),
      first_name: $("#first_name").val(),
      last_name: $("#last_name").val(),
      email: $("#email").val(),
      phone: $("#phone").val(),
      company: $("#company").val(),
      city: $("#city").val(),
    }
  }

  function getSelectedFeatures() {
    const features = []
    $('.feature-item input[type="checkbox"]:checked').each(function () {
      features.push($(this).val())
    })
    return JSON.stringify(features)
  }

  function calculatePricing() {
    let basePrice = 0
    let multiplier = 1

    // Base price by website type
    const websiteTypePrices = {
      corporate: 8000,
      ecommerce: 15000,
      blog: 5000,
      landing: 3000,
    }

    basePrice = websiteTypePrices[calculatorData.website_type] || 8000

    // Page count multiplier
    const pageCount = Number.parseInt(calculatorData.page_count)
    if (pageCount <= 5) {
      multiplier *= 1
    } else if (pageCount <= 10) {
      multiplier *= 1.3
    } else if (pageCount <= 20) {
      multiplier *= 1.6
    } else {
      multiplier *= 2
    }

    // Design complexity multiplier
    const designMultipliers = {
      basic: 1,
      custom: 1.5,
      premium: 2.2,
    }
    multiplier *= designMultipliers[calculatorData.design_complexity] || 1

    // Features additional cost
    const features = JSON.parse(calculatorData.features)
    let additionalCost = 0
    const featureCosts = {
      seo: 2000,
      cms: 1500,
      multilang: 3000,
      payment: 2500,
      booking: 2000,
      analytics: 1000,
    }

    features.forEach((feature) => {
      additionalCost += featureCosts[feature] || 0
    })

    // Timeline multiplier
    const timelineMultipliers = {
      urgent: 1.5,
      normal: 1,
      flexible: 0.9,
    }
    multiplier *= timelineMultipliers[calculatorData.timeline] || 1

    // Calculate final prices
    const finalBasePrice = Math.round(basePrice * multiplier)
    const minPrice = finalBasePrice + additionalCost
    const maxPrice = Math.round(minPrice * 1.3)

    return {
      min_price: minPrice,
      max_price: maxPrice,
      base_price: finalBasePrice,
      additional_cost: additionalCost,
      features: features,
    }
  }

  function showResults(pricing) {
    calculatorData.min_price = pricing.min_price
    calculatorData.max_price = pricing.max_price

    // Update results display
    $(".price-range").text(
      new Intl.NumberFormat("tr-TR").format(pricing.min_price) +
        " - " +
        new Intl.NumberFormat("tr-TR").format(pricing.max_price) +
        " ₺",
    )

    // Show results step
    showStep(totalSteps)

    // Save data to database
    saveCalculatorData()
  }

  function saveCalculatorData() {
    $.ajax({
      url: morpheo_ajax.ajax_url,
      type: "POST",
      data: {
        action: "save_calculator_data",
        nonce: morpheo_ajax.nonce,
        ...calculatorData,
      },
      success: (response) => {
        if (response.success) {
          calculatorData.id = response.data.id
          console.log("Calculator data saved successfully")
        } else {
          console.error("Failed to save calculator data:", response.data.message)
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX error:", error)
      },
    })
  }

  function showAppointmentModal() {
    if (!calculatorData.id) {
      alert("Lütfen önce fiyat hesaplamasını tamamlayın.")
      return
    }

    const modalHTML = `
            <div class="appointment-modal">
                <div class="modal-content">
                    <button class="modal-close">&times;</button>
                    <h3>📅 Ücretsiz Konsültasyon Randevusu</h3>
                    <p>Projenizi detaylı olarak konuşmak için bir randevu alın.</p>
                    
                    <div class="appointment-form">
                        <div class="form-group">
                            <label>Randevu Tarihi Seçin:</label>
                            <div class="calendar-container">
                                <div class="calendar-header">
                                    <button type="button" class="btn-prev-month">&lt;</button>
                                    <span class="current-month"></span>
                                    <button type="button" class="btn-next-month">&gt;</button>
                                </div>
                                <div class="calendar-grid"></div>
                            </div>
                        </div>
                        
                        <div class="form-group time-selection" style="display: none;">
                            <label>Saat Seçin:</label>
                            <div class="time-slots"></div>
                        </div>
                        
                        <div class="appointment-summary" style="display: none;">
                            <h4>Randevu Özeti:</h4>
                            <p><strong>Tarih:</strong> <span class="selected-date"></span></p>
                            <p><strong>Saat:</strong> <span class="selected-time"></span></p>
                            <p><strong>Konsültasyon Ücreti:</strong> <span class="consultation-fee">${morpheo_ajax.consultation_fee} ₺</span></p>
                            <p class="fee-note">* Konsültasyon ücreti, proje onaylandığında toplam fiyattan düşülecektir.</p>
                        </div>
                        
                        <div class="modal-buttons">
                            <button type="button" class="btn btn-primary btn-confirm-appointment" style="display: none;">
                                💳 Ödeme Yap ve Randevu Al
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `

    $("body").append(modalHTML)
    $(".appointment-modal").addClass("active")

    initializeCalendar()
  }

  function closeAppointmentModal() {
    $(".appointment-modal").removeClass("active")
    setTimeout(() => {
      $(".appointment-modal").remove()
    }, 300)
    selectedDate = null
    selectedTime = null
  }

  function initializeCalendar() {
    const now = new Date()
    const currentMonth = now.getMonth()
    const currentYear = now.getFullYear()

    generateCalendar(currentYear, currentMonth)

    $(".btn-prev-month").on("click", () => {
      const prevMonth = currentMonth === 0 ? 11 : currentMonth - 1
      const prevYear = currentMonth === 0 ? currentYear - 1 : currentYear
      generateCalendar(prevYear, prevMonth)
    })

    $(".btn-next-month").on("click", () => {
      const nextMonth = currentMonth === 11 ? 0 : currentMonth + 1
      const nextYear = currentMonth === 11 ? currentYear + 1 : currentYear
      generateCalendar(nextYear, nextMonth)
    })
  }

  function generateCalendar(year, month) {
    const monthNames = [
      "Ocak",
      "Şubat",
      "Mart",
      "Nisan",
      "Mayıs",
      "Haziran",
      "Temmuz",
      "Ağustos",
      "Eylül",
      "Ekim",
      "Kasım",
      "Aralık",
    ]

    $(".current-month").text(monthNames[month] + " " + year)

    const firstDay = new Date(year, month, 1).getDay()
    const daysInMonth = new Date(year, month + 1, 0).getDate()
    const today = new Date()

    let calendarHTML = ""

    // Add day headers
    const dayHeaders = ["Pz", "Pt", "Sa", "Ça", "Pe", "Cu", "Ct"]
    dayHeaders.forEach((day) => {
      calendarHTML += `<div class="calendar-day-header">${day}</div>`
    })

    // Add empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
      calendarHTML += '<div class="calendar-day disabled"></div>'
    }

    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
      const date = new Date(year, month, day)
      const dateString = date.toISOString().split("T")[0]
      const isDisabled = date < today || date.getDay() === 0 // Disable past dates and Sundays
      const classes = ["calendar-day"]

      if (isDisabled) {
        classes.push("disabled")
      }

      calendarHTML += `<div class="${classes.join(" ")}" data-date="${dateString}">${day}</div>`
    }

    $(".calendar-grid").html(calendarHTML)
  }

  function selectDate() {
    const $day = $(this)
    const date = $day.data("date")

    // Remove previous selection
    $(".calendar-day").removeClass("selected")
    $day.addClass("selected")

    selectedDate = date
    $(".selected-date").text(formatDate(date))

    // Load available time slots
    loadTimeSlots(date)

    // Show time selection
    $(".time-selection").show()
  }

  function loadTimeSlots(date) {
    $.ajax({
      url: morpheo_ajax.ajax_url,
      type: "POST",
      data: {
        action: "get_available_time_slots",
        nonce: morpheo_ajax.nonce,
        date: date,
      },
      success: (response) => {
        if (response.success) {
          generateTimeSlots(response.data.booked_slots)
        } else {
          console.error("Failed to load time slots:", response.data.message)
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX error:", error)
      },
    })
  }

  function generateTimeSlots(bookedSlots) {
    const timeSlots = [
      "09:00",
      "09:30",
      "10:00",
      "10:30",
      "11:00",
      "11:30",
      "13:00",
      "13:30",
      "14:00",
      "14:30",
      "15:00",
      "15:30",
      "16:00",
      "16:30",
      "17:00",
      "17:30",
    ]

    let slotsHTML = ""

    timeSlots.forEach((time) => {
      const isBooked = bookedSlots.includes(time)
      const classes = ["time-slot"]

      if (isBooked) {
        classes.push("disabled")
      }

      slotsHTML += `<div class="${classes.join(" ")}" data-time="${time}">${time}</div>`
    })

    $(".time-slots").html(slotsHTML)
  }

  function selectTime() {
    const $slot = $(this)
    const time = $slot.data("time")

    // Remove previous selection
    $(".time-slot").removeClass("selected")
    $slot.addClass("selected")

    selectedTime = time
    $(".selected-time").text(time)

    // Show appointment summary and confirm button
    $(".appointment-summary").show()
    $(".btn-confirm-appointment").show()
  }

  function confirmAppointment() {
    if (!selectedDate || !selectedTime) {
      alert("Lütfen tarih ve saat seçin.")
      return
    }

    const $button = $(".btn-confirm-appointment")
    $button.prop("disabled", true).text("⏳ Randevu oluşturuluyor...")

    $.ajax({
      url: morpheo_ajax.ajax_url,
      type: "POST",
      data: {
        action: "book_appointment",
        nonce: morpheo_ajax.nonce,
        calculator_id: calculatorData.id,
        appointment_date: selectedDate,
        appointment_time: selectedTime,
      },
      success: (response) => {
        if (response.success) {
          // Close modal
          closeAppointmentModal()

          // Show success message and redirect to payment
          alert("✅ Randevunuz başarıyla oluşturuldu! Ödeme sayfasına yönlendiriliyorsunuz...")

          // Redirect to payment URL
          if (response.data.payment_url) {
            window.open(response.data.payment_url, "_blank")
          }

          // Send WhatsApp notification
          sendWhatsAppNotification(response.data.appointment_id)
        } else {
          alert("❌ Randevu oluşturulurken hata oluştu: " + response.data.message)
          $button.prop("disabled", false).text("💳 Ödeme Yap ve Randevu Al")
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX error:", error)
        alert("❌ Bir hata oluştu. Lütfen tekrar deneyin.")
        $button.prop("disabled", false).text("💳 Ödeme Yap ve Randevu Al")
      },
    })
  }

  function sendWhatsAppNotification(appointmentId) {
    // Send WhatsApp notification to admin
    $.ajax({
      url: morpheo_ajax.ajax_url,
      type: "POST",
      data: {
        action: "send_whatsapp_notification",
        nonce: morpheo_ajax.nonce,
        appointment_id: appointmentId,
        type: "new_appointment",
      },
      success: (response) => {
        console.log("WhatsApp notification sent:", response)
      },
      error: (xhr, status, error) => {
        console.error("WhatsApp notification error:", error)
      },
    })
  }

  function handleContactSubmit(e) {
    e.preventDefault()

    const formData = new FormData(this)
    const $submitButton = $(this).find('button[type="submit"]')

    $submitButton.prop("disabled", true).text("Gönderiliyor...")

    // Here you would typically send the form data via AJAX
    // For now, we'll just show a success message
    setTimeout(() => {
      alert("✅ Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.")
      this.reset()
      $submitButton.prop("disabled", false).text("Mesaj Gönder")
    }, 1000)
  }

  function toggleTheme() {
    const $calculator = $(".morpheo-calculator")
    const isDark = $calculator.hasClass("dark-theme")

    if (isDark) {
      $calculator.removeClass("dark-theme").addClass("light-theme")
      localStorage.setItem("morpheo-calculator-theme", "light")
      updateThemeIcon("light")
    } else {
      $calculator.removeClass("light-theme").addClass("dark-theme")
      localStorage.setItem("morpheo-calculator-theme", "dark")
      updateThemeIcon("dark")
    }
  }

  function updateThemeIcon(theme) {
    const icon = theme === "dark" ? "☀️" : "🌙"
    $(".theme-toggle").text(icon)
  }

  function formatDate(dateString) {
    const date = new Date(dateString)
    const options = {
      year: "numeric",
      month: "long",
      day: "numeric",
      weekday: "long",
    }
    return date.toLocaleDateString("tr-TR", options)
  }

  // Utility functions
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

  function throttle(func, limit) {
    let inThrottle
    return function () {
      const args = arguments
      
      if (!inThrottle) {
        func.apply(this, args)
        inThrottle = true
        setTimeout(() => (inThrottle = false), limit)
      }
    }
  }
})(window.jQuery) // Use window.jQuery to ensure jQuery is declared
