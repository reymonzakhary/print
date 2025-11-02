<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mails Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during the sending of emails for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    // General
    'salutation' => 'Beste ',
    'welcome_to' => 'Welkom bij ',
    'regards' => 'Met vriendelijke groeten, ',
    'domain' => 'Domein',
    'email' => 'E-mail',
    'password' => 'Wachtwoord',
    'name' => 'Naam',
    'description' => 'Omschrijving',
    'company_name' => 'Bedrijfsnaam',

    // Supplier Invitation Mail
    'received_invitation' => 'Je hebt een uitnodiging ontvangen voor de Prindustry manager!',
    'received_because' => 'Je hebt een uitnodiging ontvangen omdat ',
    'wants_supplier' => 'graag wilt dat je hun leverancier wordt. ',
    'if_accept' => 'Als je de uitnodiging accepteert kun je order en offertes ontvangen van ',
    'want_to_produce' => 'met producten waarvan ze graag willen dat je die produceert. ',
    'work_with_them' => 'Wil je met hen samenwerken? ',
    'accept' => 'Accepteer ',
    'reject' => 'Weiger ',

    // Supplier Credentials Mail
    'been_invited' => 'Je bent uitgenodigd voor de Prindustry manager ',
    'invitation_details' => '(Er zou een aparte mail in je inbox moeten zitten met details over de uitnodiging van een van Prindustry\'s klanten) ',
    'credentials_heading' => 'De gegevens waarmee je kunt inloggen in de Prindustry manager: ',

    // Supplier Invitation Reaction Mail
    'invitation_reaction' => 'Je hebt een reactie ontvangen ',
    'has_reacted_to_invitation' => 'heeft gereageerd op je uitnodiging om met je samen te werken.',
    'invitation' => 'Uitnodiging ',
    'invitation_replied_subject' => ':company heeft gereageerd op je uitnodiging - :status',
    'invitation_subject' => ':company heeft je uitgenodigd om samen te werken',
    'go_to_dashboard' => 'Je kunt meer informatie vinden in je Prindustry Manager.',


    // Supplier Rejected Invitation Mail
    'rejected_invitation' => 'Uitnodiging afgewezen.',
    'rejected_invitation_message' => 'Je hebt de uitnodiging om met :company samen te werken afgewezen.',

    // Quotation Declined Mail
    'quotation_update' => 'Offerte update in je Prindustry manager! ',
    'quotation_response' => 'Offerte Reactie. ',
    'quotation_offer_accepted' => 'Offerte: :quotationId is geaccepteerd door de klant: :customerEmail. ',
    'quotation_offer_rejected' => 'Offerte: :quotationId is afgewezen door de klant: :customerEmail. ',
    'quotation_offer_details_link' => 'Offerte Details ',

    // Requested Credentials Mail
    'requested_credentials' => 'Je hebt nieuwe inloggegevens aangevraagd, je vind ze hieronder. ',
    'not_created_account' => 'Als je geen account hebt aangemaakt is er geen verdere actie nodig. ',

    // Send Quotation Mail
    'new_quotation_in_manager' => 'Nieuwe offerte in je Prindustry manager! ',
    'new_quotation' => 'Je hebt een nieuwe offerte ontvangen van ',

    // quotation updated mail
    'quotation_updated' => 'Er is een update op offerte ',

    // password resets
    'conversation1' => "Problemen met aanmelden? Geen probleem!<br>Het opnieuw instellen van je wachtwoord is snel en eenvoudig.<br>Gebruik gewoon de onderstaande code en volg de instructies op de pagina voor wachtwoordherstel:<br>Dan ben je zo weer aan de slag.",
    'conversation2' => 'Als je deze aanvraag niet hebt gedaan, negeer dan deze e-mail.',
    'reset_your_password' => 'Reset je wachtwoord!',
    'password_reset' => 'Wachtwoord reset',
    // Mail Footer
    'rights' => 'Alle rechten voorbehouden.',
    'verification' => [
        'email_verification' => 'E-mail verificatie.',
        'title' => 'Verifieer je e-mail.',
        'conversation' => 'Je e-mail verificatiecode is'
    ],

    'is-already-verified' => [
        'email_verification' => '----------',
        'title' => 'Je account is al geverifieerd.',
        'conversation' => '..........'
    ],

    'has-verified-successfully' => [
        'email_verification' => '----------',
        'title' => 'Je account is succesvol geverifieerd.',
        'conversation' => '..........'
    ],

    'quotation_external_id' => 'Externe id',
    'quotation_internal_id' => 'Offerte id',
];
