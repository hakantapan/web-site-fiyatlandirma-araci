<div class="morpheo-calculator" data-theme="<?php echo esc_attr($atts['theme']); ?>">
    <button class="theme-toggle" title="Tema Değiştir">🌙</button>
    
    <h2>🚀 Web Sitesi Fiyat Hesaplayıcı</h2>
    
    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        <div class="progress-text">Adım 1 / 6</div>
    </div>

    <!-- Step 1: Website Type -->
    <div class="calculator-step" data-step="1">
        <div class="step-header">
            <h3 class="step-title">🌐 Hangi Tür Website İstiyorsunuz?</h3>
            <p class="step-description">Projenizin türünü seçerek başlayalım</p>
        </div>
        
        <div class="option-cards">
            <div class="option-card">
                <input type="radio" name="website_type" value="corporate" id="corporate" required>
                <div class="option-title">🏢 Kurumsal Website</div>
                <div class="option-description">Şirket tanıtımı, hizmetler, iletişim sayfaları</div>
                <div class="option-price">8.000₺'den başlayan fiyatlar</div>
            </div>
            
            <div class="option-card">
                <input type="radio" name="website_type" value="ecommerce" id="ecommerce" required>
                <div class="option-title">🛒 E-Ticaret Sitesi</div>
                <div class="option-description">Online mağaza, ürün kataloğu, ödeme sistemi</div>
                <div class="option-price">15.000₺'den başlayan fiyatlar</div>
            </div>
            
            <div class="option-card">
                <input type="radio" name="website_type" value="blog" id="blog" required>
                <div class="option-title">📝 Blog/İçerik Sitesi</div>
                <div class="option-description">Makale paylaşımı, kategori yönetimi</div>
                <div class="option-price">5.000₺'den başlayan fiyatlar</div>
            </div>
            
            <div class="option-card">
                <input type="radio" name="website_type" value="landing" id="landing" required>
                <div class="option-title">🎯 Özel Kampanya Sayfası</div>
                <div class="option-description">Tek sayfa, odaklanmış içerik, dönüşüm odaklı</div>
                <div class="option-price">3.000₺'den başlayan fiyatlar</div>
            </div>
        </div>
        
        <div class="button-group">
            <button type="button" class="btn btn-primary btn-next">İleri →</button>
        </div>
    </div>

    <!-- Step 2: Page Count -->
    <div class="calculator-step" data-step="2">
        <div class="step-header">
            <h3 class="step-title">📄 Kaç Sayfa Olacak?</h3>
            <p class="step-description">Website'nizde bulunmasını istediğiniz sayfa sayısını belirleyin</p>
        </div>
        
        <div class="range-container">
            <input type="range" id="page-count" class="range-slider" min="1" max="50" value="5">
            <div class="range-value">5 sayfa</div>
        </div>
        
        <div class="page-examples">
            <div class="example-box">
                <h4>💡 Örnek Sayfalar:</h4>
                <ul>
                    <li>Ana Sayfa</li>
                    <li>Hakkımızda</li>
                    <li>Hizmetlerimiz</li>
                    <li>İletişim</li>
                    <li>Blog/Haberler</li>
                </ul>
            </div>
        </div>
        
        <div class="button-group">
            <button type="button" class="btn btn-secondary btn-prev">← Geri</button>
            <button type="button" class="btn btn-primary btn-next">İleri →</button>
        </div>
    </div>

    <!-- Step 3: Features -->
    <div class="calculator-step" data-step="3">
        <div class="step-header">
            <h3 class="step-title">⚙️ Hangi Özellikler Olsun?</h3>
            <p class="step-description">İhtiyacınız olan özellikleri seçin</p>
        </div>
        
        <div class="feature-grid">
            <div class="feature-item">
                <input type="checkbox" name="features[]" value="seo" id="seo">
                <label for="seo">🔍 SEO Optimizasyonu</label>
            </div>
            
            <div class="feature-item">
                <input type="checkbox" name="features[]" value="cms" id="cms">
                <label for="cms">📝 İçerik Yönetimi</label>
            </div>
            
            <div class="feature-item">
                <input type="checkbox" name="features[]" value="multilang" id="multilang">
                <label for="multilang">🌍 Çoklu Dil Desteği</label>
            </div>
            
            <div class="feature-item">
                <input type="checkbox" name="features[]" value="payment" id="payment">
                <label for="payment">💳 Online Ödeme</label>
            </div>
            
            <div class="feature-item">
                <input type="checkbox" name="features[]" value="booking" id="booking">
                <label for="booking">📅 Randevu Sistemi</label>
            </div>
            
            <div class="feature-item">
                <input type="checkbox" name="features[]" value="analytics" id="analytics">
                <label for="analytics">📊 Analitik Raporlama</label>
            </div>
        </div>
        
        <div class="button-group">
            <button type="button" class="btn btn-secondary btn-prev">← Geri</button>
            <button type="button" class="btn btn-primary btn-next">İleri →</button>
        </div>
    </div>

    <!-- Step 4: Design Complexity -->
    <div class="calculator-step" data-step="4">
        <div class="step-header">
            <h3 class="step-title">🎨 Tasarım Seviyesi</h3>
            <p class="step-description">Hangi seviyede bir tasarım istiyorsunuz?</p>
        </div>
        
        <div class="option-cards">
            <div class="option-card">
                <input type="radio" name="design_complexity" value="basic" id="basic" required>
                <div class="option-title">✨ Profesyonel & Sade</div>
                <div class="option-description">Temiz, modern ve kullanıcı dostu tasarım</div>
                <div class="option-price">Standart fiyat</div>
            </div>
            
            <div class="option-card">
                <input type="radio" name="design_complexity" value="custom" id="custom" required>
                <div class="option-title">🎯 Markanıza Özel</div>
                <div class="option-description">Özel tasarım, marka kimliğinize uygun</div>
                <div class="option-price">+50% ek ücret</div>
            </div>
            
            <div class="option-card">
                <input type="radio" name="design_complexity" value="premium" id="premium" required>
                <div class="option-title">💎 Lüks & Etkileyici</div>
                <div class="option-description">Premium animasyonlar, özel efektler</div>
                <div class="option-price">+120% ek ücret</div>
            </div>
        </div>
        
        <div class="button-group">
            <button type="button" class="btn btn-secondary btn-prev">← Geri</button>
            <button type="button" class="btn btn-primary btn-next">İleri →</button>
        </div>
    </div>

    <!-- Step 5: Timeline & Additional Options -->
    <div class="calculator-step" data-step="5">
        <div class="step-header">
            <h3 class="step-title">⏰ Teslim Süresi</h3>
            <p class="step-description">Projenizin ne kadar sürede tamamlanmasını istiyorsunuz?</p>
        </div>
        
        <div class="option-cards">
            <div class="option-card">
                <input type="radio" name="timeline" value="urgent" id="urgent" required>
                <div class="option-title">🚀 Acil (1-2 Hafta)</div>
                <div class="option-description">Hızlı teslimat, öncelikli çalışma</div>
                <div class="option-price">+50% ek ücret</div>
            </div>
            
            <div class="option-card">
                <input type="radio" name="timeline" value="normal" id="normal" required>
                <div class="option-title">⚡ Normal (3-4 Hafta)</div>
                <div class="option-description">Standart çalışma süresi</div>
                <div class="option-price">Standart fiyat</div>
            </div>
            
            <div class="option-card">
                <input type="radio" name="timeline" value="flexible" id="flexible" required>
                <div class="option-title">🕐 Esnek (5-8 Hafta)</div>
                <div class="option-description">Daha uygun fiyat, esnek süre</div>
                <div class="option-price">-10% indirim</div>
            </div>
        </div>

        <div class="additional-options">
            <h4>🔧 Ek Teknik Özellikler</h4>
            
            <div class="form-group">
                <label>SEO & Performans:</label>
                <div class="option-cards">
                    <div class="option-card">
                        <input type="radio" name="technical_seo" value="basic" id="seo_basic" required>
                        <div class="option-title">Temel SEO</div>
                        <div class="option-description">Meta etiketler, sitemap</div>
                    </div>
                    <div class="option-card">
                        <input type="radio" name="technical_seo" value="advanced" id="seo_advanced" required>
                        <div class="option-title">Gelişmiş SEO</div>
                        <div class="option-description">Schema markup, hız optimizasyonu</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Yönetim Paneli:</label>
                <div class="option-cards">
                    <div class="option-card">
                        <input type="radio" name="management_features" value="basic" id="mgmt_basic" required>
                        <div class="option-title">Temel Panel</div>
                        <div class="option-description">İçerik düzenleme, medya yönetimi</div>
                    </div>
                    <div class="option-card">
                        <input type="radio" name="management_features" value="advanced" id="mgmt_advanced" required>
                        <div class="option-title">Gelişmiş Panel</div>
                        <div class="option-description">Kullanıcı yönetimi, raporlama</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Güvenlik:</label>
                <div class="option-cards">
                    <div class="option-card">
                        <input type="radio" name="security_features" value="standard" id="security_standard" required>
                        <div class="option-title">Standart Güvenlik</div>
                        <div class="option-description">SSL, temel koruma</div>
                    </div>
                    <div class="option-card">
                        <input type="radio" name="security_features" value="enhanced" id="security_enhanced" required>
                        <div class="option-title">Gelişmiş Güvenlik</div>
                        <div class="option-description">Firewall, malware koruması</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>E-Ticaret Modülleri:</label>
                <div class="option-cards">
                    <div class="option-card">
                        <input type="radio" name="ecommerce_modules" value="none" id="ecom_none" required>
                        <div class="option-title">E-Ticaret Yok</div>
                        <div class="option-description">Sadece tanıtım sitesi</div>
                    </div>
                    <div class="option-card">
                        <input type="radio" name="ecommerce_modules" value="basic" id="ecom_basic" required>
                        <div class="option-title">Temel E-Ticaret</div>
                        <div class="option-description">Ürün kataloğu, sepet</div>
                    </div>
                    <div class="option-card">
                        <input type="radio" name="ecommerce_modules" value="advanced" id="ecom_advanced" required>
                        <div class="option-title">Gelişmiş E-Ticaret</div>
                        <div class="option-description">Stok yönetimi, raporlama</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="button-group">
            <button type="button" class="btn btn-secondary btn-prev">← Geri</button>
            <button type="button" class="btn btn-success btn-calculate">💰 Fiyat Hesapla</button>
        </div>
    </div>

    <!-- Step 6: Contact Information -->
    <div class="calculator-step" data-step="6">
        <div class="step-header">
            <h3 class="step-title">📋 İletişim Bilgileri</h3>
            <p class="step-description">Size özel teklifimizi hazırlayabilmemiz için bilgilerinizi paylaşın</p>
        </div>
        
        <div class="contact-form">
            <form id="contact-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Ad *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Soyad *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">E-posta *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Telefon *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="company">Şirket</label>
                        <input type="text" id="company" name="company">
                    </div>
                    <div class="form-group">
                        <label for="city">Şehir</label>
                        <input type="text" id="city" name="city">
                    </div>
                </div>
            </form>
        </div>
        
        <div class="button-group">
            <button type="button" class="btn btn-secondary btn-prev">← Geri</button>
            <button type="button" class="btn btn-success btn-calculate">💰 Fiyat Hesapla</button>
        </div>
    </div>

    <!-- Results Step -->
    <div class="calculator-step" data-step="7">
        <div class="results-container">
            <h3 class="results-title">🎉 Tahmini Fiyat Aralığınız</h3>
            <div class="price-range">0 - 0 ₺</div>
            <p class="results-description">
                Bu fiyat tahmini seçimlerinize göre hesaplanmıştır. 
                Kesin fiyat için ücretsiz konsültasyon randevusu alabilirsiniz.
            </p>
            
            <div class="results-features">
                <div class="result-feature">
                    <div class="result-feature-title">📱 Mobil Uyumlu</div>
                    <div>Tüm cihazlarda mükemmel görünüm</div>
                </div>
                <div class="result-feature">
                    <div class="result-feature-title">🚀 Hızlı Yükleme</div>
                    <div>Optimize edilmiş performans</div>
                </div>
                <div class="result-feature">
                    <div class="result-feature-title">🔒 Güvenli</div>
                    <div>SSL sertifikası ve güvenlik</div>
                </div>
                <div class="result-feature">
                    <div class="result-feature-title">📞 Destek</div>
                    <div>1 yıl ücretsiz teknik destek</div>
                </div>
            </div>
            
            <?php if ($atts['show_appointment'] === 'true'): ?>
            <div class="appointment-section">
                <h4>📅 Ücretsiz Konsültasyon</h4>
                <p>Projenizi detaylı konuşmak ve kesin fiyat almak için randevu alın.</p>
                <button type="button" class="btn btn-primary btn-book-appointment">
                    📞 Ücretsiz Konsültasyon Randevusu Al
                </button>
            </div>
            <?php endif; ?>
            
            <div class="contact-options">
                <h4>📞 Hemen İletişime Geçin</h4>
                <div class="contact-buttons">
                    <a href="tel:+905551234567" class="btn btn-success">📞 Ara</a>
                    <a href="https://wa.me/905551234567" class="btn btn-success" target="_blank">💬 WhatsApp</a>
                    <a href="mailto:info@morpheodijital.com" class="btn btn-primary">📧 E-posta</a>
                </div>
            </div>
        </div>
        
        <div class="button-group">
            <button type="button" class="btn btn-secondary btn-prev">← Geri</button>
            <button type="button" class="btn btn-primary" onclick="location.reload()">🔄 Yeni Hesaplama</button>
        </div>
    </div>
</div>

<style>
.page-examples {
    margin-top: 20px;
}

.example-box {
    background: #f8fbff;
    border: 1px solid #e1e8ed;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.morpheo-calculator.dark-theme .example-box {
    background: #16213e;
    border-color: #0f3460;
}

.example-box h4 {
    margin-bottom: 15px;
    color: #2c3e50;
}

.morpheo-calculator.dark-theme .example-box h4 {
    color: #ffffff;
}

.example-box ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.example-box li {
    padding: 5px 0;
    color: #666;
}

.morpheo-calculator.dark-theme .example-box li {
    color: #ccc;
}

.additional-options {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #e1e8ed;
}

.morpheo-calculator.dark-theme .additional-options {
    border-top-color: #0f3460;
}

.additional-options h4 {
    text-align: center;
    margin-bottom: 25px;
    color: #2c3e50;
}

.morpheo-calculator.dark-theme .additional-options h4 {
    color: #ffffff;
}

.appointment-section {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 25px;
    margin: 25px 0;
    text-align: center;
    backdrop-filter: blur(10px);
}

.appointment-section h4 {
    margin-bottom: 10px;
    font-size: 20px;
}

.appointment-section p {
    margin-bottom: 20px;
    opacity: 0.9;
}

.contact-options {
    margin-top: 30px;
    text-align: center;
}

.contact-options h4 {
    margin-bottom: 20px;
}

.contact-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.contact-buttons .btn {
    min-width: 120px;
}

@media (max-width: 768px) {
    .contact-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .contact-buttons .btn {
        width: 200px;
    }
}
</style>
