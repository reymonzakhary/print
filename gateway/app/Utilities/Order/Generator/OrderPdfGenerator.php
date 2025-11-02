<?php

declare(strict_types=1);

namespace App\Utilities\Order\Generator;

use App\Facades\Settings;
use App\Models\Tenants\Media\FileManager;
use App\Models\Tenants\Order;
use App\Models\Tenants\Setting;
use Barryvdh\DomPDF\PDF as DomPdf;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Filesystem\FilesystemManager;
use Psr\Log\LoggerInterface;

final readonly class OrderPdfGenerator
{
    private const string PDF_BLADE_TEMPLATE = 'pdf.order.order-pdf';

    public function __construct(
        private Dompdf          $dompdf,
        private LoggerInterface $logger,
        private FileSystemManager $fileSystemManager
    ) {
    }

    /**
     * @param Order $order
     * @param Authenticatable $user
     * @param bool $hideExpirationMessage
     *
     * @return DomPdf
     */
    public function generate(
        Order $order,
        Authenticatable $user,
        bool $hideExpirationMessage = false
    ): DomPdf
    {
        return $this->dompdf->loadView(self::PDF_BLADE_TEMPLATE, [
            'order' => $order,
            'user' => $user,
            'settings' => $this->getGeneralSettings(),
            'supplierData' => tenantCustomFields()->toArray(),
            'hideExpirationMessage' => $hideExpirationMessage,
        ]);
    }

    /**
     * @return array
     */
    private function getGeneralSettings(): array
    {
        return [
            'logo_full_width' => (bool)Settings::quotationLogoFullDocumentWidth()->value,
            'logo_position' => Settings::quotationLogoPosition()->value,
            'logo_width' => Settings::quotationLogoWidth()->value,
            'customer_address_position_direction' => Settings::quotationCustomerAddressPositionDirection()->value,
            'customer_address_position' => Settings::quotationCustomerAddressPosition()->value,
            'font' => Settings::quotationFont()->value,
            'font_size' => Settings::quotationFontSize()->value,

            'logo' => (function (): ?string {
                if (!$value = Setting::query()->firstWhere('key', 'quotation_logo')?->getAttribute('value')) {
                    return null;
                }

                return $this->generateUrlForImage($value);
            })(),

            'background' => (function (): ?string {
                if (!$value = Settings::quotationBackground()?->getAttribute('value')) {
                    return null;
                }

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
        int|string $imageId
    ): ?string
    {
        try {
            $imageFileEntryInDb = FileManager::query()->find($imageId);
            if (!$imageFileEntryInDb){
                Setting::query()
                    ->where('value', (string) $imageId)
                    ->whereIn('key', ['quotation_logo', 'quotation_background'])
                    ->update(['value' => null]);
                return null;
            }

            /* @var FileManager $imageFileEntryInDb */

            [$imageFileDiskName, $imageFileFullPath] = [
                $imageFileEntryInDb->getAttribute('disk'),
                sprintf('%s/%s', tenant()->uuid, $imageFileEntryInDb->getFullyQualifiedPath())
            ];

            return $this->fileSystemManager->disk($imageFileDiskName)->url($imageFileFullPath);
        } catch (Exception $e) {
            $this->logger->warning("Could not generate a URL for the given image", [
                'tenant_uuid' => tenant()->uuid,
                'exception_message' => $e->getMessage(),
                'image_id' => $imageId,
                'caller' => get_class($this),
            ]);

            return null;
        }
    }
}
