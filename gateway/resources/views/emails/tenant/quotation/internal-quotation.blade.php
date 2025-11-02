<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body,
            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="
    margin:0;
    padding:0;
    width:100% !important;
    height:100%;
    background-color: {{ $mail_bg_color }};
    font-family: {{ $mail_fonts_family }};
    font-size: {{ $mail_fonts_size }};
    color: {{ $mail_fonts_color }};
    line-height:1.5;
    -webkit-text-size-adjust:none;
">

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: {{ $mail_bg_color }}; margin:0; padding:0;">
    <tr>
        <td align="center">

            <!-- Main content wrapper -->
            <table width="{{ $mail_content_width }}" cellpadding="0" cellspacing="0" role="presentation"
                style="
                    background-color: {{ $mail_content_bg_color }};
                    border: {{ $mail_content_b_width }} solid {{ $mail_content_b_color }};
                    {{ $mail_content_b_radius }};
                    {{ $mail_content_padding }};
                    margin:0 auto;
                    text-align: {{ $mail_content_card_alignment }};
                ">

                {{-- HEADER --}}

                @if($mail_logo_position === 'header' && isset($image_url) && !empty($image_url))
                    <table role="presentation" width="100%">
                        <tr>
                            <td align="{{ $mail_align_logo }}"> 
                                <img src="{{ $image_url }}" 
                                    width="{{ $mail_logo_width }}" 
                                    alt="Logo" 
                                    style="display:block;">
                            </td>
                        </tr>
                    </table>
                @else
                    <tr>
                        <td align="{{ $mail_header_alignment }}" style="{{ $mail_header_padding }}; background-color: {{ $mail_header_bg_color }};">
                            <a href="{{ url('/') }}" style="color:#3d4852; font-size:19px; font-weight:bold; text-decoration:none;">
                                {{ config('app.name') }}
                            </a>
                        </td>
                    </tr>
                @endif

                {{-- BODY --}}
                <tr>
                    <td style="text-align: {{ $mail_content_alignment }};">
                        {{-- LOGO in content if needed --}}

                        @if($mail_logo_position === 'content' && isset($image_url) && !empty($image_url))
                            <table role="presentation" width="100%">
                                <tr>
                                    <td align="{{ $mail_align_logo }}"> 
                                        <img src="{{ $image_url }}" 
                                            width="{{ $mail_logo_width }}" 
                                            alt="Logo" 
                                            style="display:block;">
                                    </td>
                                </tr>
                            </table>
                        @endif
                        <p><strong>{!! optional($mailQueue->message)['greeting'] !!}</strong></p>
                        <p>{!! optional($mailQueue->message)['message'] !!}</p>

                        <table role="presentation" style="margin:20px 0; width:100%;">
                            <tr>
                                <td style="text-align: {{ $mail_button_alignment }};">

                                    <!-- Primary Button -->
                                    <a href="{{ $accept_url }}" class="button" style="
                                        display:inline-block;
                                        {{ $mail_button_padding }};
                                        {{ $mail_button_border_radius }};
                                        background-color: {{ $mail_button_primary_colors }};
                                        color: {{ $mail_text_button_primary_colors }};
                                        text-decoration:none;
                                        font-weight:bold;
                                    ">
                                        {{ __('accept') }}
                                    </a>

                                    <!-- Secondary Button -->
                                    <a href="{{ $reject_url }}" class="button" style="
                                        display:inline-block;
                                        margin-left:10px;
                                        {{ $mail_button_padding }};
                                        {{ $mail_button_border_radius }};
                                        background-color: {{ $mail_button_secondary_colors }};
                                        color: {{ $mail_text_button_secondary_colors }};
                                        text-decoration:none;
                                        font-weight:bold;
                                    ">
                                        {{ __('reject') }}
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p>{!! optional($mailQueue->message)['regards'] !!}</p>
                    </td>
                </tr>


                {{-- FOOTER --}}
                <tr>
                    <td style="
                        {{ $mail_footer_padding }};
                        background-color: {{ $mail_footer_bg_color }};
                        text-align: {{ $mail_footer_alignment }};
                        font-family: {{ $mail_footer_fonts_family }};
                        font-size: {{ $mail_footer_fonts_size }};
                        color: {{ $mail_footer_fonts_color }};
                    ">
                        {{-- LOGO in footer if needed --}}

                        @if($mail_logo_position === 'footer' && isset($image_url) && !empty($image_url))
                            <table role="presentation" width="100%">
                                <tr>
                                    <td align="{{ $mail_align_logo }}"> 
                                        <img src="{{ $image_url }}" 
                                            width="{{ $mail_logo_width }}" 
                                            alt="Logo" 
                                            style="display:block;">
                                    </td>
                                </tr>
                            </table>

                        @endif
                        <p style="margin:0;">
                            © {{ now()->year }} {{ config('app.name') }}. {{ __('mails.rights') }}
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
