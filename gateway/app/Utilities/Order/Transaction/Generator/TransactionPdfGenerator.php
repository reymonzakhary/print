<?php

declare(strict_types=1);

namespace App\Utilities\Order\Transaction\Generator;

use App\Facades\Settings;
use App\Models\Tenants\Media\FileManager;
use App\Models\Tenants\Transaction;
use Barryvdh\DomPDF\PDF as DomPdf;
use Exception;
use Illuminate\Filesystem\FilesystemManager;
use Psr\Log\LoggerInterface;

final readonly class TransactionPdfGenerator
{
    private const string PDF_BLADE_TEMPLATE = 'pdf.invoice.transaction';

    public function __construct(
        private Dompdf          $dompdf,
        private LoggerInterface $logger,
        private FileSystemManager $fileSystemManager
    ) {
    }

    /**
     * @param Transaction $transaction
     *
     * @return DomPdf
     */
    public function generate(Transaction $transaction): DomPdf
    {
        return $this->dompdf->loadView(self::PDF_BLADE_TEMPLATE, [
            'transaction' => $transaction,
            'supplierData' => tenantCustomFields()->toArray(),
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * @return array
     */
    private function getSettings(): array
    {
        return [
            'invoice_qr_code' => Settings::invoiceQrCode()?->value,
            'invoice_own_letterhead' => Settings::invoiceOwnLetterhead()?->value,
            'invoice_logo_width' => Settings::invoiceLogoWidth()?->value,
            'invoice_logo_position' => Settings::invoiceLogoPosition()?->value,
            'invoice_logo_full_document_width' => Settings::invoiceLogoFullDocumentWidth()?->value,
            'invoice_letterhead_size' => Settings::invoiceLetterheadSize()?->value,
            'invoice_font_size' => Settings::invoiceFontSize()?->value,
            'invoice_font' => Settings::invoiceFont()?->value,
            'invoice_customer_address_position_direction' => Settings::invoiceCustomerAddressPositionDirection()?->value,
            'invoice_customer_address_position' => Settings::invoiceCustomerAddressPosition()?->value,

            'invoice_logo' => (function (): ?string {
                $value = Settings::invoiceLogo()?->getAttribute('value');
                return $this->generateUrlForImage($value);
            })(),

            'invoice_background' => (function (): ?string {
                $value = Settings::invoiceBackground()?->getAttribute('value');
                return $this->generateUrlForImage($value);
            })()
        ];
    }

    /**
     * Generate a public URL for a given image by its database-ID
     *
     * @param int|string $imageId
     *
     * @return string|null
     */
    private function generateUrlForImage(
        int|string|null $imageId
    ): ?string
    {
        try {
            $imageFileEntryInDb = FileManager::query()->findOrFail($imageId);

            /* @var FileManager $imageFileEntryInDb */

            [$imageFileDiskName, $imageFileFullPath] = [
                $imageFileEntryInDb->getAttribute('disk'),
                sprintf('%s/%s', tenant()->uuid, $imageFileEntryInDb->getFullyQualifiedPath())
            ];

            return $this->fileSystemManager->disk($imageFileDiskName)->url($imageFileFullPath);
        } catch (Exception $e) {
            $this->logger->warning("This Image Is Not Exist Anymore", [
                'tenant_uuid' => tenant()->uuid,
                'exception_message' => $e->getMessage(),
                'image_id' => $imageId,
                'caller' => get_class($this),
            ]);

            return null;
        }
    }
}
