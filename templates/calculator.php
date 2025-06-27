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
        <h1>Web Sitenizi Birlikte Planlayalım</h1>
        <p>Size en uygun web sitesi türünü ve fiyatını birlikte belirleyelim</p>
        <div class="warning">
            <strong>💡 Bilgi:</strong> Sorularımız sizi doğru çözüme yönlendirecek. Teknik bilgi gerekmez!
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
        </div>
        <div class="progress-info">
            <span id="current-step">Adım 1 / 6</span>
            <span id="progress-percent">17% Tamamlandı</span>
        </div>
    </div>

    <!-- Calculator Card -->
    <div class="calculator-card">
        <div class="card-header">
            <h2 id="step-title">Adım 1: Web Sitenizin Amacı Nedir?</h2>
            <p id="step-description">Web sitenizle ne yapmak istediğinizi anlayalım</p>
        </div>

        <div class="card-content">
            <!-- Step 1: Purpose Analysis -->
            <div class="step-content" id="step-1">
                <div class="form-group">
                    <label class="form-label">Web sitenizle hangi amacı gerçekleştirmek istiyorsunuz?</label>
                    <div class="purpose-options">
                        <div class="purpose-option" data-purpose="sell-products">
                            <div class="purpose-icon">🛒</div>
                            <div class="purpose-content">
                                <h3>Ürün/Hizmet Satmak</h3>
                                <p>Online mağaza açıp ürünlerinizi satmak istiyorum</p>
                                <div class="purpose-examples">
                                    <span>Örnek: Trendyol, GittiGidiyor, kendi mağazanız</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="purpose-option" data-purpose="showcase-business">
                            <div class="purpose-icon">🏢</div>
                            <div class="purpose-content">
                                <h3>İşimi Tanıtmak</h3>
                                <p>Şirketimi, hizmetlerimi tanıtıp müşteri çekmek istiyorum</p>
                                <div class="purpose-examples">
                                    <span>Örnek: Avukat, doktor, berber, temizlik şirketi siteleri</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="purpose-option" data-purpose="share-content">
                            <div class="purpose-icon">📝</div>
                            <div class="purpose-content">
                                <h3>İçerik Paylaşmak</h3>
                                <p>Blog yazıları, haberler, makaleler paylaşmak istiyorum</p>
                                <div class="purpose-examples">
                                    <span>Örnek: Hürriyet, Sabah, kişisel blog siteleri</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="purpose-option" data-purpose="single-campaign">
                            <div class="purpose-icon">🎯</div>
                            <div class="purpose-content">
                                <h3>Tek Ürün/Kampanya</h3>
                                <p>Belirli bir ürün, hizmet veya etkinlik için özel sayfa</p>
                                <div class="purpose-examples">
                                    <span>Örnek: Kurs satış sayfası, etkinlik duyuru sayfası</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="purpose-option" data-purpose="not-sure">
                            <div class="purpose-icon">🤔</div>
                            <div class="purpose-content">
                                <h3>Emin Değilim</h3>
                                <p>Tam olarak ne istediğimi bilmiyorum, yardım edin</p>
                                <div class="purpose-examples">
                                    <span>Size uygun çözümü birlikte bulalım</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="step-1-error" class="error-message hidden"></div>
            </div>

            <!-- Step 2: Business Details -->
            <div class="step-content hidden" id="step-2">
                <div class="form-group">
                    <label class="form-label">İşiniz hakkında bize biraz bilgi verin</label>
                    <div class="business-questions">
                        <div class="question-card">
                            <h4>🏪 Ne tür bir işletmeniz var?</h4>
                            <div class="business-types">
                                <label class="business-type-option">
                                    <input type="radio" name="business-type" value="retail">
                                    <span>Perakende/Mağaza (kıyafet, elektronik, vs.)</span>
                                </label>
                                <label class="business-type-option">
                                    <input type="radio" name="business-type" value="service">
                                    <span>Hizmet Sektörü (kuaför, temizlik, danışmanlık, vs.)</span>
                                </label>
                                <label class="business-type-option">
                                    <input type="radio" name="business-type" value="restaurant">
                                    <span>Restoran/Kafe</span>
                                </label>
                                <label class="business-type-option">
                                    <input type="radio" name="business-type" value="healthcare">
                                    <span>Sağlık (doktor, diş hekimi, vs.)</span>
                                </label>
                                <label class="business-type-option">
                                    <input type="radio" name="business-type" value="education">
                                    <span>Eğitim/Kurs</span>
                                </label>
                                <label class="business-type-option">
                                    <input type="radio" name="business-type" value="other">
                                    <span>Diğer</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="question-card">
                            <h4>💰 Online ödeme alacak mısınız?</h4>
                            <div class="yes-no-options">
                                <label class="yn-option">
                                    <input type="radio" name="online-payment" value="yes">
                                    <span class="yn-content">
                                        <strong>Evet</strong>
                                        <small>Kredi kartı ile ödeme alacağım</small>
                                    </span>
                                </label>
                                <label class="yn-option">
                                    <input type="radio" name="online-payment" value="no">
                                    <span class="yn-content">
                                        <strong>Hayır</strong>
                                        <small>Sadece bilgi verip telefon/mail ile iletişim</small>
                                    </span>
                                </label>
                                <label class="yn-option">
                                    <input type="radio" name="online-payment" value="maybe">
                                    <span class="yn-content">
                                        <strong>Belki</strong>
                                        <small>Şimdilik değil ama ileride ekleyebilirim</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="question-card">
                            <h4>📱 Müşterileriniz nasıl size ulaşıyor?</h4>
                            <div class="contact-methods">
                                <label class="contact-option">
                                    <input type="checkbox" name="contact-method" value="phone">
                                    <span>📞 Telefon</span>
                                </label>
                                <label class="contact-option">
                                    <input type="checkbox" name="contact-method" value="whatsapp">
                                    <span>💬 WhatsApp</span>
                                </label>
                                <label class="contact-option">
                                    <input type="checkbox" name="contact-method" value="email">
                                    <span>📧 E-posta</span>
                                </label>
                                <label class="contact-option">
                                    <input type="checkbox" name="contact-method" value="visit">
                                    <span>🏪 Mağazaya geliyorlar</span>
                                </label>
                                <label class="contact-option">
                                    <input type="checkbox" name="contact-method" value="social">
                                    <span>📱 Sosyal medya</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="step-2-error" class="error-message hidden"></div>
            </div>

            <!-- Step 3: Website Recommendation -->
            <div class="step-content hidden" id="step-3">
                <div class="recommendation-result">
                    <div class="recommendation-header">
                        <h3>🎉 Size Özel Önerimiz Hazır!</h3>
                        <p>Verdiğiniz cevaplara göre size en uygun çözümü belirledik</p>
                    </div>
                    
                    <div class="recommended-solution" id="recommended-solution">
                        <!-- Bu alan JavaScript ile doldurulacak -->
                    </div>
                    
                    <div class="alternative-options">
                        <h4>🔄 Başka seçenekler de görmek ister misiniz?</h4>
                        <div class="website-types-grid">
                            <div class="website-type-option" data-type="corporate">
                                <span class="type-icon">🏢</span>
                                <div class="type-content">
                                    <span class="type-name">Kurumsal Website</span>
                                    <span class="type-description">İşinizi tanıtan, güven veren profesyonel site</span>
                                </div>
                            </div>
                            <div class="website-type-option" data-type="ecommerce">
                                <span class="type-icon">🛒</span>
                                <div class="type-content">
                                    <span class="type-name">E-Ticaret Sitesi</span>
                                    <span class="type-description">Online mağaza, ürün satışı, ödeme sistemi</span>
                                </div>
                            </div>
                            <div class="website-type-option" data-type="blog">
                                <span class="type-icon">📝</span>
                                <div class="type-content">
                                    <span class="type-name">Blog/İçerik Sitesi</span>
                                    <span class="type-description">Makale, haber, içerik paylaşım sitesi</span>
                                </div>
                            </div>
                            <div class="website-type-option" data-type="landing">
                                <span class="type-icon">🎯</span>
                                <div class="type-content">
                                    <span class="type-name">Özel Kampanya Sayfası</span>
                                    <span class="type-description">Tek ürün/hizmet için özel tasarım</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="step-3-error" class="error-message hidden"></div>
            </div>

            <!-- Step 4: Content & Size -->
            <div class="step-content hidden" id="step-4">
                <div class="form-group">
                    <label class="form-label">Web sitenizde hangi sayfalar olsun?</label>
                    <div class="content-planning">
                        <div class="page-categories">
                            <div class="page-category">
                                <h4>📄 Temel Sayfalar (Hepsinde olmalı)</h4>
                                <div class="page-list basic-pages">
                                    <div class="page-item checked">
                                        <span class="page-name">Ana Sayfa</span>
                                        <span class="page-desc">Sitenizin vitrin sayfası</span>
                                    </div>
                                    <div class="page-item checked">
                                        <span class="page-name">Hakkımızda</span>
                                        <span class="page-desc">İşinizi tanıtan sayfa</span>
                                    </div>
                                    <div class="page-item checked">
                                        <span class="page-name">İletişim</span>
                                        <span class="page-desc">Adres, telefon, harita</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="page-category">
                                <h4>🛍️ İş Sayfaları</h4>
                                <div class="page-list business-pages">
                                    <label class="page-item selectable">
                                        <input type="checkbox" name="pages" value="services">
                                        <span class="page-content">
                                            <span class="page-name">Hizmetlerimiz/Ürünlerimiz</span>
                                            <span class="page-desc">Ne sattığınızı gösteren sayfa</span>
                                        </span>
                                    </label>
                                    <label class="page-item selectable">
                                        <input type="checkbox" name="pages" value="gallery">
                                        <span class="page-content">
                                            <span class="page-name">Galeri/Portföy</span>
                                            <span class="page-desc">Çalışmalarınızın fotoğrafları</span>
                                        </span>
                                    </label>
                                    <label class="page-item selectable">
                                        <input type="checkbox" name="pages" value="team">
                                        <span class="page-content">
                                            <span class="page-name">Ekibimiz</span>
                                            <span class="page-desc">Çalışanlarınızı tanıtan sayfa</span>
                                        </span>
                                    </label>
                                    <label class="page-item selectable">
                                        <input type="checkbox" name="pages" value="testimonials">
                                        <span class="page-content">
                                            <span class="page-name">Müşteri Yorumları</span>
                                            <span class="page-desc">Memnun müşteri görüşleri</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="page-category">
                                <h4>📝 İçerik Sayfaları</h4>
                                <div class="page-list content-pages">
                                    <label class="page-item selectable">
                                        <input type="checkbox" name="pages" value="blog">
                                        <span class="page-content">
                                            <span class="page-name">Blog/Haberler</span>
                                            <span class="page-desc">Düzenli içerik paylaşımı</span>
                                        </span>
                                    </label>
                                    <label class="page-item selectable">
                                        <input type="checkbox" name="pages" value="faq">
                                        <span class="page-content">
                                            <span class="page-name">Sık Sorulan Sorular</span>
                                            <span class="page-desc">Müşteri sorularının cevapları</span>
                                        </span>
                                    </label>
                                    <label class="page-item selectable">
                                        <input type="checkbox" name="pages" value="career">
                                        <span class="page-content">
                                            <span class="page-name">Kariyer/İş İlanları</span>
                                            <span class="page-desc">Personel alım sayfası</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="page-counter">
                            <div class="counter-display">
                                <span class="counter-label">Toplam Sayfa Sayısı:</span>
                                <span class="counter-value" id="page-count-display">3</span>
                            </div>
                            <div class="counter-note">
                                <small>💡 Daha fazla sayfa = Daha detaylı site = Daha yüksek fiyat</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="step-4-error" class="error-message hidden"></div>
            </div>

            <!-- Step 5: Design & Features -->
            <div class="step-content hidden" id="step-5">
                <div class="form-group">
                    <label class="form-label">Sitenizin görünümü nasıl olsun?</label>
                    <div class="design-approach">
                        <div class="design-option">
                            <input type="radio" name="design" value="basic" id="design-basic">
                            <label for="design-basic">
                                <div class="design-preview">
                                    <div class="preview-mockup basic-mockup">
                                        <div class="mockup-header"></div>
                                        <div class="mockup-content">
                                            <div class="mockup-text"></div>
                                            <div class="mockup-text short"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="design-info">
                                    <h4>💼 Profesyonel & Sade</h4>
                                    <p>Temiz, düzenli, işinize odaklı tasarım</p>
                                    <div class="design-features">
                                        <span>✓ Hızlı hazırlanır</span>
                                        <span>✓ Mobil uyumlu</span>
                                        <span>✓ SEO dostu</span>
                                    </div>
                                    <div class="design-price">Ek ücret yok</div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="design-option">
                            <input type="radio" name="design" value="custom" id="design-custom">
                            <label for="design-custom">
                                <div class="design-preview">
                                    <div class="preview-mockup custom-mockup">
                                        <div class="mockup-header gradient"></div>
                                        <div class="mockup-content">
                                            <div class="mockup-image"></div>
                                            <div class="mockup-text"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="design-info">
                                    <h4>🎨 Markanıza Özel</h4>
                                    <p>Logonuza, renklerinize uygun özel tasarım</p>
                                    <div class="design-features">
                                        <span>✓ Marka kimliği</span>
                                        <span>✓ Özel renkler</span>
                                        <span>✓ Benzersiz görünüm</span>
                                    </div>
                                    <div class="design-price">+%50 ek ücret</div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="design-option">
                            <input type="radio" name="design" value="premium" id="design-premium">
                            <label for="design-premium">
                                <div class="design-preview">
                                    <div class="preview-mockup premium-mockup">
                                        <div class="mockup-header animated"></div>
                                        <div class="mockup-content">
                                            <div class="mockup-slider"></div>
                                            <div class="mockup-cards">
                                                <div class="mockup-card"></div>
                                                <div class="mockup-card"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="design-info">
                                    <h4>✨ Lüks & Etkileyici</h4>
                                    <p>Animasyonlu, modern, göz alıcı tasarım</p>
                                    <div class="design-features">
                                        <span>✓ Animasyonlar</span>
                                        <span>✓ İnteraktif öğeler</span>
                                        <span>✓ Premium görünüm</span>
                                    </div>
                                    <div class="design-price">+%100 ek ücret</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Hangi ek özellikler olsun? (İsteğe bağlı)</label>
                    <div class="features-grid">
                        <label class="feature-card">
                            <input type="checkbox" id="seo" value="seo">
                            <div class="feature-content">
                                <div class="feature-icon">🔍</div>
                                <div class="feature-info">
                                    <h4>Google'da Çıkmak</h4>
                                    <p>SEO optimizasyonu ile Google'da üst sıralarda görünün</p>
                                    <div class="feature-benefit">📈 Daha fazla müşteri</div>
                                </div>
                                <div class="feature-price">+3,000 ₺</div>
                            </div>
                        </label>
                        
                        <label class="feature-card">
                            <input type="checkbox" id="cms" value="cms">
                            <div class="feature-content">
                                <div class="feature-icon">✏️</div>
                                <div class="feature-info">
                                    <h4>Kendiniz Güncelleyebilme</h4>
                                    <p>İçerikleri kendiniz kolayca değiştirebilirsiniz</p>
                                    <div class="feature-benefit">💰 Güncelleme ücreti yok</div>
                                </div>
                                <div class="feature-price">+5,000 ₺</div>
                            </div>
                        </label>
                        
                        <label class="feature-card">
                            <input type="checkbox" id="multilang" value="multilang">
                            <div class="feature-content">
                                <div class="feature-icon">🌍</div>
                                <div class="feature-info">
                                    <h4>Çoklu Dil</h4>
                                    <p>Türkçe, İngilizce gibi farklı dillerde site</p>
                                    <div class="feature-benefit">🌐 Uluslararası müşteri</div>
                                </div>
                                <div class="feature-price">+4,000 ₺</div>
                            </div>
                        </label>
                        
                        <label class="feature-card">
                            <input type="checkbox" id="payment" value="payment">
                            <div class="feature-content">
                                <div class="feature-icon">💳</div>
                                <div class="feature-info">
                                    <h4>Online Ödeme</h4>
                                    <p>Kredi kartı ile ödeme alma sistemi</p>
                                    <div class="feature-benefit">💰 Anında ödeme</div>
                                </div>
                                <div class="feature-price">+6,000 ₺</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div id="step-5-error" class="error-message hidden"></div>
            </div>

            <!-- Step 6: Contact Information -->
            <div class="step-content hidden" id="step-6">
                <div class="final-step-header">
                    <h3>🎯 Son Adım: İletişim Bilgileriniz</h3>
                    <p>Kişiselleştirilmiş fiyat teklifinizi hazırlayalım</p>
                </div>
                
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
                        <label for="company">İşletme Adı</label>
                        <input type="text" id="company" placeholder="İşletmenizin adı (opsiyonel)">
                    </div>
                    <div class="form-group">
                        <label for="city">Şehir</label>
                        <input type="text" id="city" placeholder="Hangi şehirdesiniz? (opsiyonel)">
                    </div>
                </div>
                
                <div class="privacy-notice">
                    <p><strong>🔒 Gizlilik:</strong> Bilgileriniz sadece size özel teklif hazırlamak için kullanılır. Üçüncü kişilerle paylaşılmaz.</p>
                </div>
                
                <div id="step-6-error" class="error-message hidden"></div>
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
                <h2>🎉 Kişisel Teklifiniz Hazır!</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="price-result">
                    <div class="price-summary" id="price-summary">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                    
                    <div class="price-range">
                        <span class="price-label">Size Özel Fiyat Aralığı:</span>
                        <span class="price-value" id="price-range"></span>
                    </div>
                    
                    <div class="price-breakdown" id="price-breakdown">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                    
                    <div class="price-note">
                        <strong>📋 Önemli:</strong> Bu fiyat, verdiğiniz bilgilere göre hazırlanmış kişisel teklifinizdir. Detaylı görüşmede kesinleştirilecektir.
                    </div>
                    
                    <button id="book-appointment-btn" class="btn btn-primary btn-large">
                        🎯 Ön Görüşme İçin Randevu Al
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
                    
                    <div class="consultation-info">
                        <div class="consultation-benefits">
                            <h4>🎯 Bu görüşmede neler konuşacağız?</h4>
                            <ul>
                                <li>✅ Projenizin detaylarını analiz edeceğiz</li>
                                <li>✅ Size en uygun çözümü belirleyeceğiz</li>
                                <li>✅ Kesin fiyat teklifini vereceğiz</li>
                                <li>✅ Tüm sorularınızı cevaplayacağız</li>
                                <li>✅ Proje takvimini planlayacağız</li>
                            </ul>
                            <div class="consultation-duration">
                                <strong>⏱️ Süre:</strong> Yaklaşık 30-45 dakika
                            </div>
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
                        💳 Ödemeye Geç ve Randevuyu Onayla
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
