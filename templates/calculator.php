<div class="morpheo-calculator">
    <div class="calculator-header">
        <h2>Web Sitesi Fiyat Hesaplayıcı</h2>
        <p>Projenizin detaylarını girin ve anında fiyat teklifi alın</p>
    </div>
    
    <div class="progress-bar">
        <div class="progress-fill"></div>
    </div>
    
    <div class="calculator-content">
        <!-- Step 1: Website Type -->
        <div class="calculator-step active" id="step-1">
            <div class="step-header">
                <h3 class="step-title">Web Sitesi Türü</h3>
                <p class="step-description">Hangi tür bir web sitesi istiyorsunuz?</p>
            </div>
            
            <div class="radio-group">
                <div class="radio-item">
                    <input type="radio" id="business" name="website_type" value="business">
                    <label for="business">
                        <strong>Kurumsal Web Sitesi</strong><br>
                        <small>Şirket tanıtımı ve hizmetler</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="ecommerce" name="website_type" value="ecommerce">
                    <label for="ecommerce">
                        <strong>E-Ticaret Sitesi</strong><br>
                        <small>Online satış platformu</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="portfolio" name="website_type" value="portfolio">
                    <label for="portfolio">
                        <strong>Portföy/Kişisel Site</strong><br>
                        <small>Kişisel blog veya portföy</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="blog" name="website_type" value="blog">
                    <label for="blog">
                        <strong>Blog/İçerik Sitesi</strong><br>
                        <small>Makale ve içerik paylaşımı</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="landing" name="website_type" value="landing">
                    <label for="landing">
                        <strong>Landing Page</strong><br>
                        <small>Tek sayfa tanıtım sitesi</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="custom" name="website_type" value="custom">
                    <label for="custom">
                        <strong>Özel Proje</strong><br>
                        <small>Özelleştirilmiş çözüm</small>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Step 2: Pages -->
        <div class="calculator-step" id="step-2">
            <div class="step-header">
                <h3 class="step-title">Sayfa Sayısı</h3>
                <p class="step-description">Web sitenizde kaç sayfa olmasını istiyorsunuz?</p>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="pages">Sayfa Sayısı</label>
                <div class="pages-input-container">
                    <input type="number" id="pages" class="pages-input" min="1" max="100" value="5">
                </div>
                <p class="step-description">Ortalama: Ana sayfa, Hakkımızda, Hizmetler, İletişim, Blog</p>
            </div>
        </div>
        
        <!-- Step 3: Features -->
        <div class="calculator-step" id="step-3">
            <div class="step-header">
                <h3 class="step-title">Özellikler</h3>
                <p class="step-description">Hangi özellikleri istiyorsunuz? (İsteğe bağlı)</p>
            </div>
            
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="responsive" name="features[]" value="responsive">
                    <label for="responsive">Mobil Uyumlu Tasarım</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="seo" name="features[]" value="seo">
                    <label for="seo">SEO Optimizasyonu</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="cms" name="features[]" value="cms">
                    <label for="cms">İçerik Yönetim Sistemi</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="ecommerce_feature" name="features[]" value="ecommerce">
                    <label for="ecommerce_feature">E-Ticaret Entegrasyonu</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="blog_feature" name="features[]" value="blog">
                    <label for="blog_feature">Blog Sistemi</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="contact" name="features[]" value="contact">
                    <label for="contact">İletişim Formu</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="gallery" name="features[]" value="gallery">
                    <label for="gallery">Galeri/Portföy</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="social" name="features[]" value="social">
                    <label for="social">Sosyal Medya Entegrasyonu</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="analytics" name="features[]" value="analytics">
                    <label for="analytics">Analytics Entegrasyonu</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="multilingual" name="features[]" value="multilingual">
                    <label for="multilingual">Çoklu Dil Desteği</label>
                </div>
            </div>
        </div>
        
        <!-- Step 4: Design Complexity -->
        <div class="calculator-step" id="step-4">
            <div class="step-header">
                <h3 class="step-title">Tasarım Karmaşıklığı</h3>
                <p class="step-description">Nasıl bir tasarım istiyorsunuz?</p>
            </div>
            
            <div class="radio-group">
                <div class="radio-item">
                    <input type="radio" id="simple" name="design_complexity" value="simple">
                    <label for="simple">
                        <strong>Basit Tasarım</strong><br>
                        <small>Temiz ve minimal</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="moderate" name="design_complexity" value="moderate">
                    <label for="moderate">
                        <strong>Orta Düzey Tasarım</strong><br>
                        <small>Özelleştirilmiş ve modern</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="complex" name="design_complexity" value="complex">
                    <label for="complex">
                        <strong>Karmaşık Tasarım</strong><br>
                        <small>Özel animasyonlar ve etkiler</small>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Step 5: Timeline -->
        <div class="calculator-step" id="step-5">
            <div class="step-header">
                <h3 class="step-title">Zaman Çizelgesi</h3>
                <p class="step-description">Projenin ne zaman tamamlanmasını istiyorsunuz?</p>
            </div>
            
            <div class="radio-group">
                <div class="radio-item">
                    <input type="radio" id="asap" name="timeline" value="asap">
                    <label for="asap">
                        <strong>En Kısa Sürede</strong><br>
                        <small>Acil proje</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="1-2weeks" name="timeline" value="1-2weeks">
                    <label for="1-2weeks">
                        <strong>1-2 Hafta</strong><br>
                        <small>Hızlı teslimat</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="1month" name="timeline" value="1month">
                    <label for="1month">
                        <strong>1 Ay</strong><br>
                        <small>Standart süre</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="2-3months" name="timeline" value="2-3months">
                    <label for="2-3months">
                        <strong>2-3 Ay</strong><br>
                        <small>Detaylı planlama</small>
                    </label>
                </div>
                <div class="radio-item">
                    <input type="radio" id="flexible" name="timeline" value="flexible">
                    <label for="flexible">
                        <strong>Esnek</strong><br>
                        <small>Zaman kısıtı yok</small>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Step 6: Contact & Appointment -->
        <div class="calculator-step" id="step-6">
            <div class="step-header">
                <h3 class="step-title">İletişim Bilgileri ve Randevu</h3>
                <p class="step-description">Bilgilerinizi girin ve randevu alın</p>
            </div>
            
            <div class="price-display">
                <div class="price-range" id="price-range">Hesaplanıyor...</div>
                <p class="price-note">*Kesin fiyat görüşme sonrası belirlenecektir</p>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="first_name">Ad</label>
                <input type="text" id="first_name" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="last_name">Soyad</label>
                <input type="text" id="last_name" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">E-posta</label>
                <input type="email" id="email" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="phone">Telefon</label>
                <input type="tel" id="phone" class="form-input" required>
            </div>
            
            <div class="appointment-section">
                <h3>📅 Randevu Seçin</h3>
                <div class="appointment-grid">
                    <div class="form-group">
                        <label class="form-label" for="appointment_date">Tarih</label>
                        <input type="date" id="appointment_date" class="date-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="appointment_time">Saat</label>
                        <select id="appointment_time" class="time-select" required>
                            <option value="">Saat seçin</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="summary-section">
                <h3>📋 Özet</h3>
                <div class="summary-item">
                    <span class="summary-label">Proje Türü:</span>
                    <span class="summary-value" id="summary-type">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Sayfa Sayısı:</span>
                    <span class="summary-value" id="summary-pages">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Özellikler:</span>
                    <span class="summary-value" id="summary-features">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Tasarım:</span>
                    <span class="summary-value" id="summary-complexity">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Zaman:</span>
                    <span class="summary-value" id="summary-timeline">-</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Tahmini Fiyat:</span>
                    <span class="summary-value" id="summary-price">-</span>
                </div>
            </div>
        </div>
        
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Randevunuz oluşturuluyor...</p>
        </div>
        
        <div class="success-message" style="display: none;"></div>
        <div class="error-message" style="display: none;"></div>
        
        <div class="calculator-navigation">
            <button type="button" class="btn btn-secondary btn-prev">← Önceki</button>
            <button type="button" class="btn btn-primary btn-next">Sonraki →</button>
            <button type="button" class="btn btn-primary" id="book-appointment" style="display: none;">
                📅 Randevu Al ve Ödeme Yap
            </button>
        </div>
    </div>
</div>
