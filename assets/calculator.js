;(($) => {
  // Calculator state
  let currentStep = 1
  const calculatorData = {
    websiteType: "corporate",
    pageCount: 5,
    features: [],
    designComplexity: "",
    timeline: "",
    technicalSeo: "basic",
    managementFeatures: [],
    securityFeatures: [],
    ecommerceModules: [],
    userData: {},
  }

  // Website types with prices
  const websiteTypes = {
    corporate: { name: "Kurumsal", basePrice: 15000 },
    ecommerce: { name: "E-Ticaret", basePrice: 25000 },
    blog: { name: "Blog / Haber", basePrice: 8000 },
    landing: { name: "Landing Page", basePrice: 5000 },
  }

  // Feature prices
  const featurePrices = {
    seo: 3000,
    cms: 5000,
    multilang: 4000,
    payment: 6000,
    analytics: 2500,
    social: 1500,
    mobile: 15000,
    api: 4000,
  }

  // E-commerce module prices
  const ecommerceModulePrices = {
    inventory: 3500,
    multivendor: 8000,
    subscription: 4500,
    wishlist: 2000,
    reviews: 2500,
    loyalty: 3000,
    affiliate: 4000,
    b2b: 5000,
    pos: 3500,
    shipping: 3000,
    accounting: 4500,
    "mobile-app": 12000,
  }

  // Design multipliers
  const designMultipliers = {
    basic: 1,
    custom: 1.5,
    premium: 2,
  }

  // Timeline multipliers
  const timelineMultipliers = {
    standard: 1,
    fast: 1.3,
    urgent: 1.6,
  }

  // SEO prices
  const seoOptions = {
    none: 0,
    basic: 2500,
    advanced: 5000,
  }

  // Initialize calculator
  $(document).ready(() => {
    initializeCalculator()
    bindEvents()
    loadTheme()
    generateAppointmentDates()
  })

  function initializeCalculator() {
    updateProgress()
    updateStepContent()

    // Set default selections
    $('.website-type-option[data-type="corporate"]').addClass("selected")

    // SEO basic default olarak seçili
    $("#seo-basic").prop("checked", true)
    $("#seo-basic").closest(".seo-option").addClass("selected")
  }

  function bindEvents() {
    // Theme toggle
    $("#theme-toggle").on("click", toggleTheme)

    // Website type selection
    $(".website-type-option").on("click", function () {
      $(".website-type-option").removeClass("selected")
      $(this).addClass("selected")
      calculatorData.websiteType = $(this).data("type")

      // Show/hide e-commerce modules
      if (calculatorData.websiteType === "ecommerce") {
        $("#ecommerce-modules").show()
      } else {
        $("#ecommerce-modules").hide()
        calculatorData.ecommerceModules = []
        $('#ecommerce-modules input[type="checkbox"]').prop("checked", false)
        $(".module-option").removeClass("selected")
      }
    })

    // Page count slider
    $("#page-count-slider").on("input", function () {
      calculatorData.pageCount = Number.parseInt($(this).val())
      $("#page-count-value").text(calculatorData.pageCount)
    })

    // Design complexity
    $('input[name="design"]').on("change", function () {
      calculatorData.designComplexity = $(this).val()
      $(".design-option").removeClass("selected")
      $(this).closest(".design-option").addClass("selected")
    })

    // Features checkboxes
    $('.feature-option input[type="checkbox"]').on("change", function () {
      const featureId = $(this).val()
      const featureOption = $(this).closest(".feature-option")

      if ($(this).is(":checked")) {
        calculatorData.features.push(featureId)
        featureOption.addClass("selected")
      } else {
        calculatorData.features = calculatorData.features.filter((f) => f !== featureId)
        featureOption.removeClass("selected")
      }
    })

    // Technical SEO
    $('input[name="technical-seo"]').on("change", function () {
      calculatorData.technicalSeo = $(this).val()
      $(".seo-option").removeClass("selected")
      $(this).closest(".seo-option").addClass("selected")
    })

    // E-commerce modules
    $('.module-option input[type="checkbox"]').on("change", function () {
      const moduleId = $(this).val()
      const moduleOption = $(this).closest(".module-option")

      if ($(this).is(":checked")) {
        calculatorData.ecommerceModules.push(moduleId)
        moduleOption.addClass("selected")
      } else {
        calculatorData.ecommerceModules = calculatorData.ecommerceModules.filter((m) => m !== moduleId)
        moduleOption.removeClass("selected")
      }
    })

    // Timeline
    $('input[name="timeline"]').on("change", function () {
      calculatorData.timeline = $(this).val()
      $(".timeline-option").removeClass("selected")
      $(this).closest(".timeline-option").addClass("selected")
    })

    // Navigation buttons
    $("#prev-btn").on("click", previousStep)
    $("#next-btn").on("click", nextStep)

    // Modal events - bu kısmı bindEvents fonksiyonunda güncelleyelim
    $(".modal-close").on("click", closeModal)
    $("#book-appointment-btn").on("click", (e) => {
      e.preventDefault()
      showAppointmentModal()
    })
    $("#appointment-date").on("change", loadTimeSlots)
    $(document).on("click", ".time-slot", selectTimeSlot)
    $("#confirm-appointment-btn").on("click", confirmAppointment)

    // Close modal on outside click
    $(".modal").on("click", function (e) {
      if (e.target === this) {
        closeModal()
      }
    })
  }

  function updateProgress() {
    const progress = (currentStep / 5) * 100
    $("#progress-fill").css("width", progress + "%")
    $("#current-step").text(`Adım ${currentStep} / 5`)
    $("#progress-percent").text(`${Math.round(progress)}% Tamamlandı`)
  }

  function updateStepContent() {
    const stepTitles = {
      1: "Adım 1: Projenin Temelleri",
      2: "Adım 2: Tasarım ve Özellikler",
      3: "Adım 3: Teknik Detaylar",
      4: "Adım 4: Zaman Çizelgesi",
      5: "Adım 5: İletişim Bilgileri",
    }

    const stepDescriptions = {
      1: "Web sitenizin türünü ve sayfa sayısını belirleyin",
      2: "Tasarım yaklaşımınızı seçin ve ek özellikler ekleyin",
      3: "SEO ve e-ticaret modüllerini seçin",
      4: "Proje teslim süresini belirleyin",
      5: "Fiyat teklifi için iletişim bilgilerinizi girin",
    }

    $("#step-title").text(stepTitles[currentStep])
    $("#step-description").text(stepDescriptions[currentStep])

    // Show/hide step content
    $(".step-content").addClass("hidden")
    $(`#step-${currentStep}`).removeClass("hidden")

    // Update navigation buttons
    $("#prev-btn").prop("disabled", currentStep === 1)
    $("#next-btn").text(currentStep === 5 ? "Fiyatı Hesapla" : "İleri")
  }

  function nextStep() {
    if (!validateCurrentStep()) {
      return
    }

    if (currentStep < 5) {
      currentStep++
      updateProgress()
      updateStepContent()
    } else {
      // Calculate and show price
      collectUserData()
      calculateAndShowPrice()
    }
  }

  function previousStep() {
    if (currentStep > 1) {
      currentStep--
      updateProgress()
      updateStepContent()
    }
  }

  function validateCurrentStep() {
    switch (currentStep) {
      case 1:
        return calculatorData.websiteType && calculatorData.pageCount > 0
      case 2:
        return true // Optional features
      case 3:
        return calculatorData.technicalSeo
      case 4:
        return calculatorData.designComplexity && calculatorData.timeline
      case 5:
        const firstName = $("#first-name").val().trim()
        const lastName = $("#last-name").val().trim()
        const email = $("#email").val().trim()
        const phone = $("#phone").val().trim()

        if (!firstName || !lastName || !email || !phone) {
          alert("Lütfen zorunlu alanları doldurun.")
          return false
        }

        if (!isValidEmail(email)) {
          alert("Lütfen geçerli bir e-posta adresi girin.")
          return false
        }

        return true
      default:
        return false
    }
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  function collectUserData() {
    calculatorData.userData = {
      firstName: $("#first-name").val().trim(),
      lastName: $("#last-name").val().trim(),
      email: $("#email").val().trim(),
      phone: $("#phone").val().trim(),
      company: $("#company").val().trim(),
      city: $("#city").val().trim(),
    }
  }

  function calculatePrice() {
    const basePrice = websiteTypes[calculatorData.websiteType].basePrice
    const pagePrice = Math.max(0, calculatorData.pageCount - 5) * 500

    // Calculate features price
    let featuresPrice = 0
    calculatorData.features.forEach((feature) => {
      featuresPrice += featurePrices[feature] || 0
    })

    // Calculate e-commerce modules price
    let ecommercePrice = 0
    calculatorData.ecommerceModules.forEach((module) => {
      ecommercePrice += ecommerceModulePrices[module] || 0
    })

    // Add SEO price
    const seoPrice = seoOptions[calculatorData.technicalSeo] || 0

    // Apply multipliers
    const designMultiplier = designMultipliers[calculatorData.designComplexity] || 1
    const timelineMultiplier = timelineMultipliers[calculatorData.timeline] || 1

    const subtotal =
      (basePrice + pagePrice + featuresPrice + ecommercePrice + seoPrice) * designMultiplier * timelineMultiplier

    // Calculate price range (±15% to +25%)
    const minPrice = Math.ceil((subtotal * 0.85) / 1000) * 1000
    const maxPrice = Math.ceil((subtotal * 1.25) / 1000) * 1000

    return { minPrice, maxPrice }
  }

  function calculateAndShowPrice() {
    const price = calculatePrice()

    // Save data to database
    saveCalculatorData(price)

    // Show price modal
    $("#price-range").text(`${price.minPrice.toLocaleString("tr-TR")} - ${price.maxPrice.toLocaleString("tr-TR")} ₺`)
    $("#price-modal").removeClass("hidden")
  }

  function saveCalculatorData(price) {
    const data = {
      action: "save_calculator_data",
      nonce: window.morpheo_ajax.nonce,
      website_type: calculatorData.websiteType,
      page_count: calculatorData.pageCount,
      features: JSON.stringify(calculatorData.features),
      design_complexity: calculatorData.designComplexity,
      timeline: calculatorData.timeline,
      technical_seo: calculatorData.technicalSeo,
      management_features: JSON.stringify(calculatorData.managementFeatures),
      security_features: JSON.stringify(calculatorData.securityFeatures),
      ecommerce_modules: JSON.stringify(calculatorData.ecommerceModules),
      first_name: calculatorData.userData.firstName,
      last_name: calculatorData.userData.lastName,
      email: calculatorData.userData.email,
      phone: calculatorData.userData.phone,
      company: calculatorData.userData.company,
      city: calculatorData.userData.city,
      min_price: price.minPrice,
      max_price: price.maxPrice,
    }

    $.post(window.morpheo_ajax.ajax_url, data, (response) => {
      if (response.success) {
        calculatorData.calculatorId = response.data.id
      }
    })
  }

  function toggleTheme() {
    const container = $(".morpheo-calculator-container")
    container.toggleClass("dark-mode")

    // Save theme preference
    const isDarkMode = container.hasClass("dark-mode")
    localStorage.setItem("morpheo_theme", isDarkMode ? "dark" : "light")
  }

  function loadTheme() {
    const savedTheme = localStorage.getItem("morpheo_theme")
    const container = $(".morpheo-calculator-container")

    // Default to dark mode
    if (savedTheme === "light") {
      container.removeClass("dark-mode")
    } else {
      container.addClass("dark-mode")
    }
  }

  function closeModal() {
    $(".modal").addClass("hidden")
  }

  function showAppointmentModal() {
    console.log("Opening appointment modal...")
    $("#price-modal").addClass("hidden")

    // Make sure appointment dates are generated
    if ($("#appointment-date option").length <= 1) {
      console.log("Generating appointment dates...")
      generateAppointmentDates()
    }

    $("#appointment-modal").removeClass("hidden")
    console.log("Appointment modal should be visible now")
  }

  function generateAppointmentDates() {
    const dateSelect = $("#appointment-date")

    // Clear existing options except the first one
    dateSelect.find("option:not(:first)").remove()

    const today = new Date()

    for (let i = 1; i <= 14; i++) {
      const date = new Date(today)
      date.setDate(today.getDate() + i)

      // Skip weekends
      if (date.getDay() !== 0 && date.getDay() !== 6) {
        const dateStr = date.toISOString().split("T")[0]
        const displayDate = date.toLocaleDateString("tr-TR", {
          weekday: "long",
          year: "numeric",
          month: "long",
          day: "numeric",
        })

        dateSelect.append(`<option value="${dateStr}">${displayDate}</option>`)
      }
    }
  }

  function loadTimeSlots() {
    const timeSlots = $("#time-slots")
    timeSlots.empty()

    const times = [
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
    ]

    times.forEach((time) => {
      timeSlots.append(`<div class="time-slot" data-time="${time}">${time}</div>`)
    })
  }

  function selectTimeSlot() {
    $(".time-slot").removeClass("selected")
    $(this).addClass("selected")
    calculatorData.appointmentTime = $(this).data("time")
    $("#confirm-appointment-btn").prop("disabled", false)
  }

  function confirmAppointment() {
    const appointmentDate = $("#appointment-date").val()

    if (!appointmentDate || !calculatorData.appointmentTime) {
      alert("Lütfen tarih ve saat seçin.")
      return
    }

    // Redirect URL is provided via localized script
    const bookingUrl = window.morpheo_ajax.booking_url || "https://hakantapan.com/iletisim"

    // Open booking page directly in new tab
    window.open(bookingUrl, "_blank")

    // Close modal and show success message
    closeModal()
    alert(
      "Randevu talebiniz için iletişim sayfasına yönlendiriliyorsunuz. Lütfen formu doldurun ve randevu bilgilerinizi belirtin.",
    )
  }
})(window.jQuery)
