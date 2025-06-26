<div id="morpheo-calculator" class="morpheo-calculator-container">
    <!-- Theme Toggle -->
    <div class="theme-toggle-container">
        <button id="theme-toggle" class="theme-toggle-btn">
            <span>🌙</span>
        </button>
    </div>

    <!-- Header -->
    <div class="calculator-header">
        <div class="header-icon">
            <span>🌐</span>
        </div>
        <h1>Morpheo Dijital Website Fiyatlandırma Aracı</h1>
        <p>Profesyonel web sitesi projeleriniz için anında fiyat teklifi alın</p>
        <div class="warning">
            <strong>Bilgi:</strong> Bu araç tahmini fiyatlandırma sağlar. Kesin fiyat için detaylı görüşme gereklidir.
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
        </div>
        <div class="progress-info">
            <span id="current-step">Adım 1 / 5</span>
            <span id="progress-percent">20% Tamamlandı</span>
        </div>
    </div>

    <!-- Calculator Card -->
    <div class="calculator-card">
        <div class="card-header">
            <h2 id="step-title">Adım 1: Projenin Temelleri</h2>
            <p id="step-description">Web sitenizin türünü ve sayfa sayısını belirleyin</p>
        </div>

        <div class="card-content">
            <!-- Step 1: Website Type & Page Count -->
            <div class="step-content" id="step-1">
                <div class="form-group">
                    <label class="form-label">Web Sitesi Türü</label>
                    <div class="website-types-grid">
                        <div class="website-type-option" data-type="corporate">
                            <span class="type-icon">🏢</span>
                            <span class="type-name">Kurumsal Website</span>
                        </div>
                        <div class="website-type-option" data-type="ecommerce">
                            <span class="type-icon">🛒</span>
                            <span class="type-name">E-Ticaret</span>
                        </div>
                        <div class="website-type-option" data-type="blog">
                            <span class="type-icon">📝</span>
                            <span class="type-name">Blog / Haber</span>
                        </div>
                        <div class="website-type-option" data-type="landing">
                            <span class="type-icon">🎯</span>
                            <span class="type-name">Landing Page</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tahmini Sayfa Sayısı: <span id="page-count-value">5</span></label>
                    <input type="range" id="page-count-slider" min="1" max="50" value="5" class="slider">
                    <div class="slider-labels">
                        <span>1 Sayfa</span>
                        <span>50+ Sayfa</span>
                    </div>
                </div>
                <div id="step-1-error" class="error-message hidden"></div>
            </div>

            <!-- Step 2: Design & Features -->
            <div class="step-content hidden" id="step-2">
                <div class="form-group">
                    <label class="form-label">Tasarım Yaklaşımı</label>
                    <div class="design-options">
                        <div class="design-option">
                            <input type="radio" name="design" value="basic" id="design-basic">
                            <label for="design-basic">
                                <div class="option-content">
                                    <span>Temel Tasarım</span>
                                    <span class="option-info" title="Hazır WordPress temaları kullanılarak hızlı çözüm. Temel özelleştirmeler, logo entegrasyonu ve renk uyarlaması dahil. 2-3 hafta içinde teslim. Küçük işletmeler ve hızlı başlangıç için ideal.">ℹ️</span>
                                </div>
                                <span class="option-price">Ek ücret yok</span>
                            </label>
                        </div>
                        <div class="design-option">
                            <input type="radio" name="design" value="custom" id="design-custom">
                            <label for="design-custom">
                                <div class="option-content">
                                    <span>Özel Tasarım</span>
                                    <span class="option-info" title="Markanıza özel tasarım çalışması. Benzersiz layout, özel grafik tasarımlar, marka kimliğine uygun renk paleti ve tipografi. Responsive tasarım garantisi. Orta ölçekli işletmeler için önerilen seçenek.">ℹ️</span>
                                </div>
                                <span class="option-price">+%50 ek ücret</span>
                            </label>
                        </div>
                        <div class="design-option">
                            <input type="radio" name="design" value="premium" id="design-premium">
                            <label for="design-premium">
                                <div class="option-content">
                                    <span>Premium Tasarım</span>
                                    <span class="option-info" title="Tamamen özel, animasyonlu ve interaktif tasarım. Micro-interactions, paralaks efektler, özel animasyonlar, gelişmiş UX/UI tasarımı. A/B test optimizasyonu ve kullanıcı deneyimi analizi dahil. Büyük şirketler ve premium markalar için.">ℹ️</span>
                                </div>
                                <span class="option-price">+%100 ek ücret</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ek Özellikler</label>
                    <div class="features-list">
                        <div class="feature-option">
                            <input type="checkbox" id="seo" value="seo">
                            <label for="seo">
                                <div class="option-content">
                                    <span>SEO Optimizasyonu</span>
                                    <span class="option-info" title="Kapsamlı arama motoru optimizasyonu: Meta etiketler, yapılandırılmış veri, XML sitemap, robots.txt, sayfa hızı optimizasyonu, mobil uyumluluk, Google Analytics ve Search Console kurulumu. İlk 3 ayda %40-60 organik trafik artışı hedeflenir.">ℹ️</span>
                                </div>
                                <span class="feature-price">+3,000 ₺</span>
                            </label>
                        </div>
                        <div class="feature-option">
                            <input type="checkbox" id="cms" value="cms">
                            <label for="cms">
                                <div class="option-content">
                                    <span>İçerik Yönetim Sistemi</span>
                                    <span class="option-info" title="Kullanıcı dostu admin paneli ile kolay içerik yönetimi. Sayfa/blog yazısı ekleme, medya yönetimi, menü düzenleme, kullanıcı rolleri ve yetkilendirme sistemi. Eğitim videoları ve 3 aylık teknik destek dahil.">ℹ️</span>
                                </div>
                                <span class="feature-price">+5,000 ₺</span>
                            </label>
                        </div>
                        <div class="feature-option">
                            <input type="checkbox" id="multilang" value="multilang">
                            <label for="multilang">
                                <div class="option-content">
                                    <span>Çoklu Dil Desteği</span>
                                    <span class="option-info" title="Profesyonel çoklu dil sistemi: Dil değiştirici, URL yapısı optimizasyonu, her dil için ayrı SEO ayarları, otomatik dil algılama, RTL dil desteği. 2 dil kurulumu dahil, ek diller için +500₺/dil.">ℹ️</span>
                                </div>
                                <span class="feature-price">+4,000 ₺</span>
                            </label>
                        </div>
                        <div class="feature-option">
                            <input type="checkbox" id="payment" value="payment">
                            <label for="payment">
                                <div class="option-content">
                                    <span>Ödeme Sistemi Entegrasyonu</span>
                                    <span class="option-info" title="Güvenli ödeme altyapısı: İyzico, PayTR, Stripe entegrasyonu. Kredi kartı, havale/EFT, kapıda ödeme seçenekleri. SSL sertifikası, PCI DSS uyumluluk, otomatik fatura sistemi ve ödeme raporlama dahil.">ℹ️</span>
                                </div>
                                <span class="feature-price">+6,000 ₺</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div id="step-2-error" class="error-message hidden"></div>
            </div>

            <!-- Step 3: Technical Features -->
            <div class="step-content hidden" id="step-3">
                <div class="form-group">
                    <label class="form-label">Teknik SEO Paketi</label>
                    <div class="seo-options">
                        <div class="seo-option">
                            <input type="radio" name="technical-seo" value="none" id="seo-none">
                            <label for="seo-none">
                                <div class="option-content">
                                    <span>SEO İstemiyorum</span>
                                    <span class="option-info" title="Hiçbir SEO çalışması yapılmaz. Site arama motorlarında görünmeyebilir. Sadece mevcut müşterilerinizin direkt erişim sağladığı siteler için uygundur. Önerilmez.">ℹ️</span>
                                </div>
                            </label>
                        </div>
                        <div class="seo-option">
                            <input type="radio" name="technical-seo" value="basic" id="seo-basic" checked>
                            <label for="seo-basic">
                                <div class="option-content">
                                    <span>Temel SEO Kurulumu</span>
                                    <span class="option-info" title="Temel SEO gereksinimleri: Title/meta description optimizasyonu, H1-H6 etiket yapısı, alt text'ler, temel XML sitemap, robots.txt, Google Analytics kurulumu. Yerel işletmeler ve küçük siteler için yeterli.">ℹ️</span>
                                </div>
                                <span class="option-price">+2,500 ₺</span>
                            </label>
                        </div>
                        <div class="seo-option">
                            <input type="radio" name="technical-seo" value="advanced" id="seo-advanced">
                            <label for="seo-advanced">
                                <div class="option-content">
                                    <span>Gelişmiş SEO Çalışması</span>
                                    <span class="option-info" title="Kapsamlı SEO stratejisi: Anahtar kelime araştırması, rakip analizi, teknik SEO audit, sayfa hızı optimizasyonu, yapılandırılmış veri, yerel SEO, backlink stratejisi, 6 aylık SEO takip raporu. Rekabetçi sektörler için şart.">ℹ️</span>
                                </div>
                                <span class="option-price">+5,000 ₺</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- E-commerce Modules -->
                <div class="form-group" id="ecommerce-modules" style="display: none;">
                    <label class="form-label">E-Ticaret Modülleri</label>
                    <div class="ecommerce-modules-list">
                        <div class="module-option">
                            <input type="checkbox" id="inventory" value="inventory">
                            <label for="inventory">
                                <div class="option-content">
                                    <span>Gelişmiş Stok Yönetimi</span>
                                    <span class="option-info" title="Profesyonel stok takip sistemi: Çoklu depo yönetimi, otomatik stok uyarıları, barkod sistemi, stok hareketleri raporu, minimum stok seviyeleri, tedarikçi yönetimi. Büyük ürün kataloğu olan işletmeler için kritik.">ℹ️</span>
                                </div>
                                <span class="module-price">+3,500 ₺</span>
                            </label>
                        </div>
                        <div class="module-option">
                            <input type="checkbox" id="multivendor" value="multivendor">
                            <label for="multivendor">
                                <div class="option-content">
                                    <span>Çoklu Satıcı Sistemi</span>
                                    <span class="option-info" title="Marketplace platformu: Satıcı kayıt sistemi, komisyon yönetimi, satıcı paneli, ürün onay süreci, ayrı faturalandırma, satıcı performans raporları. Amazon/Trendyol benzeri platform kurmak isteyenler için.">ℹ️</span>
                                </div>
                                <span class="module-price">+8,000 ₺</span>
                            </label>
                        </div>
                        <div class="module-option">
                            <input type="checkbox" id="subscription" value="subscription">
                            <label for="subscription">
                                <div class="option-content">
                                    <span>Abonelik Sistemi</span>
                                    <span class="option-info" title="Tekrarlayan ödeme sistemi: Aylık/yıllık abonelikler, otomatik yenileme, abonelik planları, deneme süreleri, abonelik yönetim paneli, churn analizi. SaaS ürünler ve abonelik modeli işletmeler için.">ℹ️</span>
                                </div>
                                <span class="module-price">+4,500 ₺</span>
                            </label>
                        </div>
                        <div class="module-option">
                            <input type="checkbox" id="mobile-app" value="mobile-app">
                            <label for="mobile-app">
                                <div class="option-content">
                                    <span>Mobil Uygulama</span>
                                    <span class="option-info" title="Native iOS ve Android uygulaması: Push bildirimler, offline çalışma, kamera entegrasyonu, GPS lokasyon, uygulama mağazası yayınlama, 1 yıl güncelleme desteği. Müşteri sadakati artırmak için güçlü araç.">ℹ️</span>
                                </div>
                                <span class="module-price">+12,000 ₺</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div id="step-3-error" class="error-message hidden"></div>
            </div>

            <!-- Step 4: Timeline -->
            <div class="step-content hidden" id="step-4">
                <div class="form-group">
                    <label class="form-label">Proje Teslim Süresi</label>
                    <div class="timeline-options">
                        <div class="timeline-option">
                            <input type="radio" name="timeline" value="standard" id="timeline-standard">
                            <label for="timeline-standard">
                                <div class="option-content">
                                    <span>Standart (4-6 Hafta)</span>
                                    <span class="option-info" title="Normal çalışma temposu: Detaylı planlama, kaliteli tasarım süreci, kapsamlı test aşaması, revizyon hakları. En kaliteli sonuç için önerilen süre. Çoğu proje için ideal zaman dilimi.">ℹ️</span>
                                </div>
                                <span class="option-price">Ek ücret yok</span>
                            </label>
                        </div>
                        <div class="timeline-option">
                            <input type="radio" name="timeline" value="fast" id="timeline-fast">
                            <label for="timeline-fast">
                                <div class="option-content">
                                    <span>Hızlı (2-3 Hafta)</span>
                                    <span class="option-info" title="Hızlandırılmış süreç: Öncelikli çalışma, ek ekip kaynağı, sınırlı revizyon hakkı. Acil lansmanlar için uygun. Bazı detay çalışmaları kısıtlanabilir.">ℹ️</span>
                                </div>
                                <span class="option-price">+%30 ek ücret</span>
                            </label>
                        </div>
                        <div class="timeline-option">
                            <input type="radio" name="timeline" value="urgent" id="timeline-urgent">
                            <label for="timeline-urgent">
                                <div class="option-content">
                                    <span>Acil (1-2 Hafta)</span>
                                    <span class="option-info" title="Acil durum süreci: 7/24 çalışma, tüm ekip kaynağı, minimum revizyon. Sadece kritik durumlar için. Ek özellikler sınırlanabilir, temel fonksiyonlara odaklanılır.">ℹ️</span>
                                </div>
                                <span class="option-price">+%60 ek ücret</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div id="step-4-error" class="error-message hidden"></div>
            </div>

            <!-- Step 5: Contact Information -->
            <div class="step-content hidden" id="step-5">
                <div class="contact-form-grid">
                    <div class="form-group">
                        <label for="first-name">Ad *</label>
                        <input type="text" id="first-name" required placeholder="Adınız">
                    </div>
                    <div class="form-group">
                        <label for="last-name">Soyad *</label>
                        <input type="text" id="last-name" required placeholder="Soyadınız">
                    </div>
                    <div class="form-group">
                        <label for="email">E-posta *</label>
                        <input type="email" id="email" required placeholder="ornek@email.com">
                    </div>
                    <div class="form-group">
                        <label for="phone">Telefon *</label>
                        <input type="tel" id="phone" required placeholder="0555 123 45 67">
                    </div>
                    <div class="form-group">
                        <label for="company">Şirket</label>
                        <input type="text" id="company" placeholder="Şirket adınız (opsiyonel)">
                    </div>
                    <div class="form-group">
                        <label for="city">Şehir</label>
                        <input type="text" id="city" placeholder="Şehriniz (opsiyonel)">
                    </div>
                </div>
                <div id="step-5-error" class="error-message hidden"></div>
            </div>

            <!-- Navigation Buttons -->
            <div class="navigation-buttons">
                <button type="button" id="prev-btn" class="btn btn-outline" disabled>← Geri</button>
                <button type="button" id="next-btn" class="btn btn-primary">İleri →</button>
            </div>
        </div>
    </div>

    <!-- Price Result Modal -->
    <div id="price-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🎉 Fiyat Hesaplaması Tamamlandı!</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="price-result">
                    <div class="price-range">
                        <span class="price-label">Tahmini Proje Maliyeti:</span>
                        <span class="price-value" id="price-range"></span>
                    </div>
                    <div class="price-note">
                        <strong>📋 Önemli Not:</strong> Bu fiyat aralığı tahminidir ve proje detaylarına göre değişiklik gösterebilir. Kesin fiyat teklifi için detaylı görüşme yapılması gerekmektedir.
                    </div>
                    <button id="book-appointment-btn" class="btn btn-primary btn-large">
                        💼 Ücretli Konsültasyon Randevusu Al
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointment Modal -->
    <div id="appointment-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📅 Ücretli Konsültasyon Randevusu</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="appointment-form">
                    <div class="consultation-fee-info">
                        <div class="fee-notice">
                            <strong>💰 Konsültasyon Ücreti:</strong> <span id="consultation-fee"></span> ₺
                            <p>Detaylı proje analizi ve özel çözüm önerileri için profesyonel konsültasyon hizmeti.</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Randevu Tarihi Seçin</label>
                        <select id="appointment-date">
                            <option value="">Tarih seçiniz...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Randevu Saati Seçin</label>
                        <div class="time-slots" id="time-slots">
                            <!-- Time slots will be populated by JavaScript -->
                        </div>
                    </div>
                    <div id="appointment-error" class="error-message hidden"></div>
                    <button id="confirm-appointment-btn" class="btn btn-primary btn-large" disabled>
                        💳 Ödeme Yap ve Randevuyu Onayla
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
