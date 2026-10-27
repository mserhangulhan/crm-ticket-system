# 🎫 Open Source CRM Ticket System

[![CI Test & Lint](https://github.com/mserhangulhan/crm-ticket-system/actions/workflows/ci.yml/badge.svg)](https://github.com/mserhangulhan/crm-ticket-system/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4.svg)](https://www.php.net/)

Modern, hafif ve modüler açık kaynak **CRM Destek & Bilet (Ticket) Yönetim Sistemi**.

Bu proje, müşteri taleplerini, destek biletlerini ve teknik arıza bildirimlerini yönetmek için geliştirilmiş, RESTful API mimarisine sahip açık kaynaklı bir çözümdür.

---

## 🌟 Öne Çıkan Özellikler

- **RESTful Ticket API:** Kolay entegre edilebilir JSON tabanlı bilet oluşturma, sorgulama ve durum güncelleme.
- **Veritabanı Esnekliği:** Geliştirme için SQLite, üretim için MySQL/PostgreSQL uyumlu PDO mimarisi.
- **Modüler & Temiz Mimari:** PSR-12 ve PSR-4 standartlarına tam uyumlu nesne yönelimli PHP tasarımı.
- **Otomatik CI/CD:** GitHub Actions testleri ve Dependabot bağımlılık güncellemeleri.

---

## 🚀 Başlangıç

### Gereksinimler
- PHP 8.0 veya üzeri (PDO ve Curl eklentileriyle)
- Composer
- Git

### Kurulum

```bash
# 1. Depoyu klonlayın
git clone https://github.com/mserhangulhan/crm-ticket-system.git
cd crm-ticket-system

# 2. Bağımlılıkları yükleyin
composer install

# 3. Ortam dosyasını yapılandırın
cp .env.example .env

# 4. Yerel sunucuyu başlatın
php -S localhost:8000 -t public
```

---

## 🛠️ API Kullanımı

### Yeni Bilet (Ticket) Açma
```bash
curl -X POST http://localhost:8000/tickets \
  -H "Content-Type: application/json" \
  -d '{"title": "Giriş Hatası", "description": "Kullanıcı paneline erişilemiyor", "priority": "high", "customer_email": "musteri@ornek.com"}'
```

### Biletleri Listeleme
```bash
curl http://localhost:8000/tickets
```

---

## 🧪 Testler

```bash
vendor/bin/phpunit
# veya
composer test
```

---

## 🤝 Katkıda Bulunma

Katkı kuralları, stil standartları (PSR-12) ve PR akışı için lütfen [CONTRIBUTING.md](CONTRIBUTING.md) dosyasını inceleyiniz.

---

## 📑 Lisans

Bu proje **MIT Lisansı** ile lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakabilirsiniz.

---

## 📧 İletişim

- **Geliştirici:** Murat Serhan Gülhan (EgeLeon Bilişim)
- **E-posta:** iletisim@egeleon.com
