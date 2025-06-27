;(($) => {
  // Calculator state
  let currentStep = 1
  const calculatorData = {
    purpose: "",
    businessType: "",
    onlinePayment: "",
    contactMethods: [],
    websiteType: "",
    pageCount: 3,
    selectedPages: [],
    features: [],
    designComplexity: "",
    userData: {},
    recommendation: null,
  }

  // Website types with prices
  const websiteTypes = {
    corporate: { name: "Kurumsal Website", basePrice: 15000 },
    ecommerce: { name: "E-Ticaret Sitesi", basePrice: 25000 },
    blog: { name: "Blog/İçerik Sitesi", basePrice: 8000 },
    landing: { name: "Özel Kampanya Sayfası", basePrice: 5000 },
  }

  // Feature prices
  const featurePrices = {
    seo: 3000,
    cms: 5000,
    multilang: 4000,
    payment: 6000,
  }

  // Design multipliers
  const designMultipliers = {
    basic: 1,
    custom: 1.5,
    premium: 2,
  }

  // Recommendation logic
  const recommendationRules = {
    "sell-products": {
      yes: "ecommerce",
      maybe: "corporate",
      no: "corporate",
    },
    "showcase-business": {
      yes: "ecommerce",
      maybe: "corporate",
      no: "corporate",
    },
    "share-content": {
      yes: "blog",
      maybe: "blog",
      no: "blog",
    },
    "single-campaign": {
      yes: "landing",
      maybe: "landing",
      no: "landing",
    },
    "not-sure": {
      yes: "ecommerce",
      maybe: "corporate",
      no: "corporate",
    },
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
  }

  function bindEvents() {
    // Theme toggle
    $("#theme-toggle").on("click", toggleTheme)

    // Purpose selection
    $(".purpose-option").on("click", function () {
      $(".purpose-option").removeClass("selected")
      $(this).addClass("selected")
      calculatorData.purpose = $(this).data("purpose")
      hideErrorMessage(1)
    })

    // Business type selection
    $('input[name="business-type"]').on("change", function () {
      calculatorData.businessType = $(this).val()
      hideErrorMessage(2)
    })

    // Online payment selection
    $('input[name="online-payment"]').on("change", function () {
      calculatorData.onlinePayment = $(this).val()
      hideErrorMessage(2)
    })

    // Contact methods
    $('input[name="contact-method"]').on("change", function () {
      const method = $(this).val()
      if ($(this).is(":checked")) {
        calculatorData.contactMethods.push(method)
      } else {
        calculatorData.contactMethods = calculatorData.contactMethods.filter((m) => m !== method)
      }
    })

    // Website type selection (alternative options)
    $(".website-type-option").on("click", function () {
      $(".website-type-option").removeClass("selected")
      $(this).addClass("selected")
      calculatorData.websiteType = $(this).data("type")
      hideErrorMessage(3)
    })

    // Page selection
    $('input[name="pages"]').on("change", function () {
      const page = $(this).val()
      if ($(this).is(":checked")) {
        calculatorData.selectedPages.push(page)
      } else {
        calculatorData.selectedPages = calculatorData.selectedPages.filter((p) => p !== page)
      }
      updatePageCount()
    })

    // Design complexity
    $('input[name="design"]').on("change", function () {
      calculatorData.designComplexity = $(this).val()
      $(".design-option").removeClass("selected")
      $(this).closest(".design-option").addClass("selected")
      hideErrorMessage(5)
    })

    // Features checkboxes
    $('.feature-card input[type="checkbox"]').on("change", function () {
      const featureId = $(this).val()
      const featureCard = $(this).closest(".feature-card")

      if ($(this).is(":checked")) {
        calculatorData.features.push(featureId)
        featureCard.addClass("selected")
      } else {
        calculatorData.features = calculatorData.features.filter((f) => f !== featureId)
        featureCard.removeClass("selected")
      }
    })

    // Contact form inputs
    $("#first-name, #last-name, #email, #phone").on("input", () => {
      hideErrorMessage(6)
    })

    // Navigation buttons
    $("#prev-btn").on("click", previousStep)
    $("#next-btn").on("click", nextStep)

    // Modal events
    $(".modal-close").on("click", closeModal)
    $("#book-appointment-btn").on("click", (e) => {
      e.preventDefault()
      showAppointmentModal()
    })
    $("#appointment-date").on("change", loadTimeSlots)
    $(document).on("click", ".time-slot:not(.disabled)", selectTimeSlot)
    $("#confirm-appointment-btn").on("click", confirmAppointment)

    // Close modal on outside click
    $(".modal").on("click", function (e) {
      if (e.target === this) {
        closeModal()
      }
    })
  }

  function updatePageCount() {
    const basePages = 3 // Ana sayfa, Hakkımızda, İletişim
    const additionalPages = calculatorData.selectedPages.length
    calculatorData.pageCount = basePages + additionalPages
    $("#page-count-display").text(calculatorData.pageCount)
  }

  function generateRecommendation() {
    if (!calculatorData.purpose || !calculatorData.onlinePayment) {
      return null
    }

    const recommendedType = recommendationRules[calculatorData.purpose][calculatorData.onlinePayment]
    calculatorData.websiteType = recommendedType

    return {
      type: recommendedType,
      confidence: "high",
      reasoning: getRecommendationReasoning(
        calculatorData.purpose,
        calculatorData.onlinePayment,
        calculatorData.businessType,
      ),
    }
  }

  function getRecommendationReasoning(purpose, payment, businessType) {
    const reasons = {
      "sell-products": {
        yes: `Online ürün satışı yapmak istediğiniz için <strong>E-Ticaret Sitesi</strong> en uygun seçenek. Ürünlerinizi sergileyebilir, stok takibi yapabilir ve güvenli ödeme alabilirsiniz.`,
        maybe: `Şimdilik online ödeme almayacağınız için <strong>Kurumsal Website</strong> ile başlayıp, ileride e-ticaret özelliklerini ekleyebiliriz.`,
        no: `Ürünlerinizi tanıtmak için <strong>Kurumsal Website</strong> ideal. Müşteriler ürünlerinizi görüp telefon/mail ile sipariş verebilir.`,
      },
      "showcase-business": {
        yes: `Hizmet satışı yapacağınız için <strong>E-Ticaret Sitesi</strong> öneriyoruz. Hizmet paketlerinizi satabilir, randevu sistemi ekleyebiliriz.`,
        maybe: `<strong>Kurumsal Website</strong> ile işinizi profesyonelce tanıtabilir, ileride online ödeme sistemi ekleyebiliriz.`,
        no: `İşinizi tanıtmak için <strong>Kurumsal Website</strong> mükemmel. Hizmetlerinizi, referanslarınızı gösterip müşteri çekebilirsiniz.`,
      },
      "share-content": {
        yes: `İçerik paylaşımından gelir elde etmek için <strong>Blog/İçerik Sitesi</strong> ideal. Reklam, sponsorluk veya premium içerik satabilirsiniz.`,
        maybe: `<strong>Blog/İçerik Sitesi</strong> ile başlayıp, ileride monetizasyon seçeneklerini değerlendirebiliriz.`,
        no: `İçerik paylaşımı için <strong>Blog/İçerik Sitesi</strong> en uygun. SEO ile Google'da üst sıralarda çıkabilirsiniz.`,
      },
      "single-campaign": {
        yes: `Tek ürün/hizmet satışı için <strong>Özel Kampanya Sayfası</strong> en etkili. Odaklanmış tasarım ile dönüşüm oranınız yüksek olur.`,
        maybe: `<strong>Özel Kampanya Sayfası</strong> ile başlayıp, ileride ödeme sistemi ekleyebiliriz.`,
        no: `Kampanyanızı tanıtmak için <strong>Özel Kampanya Sayfası</strong> ideal. Tek sayfada tüm bilgileri verebilirsiniz.`,
      },
      "not-sure": {
        yes: `Henüz net karar vermediğiniz için <strong>E-Ticaret Sitesi</strong> öneriyoruz. Hem tanıtım hem satış yapabilirsiniz.`,
        maybe: `<strong>Kurumsal Website</strong> ile başlamanızı öneriyoruz. Esnek yapısı sayesinde ileride her türlü özelliği ekleyebiliriz.`,
        no: `<strong>Kurumsal Website</strong> en güvenli seçenek. İşinizi tanıtır, ileride ihtiyaçlarınıza göre geliştirebiliriz.`,
      },
    }

    return reasons[purpose][payment] || "Size uygun çözümü birlikte belirleyelim."
  }

  function showRecommendation() {
    const recommendation = generateRecommendation()
    if (!recommendation) return

    const websiteType = websiteTypes[recommendation.type]
    const recommendationHtml = `
      <div class="recommended-card">
        <div class="recommendation-badge">
          <span>🎯 Size Özel Öneri</span>
        </div>
        <div class="recommendation-content">
          <div class="recommendation-type">
            <h3>${websiteType.name}</h3>
            <div class="recommendation-price">
              ${websiteType.basePrice.toLocaleString("tr-TR")} ₺'den başlayan fiyatlarla
            </div>
          </div>
          <div class="recommendation-reasoning">
            <p>${recommendation.reasoning}</p>
          </div>
          <div class="recommendation-features">
            <h4>Bu çözümde neler var?</h4>
            <ul id="recommendation-features-list">
              ${getRecommendationFeatures(recommendation.type)}
            </ul>
          </div>
        </div>
      </div>
    `

    $("#recommended-solution").html(recommendationHtml)

    // Auto-select the recommended option
    $(`.website-type-option[data-type="${recommendation.type}"]`).addClass("selected")
  }

  function getRecommendationFeatures(type) {
    const features = {
      corporate: [
        "Profesyonel kurumsal tasarım",
        "Mobil uyumlu responsive yapı",
        "İletişim formları",
        "Google harita entegrasyonu",
        "Sosyal medya bağlantıları",
        "Temel SEO optimizasyonu",
      ],
      ecommerce: [
        "Ürün katalog sistemi",
        "Sepet ve ödeme sistemi",
        "Stok takip sistemi",
        "Müşteri hesap paneli",
        "Sipariş yönetimi",
        "Kargo entegrasyonu",
      ],
      blog: [
        "İçerik yönetim sistemi",
        "Kategori ve etiket sistemi",
        "Yorum sistemi",
        "Sosyal medya paylaşım",
        "SEO optimizasyonu",
        "E-bülten sistemi",
      ],
      landing: [
        "Tek sayfa odaklanmış tasarım",
        "Yüksek dönüşüm optimizasyonu",
        "İletişim formları",
        "Sosyal kanıt alanları",
        "Hızlı yükleme",
        "Mobil optimizasyon",
      ],
    }

    return features[type].map((feature) => `<li>✅ ${feature}</li>`).join("")
  }

  function showErrorMessage(step, message) {
    const errorEl = $(`#step-${step}-error`)
    errorEl.text(message).removeClass("hidden")
    errorEl[0].scrollIntoView({ behavior: "smooth", block: "center" })
  }

  function hideErrorMessage(step) {
    $(`#step-${step}-error`).addClass("hidden").text("")
  }

  function updateProgress() {
    const progress = (currentStep / 6) * 100
    $("#progress-fill").css("width", progress + "%")
    $("#current-step").text(`Adım ${currentStep} / 6`)
    $("#progress-percent").text(`${Math.round(progress)}% Tamamlandı`)
  }

  function updateStepContent() {
    const stepTitles = {
      1: "Adım 1: Web Sitenizin Amacı Nedir?",
      2: "Adım 2: İşiniz Hakkında Bilgi",
      3: "Adım 3: Size Özel Öneri",
      4: "Adım 4: Sayfa İçerikleri",
      5: "Adım 5: Tasarım ve Özellikler",
      6: "Adım 6: İletişim Bilgileri",
    }

    const stepDescriptions = {
      1: "Web sitenizle ne yapmak istediğinizi anlayalım",
      2: "İşinizin detaylarını öğrenelim",
      3: "Size en uygun çözümü belirleyelim",
      4: "Hangi sayfaların olacağını planlayalım",
      5: "Sitenizin görünümünü ve özelliklerini seçelim",
      6: "Kişisel teklifinizi hazırlayalım",
    }

    $("#step-title").text(stepTitles[currentStep])
    $("#step-description").text(stepDescriptions[currentStep])

    // Show/hide step content
    $(".step-content").addClass("hidden")
    $(`#step-${currentStep}`).removeClass("hidden")

    // Hide all error messages when changing steps
    for (let i = 1; i <= 6; i++) {
      hideErrorMessage(i)
    }

    // Update navigation buttons
    $("#prev-btn").prop("disabled", currentStep === 1)
    $("#next-btn").text(currentStep === 6 ? "Teklifimi Hazırla 🎯" : "İleri →")

    // Special handling for step 3 (recommendation)
    if (currentStep === 3) {
      showRecommendation()
    }
  }

  function nextStep() {
    if (!validateCurrentStep()) {
      return
    }

    if (currentStep < 6) {
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
        if (!calculatorData.purpose) {
          showErrorMessage(1, "Lütfen web sitenizin amacını seçin.")
          return false
        }
        return true

      case 2:
        if (!calculatorData.businessType) {
          showErrorMessage(2, "Lütfen işletme türünüzü seçin.")
          return false
        }
        if (!calculatorData.onlinePayment) {
          showErrorMessage(2, "Lütfen online ödeme tercihinizi belirtin.")
          return false
        }
        return true

      case 3:
        if (!calculatorData.websiteType) {
          showErrorMessage(3, "Lütfen bir web sitesi türü seçin.")
          return false
        }
        return true

      case 4:
        // Page selection is optional, always valid
        return true

      case 5:
        if (!calculatorData.designComplexity) {
          showErrorMessage(5, "Lütfen tasarım yaklaşımını seçin.")
          return false
        }
        return true

      case 6:
        const firstName = $("#first-name").val().trim()
        const lastName = $("#last-name").val().trim()
        const email = $("#email").val().trim()
        const phone = $("#phone").val().trim()

        if (!firstName) {
          showErrorMessage(6, "Lütfen adınızı girin.")
          $("#first-name").focus()
          return false
        }

        if (!lastName) {
          showErrorMessage(6, "Lütfen soyadınızı girin.")
          $("#last-name").focus()
          return false
        }

        if (!email) {
          showErrorMessage(6, "Lütfen e-posta adresinizi girin.")
          $("#email").focus()
          return false
        }

        if (!isValidEmail(email)) {
          showErrorMessage(6, "Lütfen geçerli bir e-posta adresi girin.")
          $("#email").focus()
          return false
        }

        if (!phone) {
          showErrorMessage(6, "Lütfen telefon numaranızı girin.")
          $("#phone").focus()
          return false
        }

        if (!isValidPhone(phone)) {
          showErrorMessage(6, "Lütfen geçerli bir telefon numarası girin.")
          $("#phone").focus()
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

  function isValidPhone(phone) {
    const phoneRegex = /^(\+90|0)?[5][0-9]{9}$/
    const cleanPhone = phone.replace(/[\s\-$$$$]/g, "")
    return phoneRegex.test(cleanPhone) || cleanPhone.length >= 10
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

    // Apply design multiplier
    const designMultiplier = designMultipliers[calculatorData.designComplexity] || 1

    const subtotal = (basePrice + pagePrice + featuresPrice) * designMultiplier

    // Calculate price range (±15% to +25%)
    const minPrice = Math.ceil((subtotal * 0.85) / 1000) * 1000
    const maxPrice = Math.ceil((subtotal * 1.25) / 1000) * 1000

    return {
      minPrice,
      maxPrice,
      breakdown: {
        basePrice,
        pagePrice,
        featuresPrice,
        designMultiplier,
        subtotal,
      },
    }
  }

  // Update the calculateAndShowPrice function to include loading animation

  function calculateAndShowPrice() {
    // Show loading overlay
    showLoadingOverlay()

    // Simulate calculation steps with delays for better UX
    setTimeout(() => {
      updateCalculationStep(1, "active")

      setTimeout(() => {
        updateCalculationStep(1, "completed")
        updateCalculationStep(2, "active")

        setTimeout(() => {
          updateCalculationStep(2, "completed")
          updateCalculationStep(3, "active")

          setTimeout(() => {
            updateCalculationStep(3, "completed")
            updateCalculationStep(4, "active")

            setTimeout(() => {
              updateCalculationStep(4, "completed")

              // Actually calculate the price
              const price = calculatePrice()

              // Save data to database
              saveCalculatorData(price)

              setTimeout(() => {
                // Hide loading and show price modal
                hideLoadingOverlay()
                showPriceModal(price)
              }, 500)
            }, 800)
          }, 600)
        }, 700)
      }, 500)
    }, 300)
  }

  function showLoadingOverlay() {
    const loadingHtml = `
    <div id="loading-overlay" class="loading-overlay">
      <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text">
          Teklifiniz Hazırlanıyor<span class="loading-dots"></span>
        </div>
        <div class="loading-description">
          Size özel fiyat hesaplaması yapılıyor
        </div>
        
        <div class="calculation-steps">
          <div class="calculation-step" id="step-1">
            <span class="step-icon">📊</span>
            <span>Proje türü analiz ediliyor</span>
          </div>
          <div class="calculation-step" id="step-2">
            <span class="step-icon">📄</span>
            <span>Sayfa sayısı hesaplanıyor</span>
          </div>
          <div class="calculation-step" id="step-3">
            <span class="step-icon">🎨</span>
            <span>Tasarım karmaşıklığı değerlendiriliyor</span>
          </div>
          <div class="calculation-step" id="step-4">
            <span class="step-icon">💰</span>
            <span>Fiyat aralığı belirleniyor</span>
          </div>
        </div>
      </div>
    </div>
  `

    $("body").append(loadingHtml)

    // Reset all steps
    $(".calculation-step").removeClass("active completed")
  }

  function hideLoadingOverlay() {
    $("#loading-overlay").addClass("hidden")
    setTimeout(() => {
      $("#loading-overlay").remove()
    }, 300)
  }

  function updateCalculationStep(stepNumber, status) {
    const step = $(`#step-${stepNumber}`)

    if (status === "active") {
      step.addClass("active").removeClass("completed")
    } else if (status === "completed") {
      step.removeClass("active").addClass("completed")
      step.find(".step-icon").html("✅")
    }
  }

  function showPriceModal(price) {
    // Price summary
    const websiteType = websiteTypes[calculatorData.websiteType]
    const summaryHtml = `
      <div class="price-summary-content">
        <div class="selected-solution">
          <h3>📋 Seçtiğiniz Çözüm</h3>
          <div class="solution-details">
            <div class="solution-type">${websiteType.name}</div>
            <div class="solution-features">
              <span>${calculatorData.pageCount} sayfa</span>
              <span>${calculatorData.designComplexity === "basic" ? "Profesyonel" : calculatorData.designComplexity === "custom" ? "Özel" : "Premium"} tasarım</span>
              ${calculatorData.features.length > 0 ? `<span>${calculatorData.features.length} ek özellik</span>` : ""}
            </div>
          </div>
        </div>
      </div>
    `
    $("#price-summary").html(summaryHtml)

    // Price breakdown
    const breakdownHtml = `
      <div class="price-breakdown-content">
        <h4>💰 Fiyat Detayları</h4>
        <div class="breakdown-items">
          <div class="breakdown-item">
            <span>Temel ${websiteType.name}</span>
            <span>${price.breakdown.basePrice.toLocaleString("tr-TR")} ₺</span>
          </div>
          ${
            price.breakdown.pagePrice > 0
              ? `
            <div class="breakdown-item">
              <span>Ek sayfalar (${calculatorData.pageCount - 5} sayfa)</span>
              <span>${price.breakdown.pagePrice.toLocaleString("tr-TR")} ₺</span>
            </div>
          `
              : ""
          }
          ${
            price.breakdown.featuresPrice > 0
              ? `
            <div class="breakdown-item">
              <span>Ek özellikler</span>
              <span>${price.breakdown.featuresPrice.toLocaleString("tr-TR")} ₺</span>
            </div>
          `
              : ""
          }
          ${
            price.breakdown.designMultiplier > 1
              ? `
            <div class="breakdown-item">
              <span>Tasarım ek ücreti (%${Math.round((price.breakdown.designMultiplier - 1) * 100)})</span>
              <span>${(price.breakdown.subtotal - (price.breakdown.basePrice + price.breakdown.pagePrice + price.breakdown.featuresPrice)).toLocaleString("tr-TR")} ₺</span>
            </div>
          `
              : ""
          }
        </div>
      </div>
    `
    $("#price-breakdown").html(breakdownHtml)

    // Show price range
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
      timeline: "standard", // Default timeline
      technical_seo: calculatorData.features.includes("seo") ? "basic" : "none",
      management_features: JSON.stringify([]),
      security_features: JSON.stringify([]),
      ecommerce_modules: JSON.stringify([]),
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
      } else {
        console.error("Failed to save calculator data", response.data)
      }
    })
  }

  function toggleTheme() {
    const container = $(".morpheo-calculator-container")
    container.toggleClass("dark-mode")
    const isDarkMode = container.hasClass("dark-mode")
    localStorage.setItem("morpheo_theme", isDarkMode ? "dark" : "light")
  }

  function loadTheme() {
    const savedTheme = localStorage.getItem("morpheo_theme")
    const container = $(".morpheo-calculator-container")
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
    $("#price-modal").addClass("hidden")

    // Konsültasyon ücretini göster
    $("#consultation-fee").text(window.morpheo_ajax.consultation_fee || "250")

    if ($("#appointment-date option").length <= 1) {
      generateAppointmentDates()
    }
    $("#appointment-modal").removeClass("hidden")
  }

  function generateAppointmentDates() {
    const dateSelect = $("#appointment-date")
    dateSelect.find("option:not(:first)").remove()
    const today = new Date()

    for (let i = 1; i <= 14; i++) {
      const date = new Date(today)
      date.setDate(today.getDate() + i)
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
    const selectedDate = $("#appointment-date").val()
    const timeSlots = $("#time-slots")

    if (!selectedDate) {
      timeSlots.empty()
      return
    }

    timeSlots.html('<div class="loading">Müsait saatler yükleniyor...</div>')

    $.post(
      window.morpheo_ajax.ajax_url,
      {
        action: "get_available_time_slots",
        nonce: window.morpheo_ajax.nonce,
        date: selectedDate,
      },
      (response) => {
        timeSlots.empty()
        if (response.success) {
          const bookedSlots = response.data.booked_slots || []
          const allTimes = [
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

          allTimes.forEach((time) => {
            const isBooked = bookedSlots.includes(time)
            const slotClass = isBooked ? "time-slot disabled" : "time-slot"
            const slotTitle = isBooked ? "Bu saat dolu" : "Müsait"

            timeSlots.append(
              `<div class="${slotClass}" data-time="${time}" title="${slotTitle}">
                ${time}
                ${isBooked ? '<span class="booked-indicator">✗</span>' : ""}
              </div>`,
            )
          })

          $("#confirm-appointment-btn").prop("disabled", true)
        } else {
          timeSlots.html('<div class="error">Saatler yüklenirken hata oluştu.</div>')
        }
      },
    ).fail(() => {
      timeSlots.html('<div class="error">Saatler yüklenirken hata oluştu.</div>')
    })
  }

  function selectTimeSlot() {
    if ($(this).hasClass("disabled")) {
      return false
    }

    $(".time-slot").removeClass("selected")
    $(this).addClass("selected")
    calculatorData.appointmentTime = $(this).data("time")
    $("#confirm-appointment-btn").prop("disabled", false)
  }

  function confirmAppointment() {
    const appointmentDate = $("#appointment-date").val()
    const errorEl = $("#appointment-error")

    errorEl.addClass("hidden").text("")

    if (!appointmentDate) {
      errorEl.text("Lütfen randevu tarihi seçin.").removeClass("hidden")
      return
    }

    if (!calculatorData.appointmentTime) {
      errorEl.text("Lütfen randevu saati seçin.").removeClass("hidden")
      return
    }

    // Disable button to prevent double booking
    $("#confirm-appointment-btn").prop("disabled", true).text("Randevu kaydediliyor...")

    // First book the appointment
    $.post(
      window.morpheo_ajax.ajax_url,
      {
        action: "book_appointment",
        nonce: window.morpheo_ajax.nonce,
        calculator_id: calculatorData.calculatorId,
        appointment_date: appointmentDate,
        appointment_time: calculatorData.appointmentTime,
      },
      (response) => {
        if (response.success) {
          // Appointment booked successfully, now redirect to payment
          const woocommerceUrl =
            window.morpheo_ajax.woocommerce_url ||
            "https://morpheodijital.com/satis/checkout-link/?urun=web-site-on-gorusme-randevusu"

          // Randevu bilgilerini URL parametreleri olarak hazırla
          const appointmentParams = new URLSearchParams({
            randevu_tarihi: appointmentDate,
            randevu_saati: calculatorData.appointmentTime,
            musteri_adi: calculatorData.userData.firstName + " " + calculatorData.userData.lastName,
            musteri_email: calculatorData.userData.email,
            musteri_telefon: calculatorData.userData.phone,
            proje_tipi: calculatorData.websiteType,
            tahmini_fiyat: $("#price-range").text(),
            calculator_id: calculatorData.calculatorId || "",
            appointment_id: response.data.appointment_id,
          })

          // WooCommerce sitesine yönlendir
          const separator = woocommerceUrl.includes("?") ? "&" : "?"
          const paymentUrl = `${woocommerceUrl}${separator}${appointmentParams.toString()}`

          // Yeni sekmede aç
          window.open(paymentUrl, "_blank")

          // Modal'ı kapat ve bilgi mesajı göster
          closeModal()

          alert(
            `Randevunuz geçici olarak rezerve edildi ve ödeme sayfasına yönlendiriliyorsunuz.\n\n` +
              `Randevu Detayları:\n` +
              `📅 Tarih: ${new Date(appointmentDate).toLocaleDateString("tr-TR")}\n` +
              `🕐 Saat: ${calculatorData.appointmentTime}\n` +
              `💰 Ücret: ${window.morpheo_ajax.consultation_fee} ₺\n\n` +
              `⚠️ Önemli: Ödeme işlemini 15 dakika içinde tamamlamazsanız randevunuz iptal olacaktır.`,
          )
        } else {
          errorEl.text(response.data.message || "Randevu kaydedilirken hata oluştu.").removeClass("hidden")
          $("#confirm-appointment-btn").prop("disabled", false).text("💳 Ödeme Yap ve Randevuyu Onayla")
        }
      },
    ).fail(() => {
      errorEl.text("Randevu kaydedilirken hata oluştu. Lütfen tekrar deneyin.").removeClass("hidden")
      $("#confirm-appointment-btn").prop("disabled", false).text("💳 Ödeme Yap ve Randevuyu Onayla")
    })
  }
})(window.jQuery)
