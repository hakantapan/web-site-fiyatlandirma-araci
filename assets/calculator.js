jQuery(document).ready(($) => {
  let currentStep = 1
  const totalSteps = 6
  const calculatorData = {
    websiteType: "",
    pages: 5,
    features: [],
    designComplexity: "",
    timeline: "",
    userData: {},
  }

  // Declare morpheo_ajax variable
  const morpheo_ajax = {
    nonce: "your_nonce_here",
    ajax_url: "your_ajax_url_here",
  }

  // Initialize calculator
  updateProgressBar()
  updateNavigation()

  // Website type selection
  $('input[name="website_type"]').change(function () {
    calculatorData.websiteType = $(this).val()
    updatePricing()
  })

  // Pages input
  $("#pages").on("input", function () {
    let pages = Number.parseInt($(this).val()) || 1
    pages = Math.max(1, Math.min(100, pages))
    $(this).val(pages)
    calculatorData.pages = pages
    updatePricing()
  })

  // Features selection
  $('input[name="features[]"]').change(() => {
    calculatorData.features = []
    $('input[name="features[]"]:checked').each(function () {
      calculatorData.features.push($(this).val())
    })
    updatePricing()
  })

  // Design complexity
  $('input[name="design_complexity"]').change(function () {
    calculatorData.designComplexity = $(this).val()
    updatePricing()
  })

  // Timeline
  $('input[name="timeline"]').change(function () {
    calculatorData.timeline = $(this).val()
    updatePricing()
  })

  // User data inputs
  $("#first_name, #last_name, #email, #phone").on("input", function () {
    const field = $(this).attr("id")
    calculatorData.userData[field] = $(this).val()
  })

  // Navigation
  $(".btn-next").click(() => {
    if (validateCurrentStep()) {
      if (currentStep < totalSteps) {
        currentStep++
        showStep(currentStep)
      }
    }
  })

  $(".btn-prev").click(() => {
    if (currentStep > 1) {
      currentStep--
      showStep(currentStep)
    }
  })

  // Form submission
  $("#book-appointment").click(() => {
    if (validateCurrentStep()) {
      const appointmentDate = $("#appointment_date").val()
      const appointmentTime = $("#appointment_time").val()

      if (!appointmentDate || !appointmentTime) {
        alert("Lütfen randevu tarihi ve saatini seçin.")
        return
      }

      bookAppointment(appointmentDate, appointmentTime)
    }
  })

  function showStep(step) {
    $(".calculator-step").removeClass("active")
    $("#step-" + step).addClass("active")
    updateProgressBar()
    updateNavigation()

    if (step === 6) {
      updateSummary()
    }
  }

  function updateProgressBar() {
    const progress = (currentStep / totalSteps) * 100
    $(".progress-fill").css("width", progress + "%")
  }

  function updateNavigation() {
    $(".btn-prev").toggle(currentStep > 1)
    $(".btn-next").toggle(currentStep < totalSteps)
    $("#book-appointment").toggle(currentStep === totalSteps)
  }

  function validateCurrentStep() {
    switch (currentStep) {
      case 1:
        if (!calculatorData.websiteType) {
          alert("Lütfen web sitesi türünü seçin.")
          return false
        }
        break
      case 2:
        if (calculatorData.pages < 1) {
          alert("Lütfen sayfa sayısını girin.")
          return false
        }
        break
      case 3:
        // Features are optional
        break
      case 4:
        if (!calculatorData.designComplexity) {
          alert("Lütfen tasarım karmaşıklığını seçin.")
          return false
        }
        break
      case 5:
        if (!calculatorData.timeline) {
          alert("Lütfen zaman çizelgesini seçin.")
          return false
        }
        break
      case 6:
        if (
          !calculatorData.userData.firstName ||
          !calculatorData.userData.lastName ||
          !calculatorData.userData.email ||
          !calculatorData.userData.phone
        ) {
          alert("Lütfen tüm kişisel bilgileri doldurun.")
          return false
        }
        if (!isValidEmail(calculatorData.userData.email)) {
          alert("Lütfen geçerli bir e-posta adresi girin.")
          return false
        }
        break
    }
    return true
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  function updatePricing() {
    let basePrice = 0
    let maxPrice = 0

    // Base price by website type
    switch (calculatorData.websiteType) {
      case "business":
        basePrice = 8000
        maxPrice = 15000
        break
      case "ecommerce":
        basePrice = 15000
        maxPrice = 30000
        break
      case "portfolio":
        basePrice = 5000
        maxPrice = 10000
        break
      case "blog":
        basePrice = 4000
        maxPrice = 8000
        break
      case "landing":
        basePrice = 2000
        maxPrice = 5000
        break
      case "custom":
        basePrice = 10000
        maxPrice = 50000
        break
    }

    // Adjust for pages
    if (calculatorData.pages > 5) {
      const extraPages = calculatorData.pages - 5
      basePrice += extraPages * 500
      maxPrice += extraPages * 1000
    }

    // Adjust for features
    const featureMultiplier = calculatorData.features.length * 0.1
    basePrice += basePrice * featureMultiplier
    maxPrice += maxPrice * featureMultiplier

    // Adjust for design complexity
    switch (calculatorData.designComplexity) {
      case "simple":
        // No change
        break
      case "moderate":
        basePrice *= 1.3
        maxPrice *= 1.3
        break
      case "complex":
        basePrice *= 1.6
        maxPrice *= 1.6
        break
    }

    // Adjust for timeline
    switch (calculatorData.timeline) {
      case "asap":
        basePrice *= 1.5
        maxPrice *= 1.5
        break
      case "1-2weeks":
        basePrice *= 1.2
        maxPrice *= 1.2
        break
      case "1month":
        // No change
        break
      case "2-3months":
        basePrice *= 0.9
        maxPrice *= 0.9
        break
      case "flexible":
        basePrice *= 0.8
        maxPrice *= 0.8
        break
    }

    // Round prices
    basePrice = Math.round(basePrice / 1000) * 1000
    maxPrice = Math.round(maxPrice / 1000) * 1000

    const priceRange = formatPrice(basePrice) + " - " + formatPrice(maxPrice)
    calculatorData.priceRange = priceRange

    $("#price-range").text(priceRange)
  }

  function formatPrice(price) {
    return new Intl.NumberFormat("tr-TR", {
      style: "currency",
      currency: "TRY",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(price)
  }

  function updateSummary() {
    const typeNames = {
      business: "Kurumsal Web Sitesi",
      ecommerce: "E-Ticaret Sitesi",
      portfolio: "Portföy/Kişisel Site",
      blog: "Blog/İçerik Sitesi",
      landing: "Landing Page",
      custom: "Özel Proje",
    }

    const complexityNames = {
      simple: "Basit Tasarım",
      moderate: "Orta Düzey Tasarım",
      complex: "Karmaşık Tasarım",
    }

    const timelineNames = {
      asap: "En Kısa Sürede",
      "1-2weeks": "1-2 Hafta",
      "1month": "1 Ay",
      "2-3months": "2-3 Ay",
      flexible: "Esnek",
    }

    $("#summary-type").text(typeNames[calculatorData.websiteType] || calculatorData.websiteType)
    $("#summary-pages").text(calculatorData.pages + " sayfa")
    $("#summary-features").text(calculatorData.features.length + " özellik")
    $("#summary-complexity").text(complexityNames[calculatorData.designComplexity] || calculatorData.designComplexity)
    $("#summary-timeline").text(timelineNames[calculatorData.timeline] || calculatorData.timeline)
    $("#summary-price").text(calculatorData.priceRange)
  }

  function bookAppointment(appointmentDate, appointmentTime) {
    $(".loading-spinner").show()
    $("#book-appointment").prop("disabled", true)

    const data = {
      action: "book_appointment",
      nonce: morpheo_ajax.nonce,
      calculatorData: JSON.stringify(calculatorData),
      appointmentDate: appointmentDate,
      appointmentTime: appointmentTime,
    }

    $.post(morpheo_ajax.ajax_url, data, (response) => {
      $(".loading-spinner").hide()
      $("#book-appointment").prop("disabled", false)

      if (response.success) {
        $(".success-message")
          .html(
            "<h3>🎉 Randevunuz Başarıyla Oluşturuldu!</h3>" +
              "<p>" +
              response.data.message +
              "</p>" +
              "<p><strong>Ödeme için:</strong> E-posta adresinizi kontrol edin veya aşağıdaki linke tıklayın:</p>" +
              '<a href="' +
              response.data.payment_url +
              '" target="_blank" class="btn btn-primary">💳 Ödemeyi Tamamla</a>',
          )
          .show()

        // Hide the form
        $(".calculator-step").hide()
        $(".calculator-navigation").hide()
      } else {
        $(".error-message")
          .html(
            "<h3>❌ Hata Oluştu</h3>" + "<p>" + (response.data || "Randevu oluşturulurken bir hata oluştu.") + "</p>",
          )
          .show()
      }
    }).fail(() => {
      $(".loading-spinner").hide()
      $("#book-appointment").prop("disabled", false)
      $(".error-message")
        .html("<h3>❌ Bağlantı Hatası</h3>" + "<p>Sunucuya bağlanırken bir hata oluştu. Lütfen tekrar deneyin.</p>")
        .show()
    })
  }

  // Set minimum date to today
  const today = new Date().toISOString().split("T")[0]
  $("#appointment_date").attr("min", today)

  // Generate time options
  const timeSelect = $("#appointment_time")
  for (let hour = 9; hour <= 17; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
      const timeString = String(hour).padStart(2, "0") + ":" + String(minute).padStart(2, "0")
      timeSelect.append('<option value="' + timeString + '">' + timeString + "</option>")
    }
  }
})
