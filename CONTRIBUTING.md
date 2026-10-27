# Katkı Kılavuzu (Contributing Guide)

`crm-ticket-system` projesine katkıda bulunmak istediğiniz için teşekkür ederiz! Açık kaynak topluluğunun desteğini memnuniyetle karşılıyoruz.

## Geliştirme Süreci

1. Bu depoyu (repository) fork edin.
2. Yeni bir özellik veya hata düzeltmesi için dal (branch) açın:
   ```bash
   git checkout -b feature/yeni-ozellik
   ```
3. Kodlama standartları:
   - PHP kodları **PSR-12** standartlarına uygun olmalıdır.
   - Yeni özellikler için `tests/` klasörüne PHPUnit testleri eklenmelidir.
4. Testleri çalıştırın:
   ```bash
   composer test
   ```
5. Değişikliklerinizi commit edip GitHub'a push edin ve bir Pull Request (PR) açın.

## Pull Request Süreci
- Her Pull Request otomatik olarak GitHub Actions CI testlerinden geçer.
- Kod incelemesi tamamlandıktan sonra ana dala (`main`) birleştirilir.
