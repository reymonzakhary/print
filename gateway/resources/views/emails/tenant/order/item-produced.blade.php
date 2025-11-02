<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Item Assigned</title>
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body, .footer {
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
<body style="background-color: #ffffff; font-family: sans-serif; line-height: 1.4; margin: 0; padding: 0; width: 100%;">
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="header"
                        style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; padding: 25px 0; text-align: center;">
                        <a href="{{config('app.url')}}"
                           style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; color: #3d4852; font-size: 19px; font-weight: bold; text-decoration: none; display: inline-block;">
                            Prindustry
                        </a>
                    </td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" style="background-color: #edf2f7; padding: 0;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 32px; border-radius: 2px;">
                            <tr>
                                <td class="content-cell">
                                    <h1 style="color: #3d4852; font-size: 18px; font-weight: bold;">
                                        A New Items Have Been Assigned to You from <br> {{$companyName}} <br>
                                        Please Check Them At Order #{{$orderId}}
                                    </h1>

                                </td>
                            </tr>




                            <tr>
                                <td class="content-cell">
                                    <a
                                    @if(config('app.env') === 'production')
                                        href="https://{{$producerDomain }}/orders/{{ $orderId }}"
                                    @else
                                        href="http://{{$producerDomain}}:3000/orders/{{ $orderId }}"
                                    @endif
                                       style="display: inline-block; padding: 10px 20px; background-color: #3869D4; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: bold;">
                                        View Order #{{ $orderId }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="content-cell" align="center"
                                    style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; max-width: 100vw; padding: 32px;">
                                    <p
                                            style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; line-height: 1.5em; margin-top: 0; color: #b0adc5; font-size: 12px; text-align: center;">
                                        © {{ now()->year }} Prindustry. {{__('mails.rights')}}</p>

                                </td>
                            </tr>

                        </table>
                    </td>

                </tr>

                <tr>
                    <td>
                        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" style="margin: 0 auto; padding: 32px; text-align: center;">
                            <tr>
                                <td class="content-cell">
                                    <p style="color: #b0adc5; font-size: 12px;">© {{ now()->year }} {{ config('app.name') }}. All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
