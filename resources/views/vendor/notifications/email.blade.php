<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
    <title>{{ config('app.name') }}</title>

    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->

    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        @media only screen and (max-width: 600px) {
            .email-content { width: 100% !important; }
            .email-body { padding: 24px 20px !important; }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">

@isset($preheader)
<div style="display:none; font-size:1px; line-height:1px; max-height:0px; max-width:0px; opacity:0; overflow:hidden;">
    {{ $preheader }}
</div>
@endisset

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center" style="padding: 36px 16px;">

            <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0"
                   style="max-width: 560px; width: 100%;
                          border-collapse: separate;
                          border-radius: 12px;
                          overflow: hidden;
                          border: 1px solid;">

                <!-- HEADER -->
                <tr>
                    <td align="left" style="padding: 28px 36px 22px 36px;
                                            background-color: rgba(147, 197, 253, 0.15);
                                            border-bottom: 1px solid;">
                        <span style="font-size: 22px; font-weight: 900; letter-spacing: -0.5px; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">TimeTrack <span style="color: #570DF8;">Pro</span></span>
                        <p style="margin: 8px 0 0 0; font-size: 11px; font-family: 'Segoe UI', Arial, Helvetica, sans-serif; text-transform: uppercase; letter-spacing: 0.08em;">
                            Overtime Tracker System
                        </p>
                    </td>
                </tr>

                <!-- BODY -->
                <tr>
                    <td class="email-body" style="padding: 32px 36px 28px 36px;">

                        @if (!empty($greeting))
                            <h1 style="font-size: 22px; font-weight: 800; margin: 0 0 16px 0; line-height: 1.3; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                {{ $greeting }}
                            </h1>
                        @else
                            @if ($level === 'error')
                                <h1 style="font-size: 22px; font-weight: 800; color: #dc2626; margin: 0 0 16px 0; line-height: 1.3; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                    @lang('Whoops!')
                                </h1>
                            @else
                                <h1 style="font-size: 22px; font-weight: 800; margin: 0 0 16px 0; line-height: 1.3; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                    @lang('Hello!')
                                </h1>
                            @endif
                        @endif

                        @foreach ($introLines as $line)
                            <p style="font-size: 15px; line-height: 1.7; margin: 0 0 12px 0; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                {{ $line }}
                            </p>
                        @endforeach

                        @isset($actionText)
                            <?php
                                $bgColor = match($level) {
                                    'success' => '#16a34a',
                                    'error'   => '#dc2626',
                                    default   => '#570DF8',
                                };
                            ?>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 28px 0;">
                                <tr>
                                    <td align="center">

                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                            href="{{ $actionUrl }}"
                                            style="height:46px; width:220px; v-text-anchor:middle;"
                                            arcsize="17%"
                                            fillcolor="{{ $bgColor }}"
                                            strokecolor="{{ $bgColor }}">
                                            <w:anchorlock/>
                                            <center style="color:#ffffff; font-family:'Segoe UI',Arial,sans-serif; font-size:14px; font-weight:700;">
                                                {{ $actionText }} &rarr;
                                            </center>
                                        </v:roundrect>
                                        <![endif]-->

                                        <!--[if !mso]><!-->
                                        <a href="{{ $actionUrl }}"
                                           style="display: inline-block; background: {{ $bgColor }}; color: #ffffff; font-size: 14px; font-weight: 700; text-decoration: none; padding: 13px 32px; border-radius: 8px; font-family: 'Segoe UI', Arial, Helvetica, sans-serif; letter-spacing: 0.02em; box-shadow: 0 4px 14px rgba(87,13,248,0.25); mso-hide: all;">
                                            {{ $actionText }} &rarr;
                                        </a>
                                        <!--<![endif]-->

                                    </td>
                                </tr>
                            </table>
                        @endisset

                        @foreach ($outroLines as $line)
                            <p style="font-size: 15px; line-height: 1.7; margin: 0 0 12px 0; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                {{ $line }}
                            </p>
                        @endforeach

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 28px; border-top: 1px solid; padding-top: 20px;">
                            <tr>
                                <td>

                        @if (!empty($salutation))
                            <p style="font-size: 14px; margin: 0; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                {{ $salutation }}
                            </p>
                        @else
                            <p style="font-size: 14px; margin: 0 0 2px 0; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                @lang('Regards,')
                            </p>
                            <p style="font-size: 15px; font-weight: 700; color: #570DF8; margin: 0; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                {{ config('app.name') }}
                            </p>
                        @endif

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td style="border-top: 1px solid; padding: 20px 36px; background-color: rgba(147, 197, 253, 0.15);">

                        @isset($actionText)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
                                <tr>
                                    <td style="padding: 12px 14px;
                                                border: 1px solid;
                                                border-radius: 8px;">
                                        <p style="font-size: 11px; margin: 0 0 5px 0; line-height: 1.6; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                            @lang("If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your web browser:", ['actionText' => $actionText])
                                        </p>
                                        <p style="font-size: 11px; word-break: break-all; overflow-wrap: break-word; margin: 0; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                                            <a href="{{ $actionUrl }}" style="color: #570DF8; text-decoration: none;">{{ $displayableActionUrl }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        @endisset

                        <p style="font-size: 11px; text-align: center; margin: 0; font-family: 'Segoe UI', Arial, Helvetica, sans-serif;">
                            &copy; {{ date('Y') }} TimeTrack Pro &mdash; Overtime Tracker System. All rights reserved.
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
