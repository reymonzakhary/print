<?php

declare(strict_types=1);

namespace Database\Seeders\Tenants;

use App\Models\Tenant\Lexicon;
use Illuminate\Database\Seeder;

class LexiconSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run(): void
    {

        $lexicons = [
            // ENGLISH
            [
                'name' => 'Quotation Mail subject',
                'template' => 'subject',
                'namespace' => 'mail',
                'area' => 'quotation',
                'language' => 'en',
                'value' => 'Your quote is ready – review, accept or decline',
            ],
            [
                'name' => 'Quotation Mail body',
                'template' => 'body',
                'namespace' => 'mail',
                'area' => 'quotation',
                'language' => 'en',
                'value' => '<p>Dear [[%customer.first_name]],</p><p>Attached you\'ll find the quote&nbsp;[[%quotation.id]], prepared by us.&nbsp;</p><p>Please review the details in the attachment. You can respond directly using the buttons below.</p><p>If you have any questions or feedback, feel free to reach out. We\'re happy to help.&nbsp;</p><p>Best regards,</p>',
            ],

            [
                'name' => 'Order Mail subject',
                'template' => 'subject',
                'namespace' => 'mail',
                'area' => 'order',
                'language' => 'en',
                'value' => 'Your order has been placed',
            ],
            [
                'name' => 'Order Mail body',
                'template' => 'body',
                'namespace' => 'mail',
                'area' => 'order',
                'language' => 'en',
                'value' => '<p>Dear&nbsp;[[%customer.first_name]],</p><p>Your order for [[%quotation.reference]]&nbsp;with ID [[%quotation.id]] has been successfully placed. We\'ll get to work on it immediately.&nbsp;</p><p>You\'ll receive a notification once the order has been processed or shipped.</p><p>If you have any questions, feel free to get in touch.</p><p>Best regards,</p>',
            ],

            [
                'name' => 'Invoice Mail Subject',
                'template' => 'subject',
                'namespace' => 'mail',
                'area' => 'invoice',
                'language' => 'en',
                'value' => 'Your invoice',
            ],
            [
                'name' => 'Invoice Mail Body',
                'template' => 'body',
                'namespace' => 'mail',
                'area' => 'invoice',
                'language' => 'en',
                'value' => '<p>Dear&nbsp;[[%customer.first_name]],</p><p>Please find attached the invoice for [[%quotation.reference]]&nbsp;with ID [[%quotation.id]].</p><p>The invoice is included as a PDF and contains all necessary payment details. We kindly ask you to settle the amount by [[%quotation.expire_at]]. </p><p>If you have any questions about this invoice, feel free to reach out.</p><p>Best regards,</p>',
            ],
            // DUTCH
            [
                'name' => 'Quotation Mail subject',
                'template' => 'subject',
                'namespace' => 'mail',
                'area' => 'quotation',
                'language' => 'nl',
                'value' => 'Uw offerte is klaar – bekijk, accepteer of weiger',
            ],
            [
                'name' => 'Quotation Mail body',
                'template' => 'body',
                'namespace' => 'mail',
                'area' => 'quotation',
                'language' => 'nl',
                'value' => '<p>Beste&nbsp;[[%customer.first_name]],</p><p>Bij deze ontvangt u de offerte met ID [[%quotation.id]] opgesteld door ons.&nbsp;</p><p>In de bijlage vindt u het volledige overzicht van de kosten en details. U kunt hieronder aangeven of u akkoord gaat met de offerte.</p><p>Heeft u vragen of opmerkingen? Laat het gerust weten, we denken graag met u mee.</p><p>Met vriendelijke groet,</p>',
            ],

            [
                'name' => 'Order Mail subject',
                'template' => 'subject',
                'namespace' => 'mail',
                'area' => 'order',
                'language' => 'nl',
                'value' => 'Uw bestelling is geplaatst',
            ],
            [
                'name' => 'Order Mail body',
                'template' => 'body',
                'namespace' => 'mail',
                'area' => 'order',
                'language' => 'nl',
                'value' => '<p>Beste&nbsp;[[%customer.first_name]],</p><p>Uw bestelling voor [[%quotation.reference]]&nbsp;is succesvol geplaatst. Wij gaan direct voor u aan de slag.</p><p>U ontvangt een melding zodra de bestelling is verwerkt of verzonden.&nbsp;</p><p>Heeft u vragen? Laat het ons gerust weten.</p><p>Met vriendelijke groet,</p>',
            ],

            [
                'name' => 'Invoice Mail Subject',
                'template' => 'subject',
                'namespace' => 'mail',
                'area' => 'invoice',
                'language' => 'nl',
                'value' => 'Uw factuur',
            ],
            [
                'name' => 'Invoice Mail Body',
                'template' => 'body',
                'namespace' => 'mail',
                'area' => 'invoice',
                'language' => 'nl',
                'value' => '<p>Beste&nbsp;[[%customer.first_name]],</p><p>Bij deze ontvangt u de factuur voor [[%quotation.reference]].</p><p>De factuur is bijgevoegd als PDF. U vindt hierin alle benodigde betaalgegevens. Wij verzoeken u vriendelijk om het openstaande bedrag voor [[%quotation.expire_at]] te voldoen.</p><p>Heeft u vragen over deze factuur? Neem dan gerust contact met ons op.</p><p>Met vriendelijke groet,</p>',
            ],
        ];

//        $lexicons = [
//            [
//                'name' => 'Quotation Mail subject',
//                'template' => 'quotation.subject',
//                'namespace' => 'mail',
//                'area' => 'quotation',
//                'language' => 'en',
//                'value' => 'Quotation for mr/mrs [[%data.orderedBy.profile.first_name]] [[%data.orderedBy.profile.last_name]]',
//            ],
//            [
//                'name' => 'Quotation Mail body',
//                'template' => 'quotation.body',
//                'namespace' => 'mail',
//                'area' => 'quotation',
//                'language' => 'en',
//                'value' => 'Dear [[%data.orderedBy.profile.first_name]] [[%data.orderedBy.profile.last_name]] /n ',
//            ],
//
//            [
//                'name' => 'Order Mail subject',
//                'template' => 'order.subject',
//                'namespace' => 'mail',
//                'area' => 'order',
//                'language' => 'en',
//                'value' => 'Order for mr/mrs [[%data.orderedBy.profile.first_name]] [[%data.orderedBy.profile.last_name]]',
//            ],
//            [
//                'name' => 'Order Mail body',
//                'template' => 'order.body',
//                'namespace' => 'mail',
//                'area' => 'order',
//                'language' => 'en',
//                'value' => 'Dear [[%data.orderedBy.profile.first_name]] [[%data.orderedBy.profile.last_name]] /n ',
//            ],
//
//            [
//                'name' => 'Invoice Mail Subject',
//                'template' => 'invoice.subject',
//                'namespace' => 'mail',
//                'area' => 'invoice',
//                'language' => 'en',
//                'value' => 'Invoice for mr/mrs [[%customer.full_name]]',
//            ],
//            [
//                'name' => 'Invoice Mail Body',
//                'template' => 'invoice.body',
//                'namespace' => 'mail',
//                'area' => 'invoice',
//                'language' => 'en',
//                'value' => 'Dear [[%customer.full_name]] /n ',
//            ],
//        ];

        if(Lexicon::query()->where('template', 'quotation.subject')->count()) {
            Lexicon::query()->delete();
        }

        collect($lexicons)->each(
            static fn(array $lex) => Lexicon::query()->updateOrCreate($lex)
        );
    }
}
