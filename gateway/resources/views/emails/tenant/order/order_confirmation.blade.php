<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Order Confirmation</title>
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
<body style="background-color: #f7f7f7; font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; width: 100%;">
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
                    <td class="body" style="background-color: #ffffff; padding: 0;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 32px; border-radius: 2px;">
                            <tr>
                                <td class="content-cell text-center" >
                                    <h1 style="font-size: 22px; font-weight: bold; color: #333333;">Order Confirmation</h1>
                                    <p style="font-size: 14px; color: #555555;">
                                        Hello {{$order->orderedBy->profile->first_name}} , <br>
                                        Your Order #{{$order->order_nr}} has been updated by the supplier,
                                        <br>
                                        Check The Attachment for full information,
                                        Thank you.
                                    </p>
                                </td>
                            </tr>

                            <!-- Order Details -->


                            <!-- Footer -->
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
        </td>
    </tr>
</table>
</body>
</html>
