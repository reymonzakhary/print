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
    'salutation' => 'Dear ',
    'welcome_to' => 'Welcome to ',
    'regards' => 'Kind regards, ',
    'domain' => 'Domain',
    'email' => 'Email',
    'password' => 'Password',
    'name' => 'Name',
    'description' => 'Description',
    'company_name' => 'Company name',

    // Supplier Invitation Mail
    'received_invitation' => 'You received an invitation to the Prindustry manager!',
    'received_because' => 'You received this invitation because ',
    'wants_supplier' => 'wants you to be their supplier. ',
    'if_accept' => 'If you accept this invitation you will be able to receive orders and quotations from ',
    'want_to_produce' => 'with products they want you to produce. ',
    'work_with_them' => 'Would you like to work with them? ',
    'accept' => 'Accept ',
    'reject' => 'Reject ',

    // Supplier Credentials Mail
    'been_invited' => 'You have been invited to the Prindustry manager ',
    'invitation_details' => '(There should be a seperate mail in your mailbox with more details about an invitation from one of Prindustry\'s customers) ',
    'credentials_heading' => 'The credentials to enter the Prindustry manager: ',

    // Supplier Invitation Reaction Mail
    'invitation_reaction' => 'You received a response to your invitation',
    'has_reacted_to_invitation' => 'has replied to your invitation to work with you.',
    'invitation' => 'Invitation ',
    'invitation_replied_subject' => ':company replied to your invitation - :status',
    'invitation_subject' => ':company invited you to collaborate',
    'go_to_dashboard' => 'You can find more information in your Prindustry Manager.',

    // Supplier Rejected Invitation Mail
    'rejected_invitation' => 'Invitation rejected.',
    'rejected_invitation_message' => 'You have rejected the invitation to work with :company.',

    // Quotation Declined Mail
    'quotation_update' => 'Quotation update in your Prindustry manager! ',
    'quotation_response' => 'Quotation Response. ',
    'quotation_offer_accepted' => 'Quotation: :quotationId has been accepted by the customer: :customerEmail. ',
    'quotation_offer_rejected' => 'Quotation: :quotationId has been rejected by the customer: :customerEmail. ',
    'quotation_offer_details_link' => 'Quotation Details ',

    // Requested Credentials Mail
    'requested_credentials' => 'You requested your credentials, which you can find below. ',
    'not_created_account' => 'If you did not create an account, no further action is required. ',

    // Send Quotation Mail
    'new_quotation_in_manager' => 'New quotation in your Prindustry manager! ',
    'new_quotation' => 'Your have received a new quotation from ',

    // quotation updated mail
    'quotation_updated' => 'You have an update on quotation ',

    // password resets
    'conversation1' => "Having trouble signing in? No problem!<br>Resetting your password is quick and easy.<br>Simply use the code below and follow the instructions on the password reset page: <br>We'll have you up and running in no time.",
    'conversation2' => 'If you did not make this request then please ignore this email.',
    'reset_your_password' => 'Reset your password!',
    'password_reset' => 'Password reset',
    // Mail Footer
    'rights' => 'All rights reserved. ',
    'verification' => [
        'email_verification' => 'Email Verification.',
        'title' => 'Verify your email.',
        'conversation' => 'Your email verification code is'
    ],

    'is-already-verified' => [
        'email_verification' => '----------',
        'title' => 'Your account is already verified.',
        'conversation' => '..........'
    ],

    'has-verified-successfully' => [
        'email_verification' => '----------',
        'title' => 'Your account has been verified successfully.',
        'conversation' => '..........'
    ],

    'quotation_external_id' => 'External id',
    'quotation_internal_id' => 'Offer id',
];
