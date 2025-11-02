<?php

namespace App\Facades;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @author Reymon Zakhary
 * @method static self from(string $driver)
 * @method static Builder|Model|object|null  managerLanguage(?string $fallback = null)
 * @method static Builder|Model|object|null  enableBlueprints(?string $fallback = null)
 * @method static Builder|Model|object|null  mailSmtpHosts(?string $fallback = null)
 * @method static Builder|Model|object|null  mailSmtpPort(?string $fallback = null)
 * @method static Builder|Model|object|null  mailSmtpUser(?string $fallback = null)
 * @method static Builder|Model|object|null  mailSmtpPass(?string $fallback = null)
 * @method static Builder|Model|object|null  mailSmtpPrefix(?string $fallback = null)
 * @method static Builder|Model|object|null  mailSmtpFromName(?string $fallback = null)
 * @method static Builder|Model|object|null  mailSmtpFrom(?string $fallback = null)
 * @method static Builder|Model|object|null  vat(?string $fallback = null)
 * @method static Builder|Model|object|null  useTeamAddress(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationExpiresAfter(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationLogoFullDocumentWidth(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationLogoPosition(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationLogoWidth(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationCustomerAddressPositionDirection(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationCustomerAddressPosition(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationFont(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationFontSize(?string $fallback = null)
 * @method static Builder|Model|object|null  quotationBackground(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceQrCode(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceOwnLetterhead(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceLogoWidth(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceLogoPosition(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceLogoFullDocumentWidth(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceLogo(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceLetterheadSize(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceFontSize(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceFont(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceCustomerAddressPositionDirection(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceCustomerAddressPosition(?string $fallback = null)
 * @method static Builder|Model|object|null  invoiceBackground(?string $fallback = null)
 * @method static Builder|Model|object|null  currency(?string $fallback = null)
 *
 * @see \App\Foundation\Settings\Setting;
 */
class Settings extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'settings';
    }

}
