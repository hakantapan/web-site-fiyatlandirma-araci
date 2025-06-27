# Morpheo Web Sitesi Fiyatlandırma Aracı

WordPress eklentisi olarak geliştirilmiş, web sitesi projelerinin fiyatlandırılması ve randevu rezervasyonu için gelişmiş hesaplama aracı.

## Özellikler

- **Akıllı Fiyatlandırma**: Proje türü, sayfa sayısı, özellikler ve tasarım karmaşıklığına göre otomatik fiyat hesaplama
- **Randevu Sistemi**: Entegre randevu rezervasyon sistemi
- **Ödeme Entegrasyonu**: WooCommerce ile ödeme takibi
- **E-posta Bildirimleri**: Otomatik onay ve hatırlatma e-postaları
- **WhatsApp Entegrasyonu**: WhatsApp Business API ile mesaj gönderimi
- **Ödeme Takibi**: Gerçek zamanlı ödeme durumu kontrolü
- **Admin Paneli**: Kapsamlı yönetim ve raporlama

## Kurulum

1. Eklenti dosyalarını `/wp-content/plugins/morpheo-calculator/` dizinine yükleyin
2. WordPress admin panelinden eklentiyi etkinleştirin
3. `Morpheo Calculator` menüsünden ayarları yapılandırın

## Kullanım

### Shortcode
Hesaplama aracını herhangi bir sayfada göstermek için:
\`\`\`
[morpheo_calculator]
\`\`\`

### Admin Ayarları
- **WooCommerce URL**: Ödeme sayfasının URL'si
- **WhatsApp Entegrasyonu**: WhatsApp Business API ayarları
- **E-posta Ayarları**: SMTP ve e-posta şablonları

## Teknik Detaylar

### Veritabanı Tabloları
- `wp_morpheo_calculator_results`: Hesaplama sonuçları
- `wp_morpheo_calculator_appointments`: Randevu kayıtları

### Cron Jobs
- **Ödeme Hatırlatma**: Saatlik çalışır
- **Ödeme Kontrolü**: 10 dakikada bir çalışır

### API Entegrasyonları
- WooCommerce ödeme sistemi
- WhatsApp Business API
- E-posta SMTP

## Sürüm Geçmişi

### v2.2.4
- Ödeme linklerinde tüm parametrelerin eklenmesi
- Payment API hata düzeltmeleri
- Admin paneli iyileştirmeleri

### v2.2.3
- WhatsApp entegrasyonu iyileştirmeleri
- E-posta şablonları güncelleme
- Ödeme URL parametreleri

### v2.2.2
- Ödeme takip sistemi
- Otomatik ödeme kontrolü
- Admin dashboard

## Destek

Teknik destek için: info@morpheodijital.com

## Lisans

Bu eklenti Morpheo Dijital tarafından geliştirilmiştir.
