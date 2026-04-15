<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Segoe UI', Arial, sans-serif;">
    <tr>
        <td align="center" style="padding: 32px 0 24px 0;">
            <!-- Logo mark + wordmark -->
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td style="vertical-align: middle; padding-right: 10px;">
                        <div style="
                            width: 36px;
                            height: 36px;
                            background: #570DF8;
                            border-radius: 8px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            line-height: 36px;
                            text-align: center;
                        ">
                            <!-- Clock SVG icon -->
                            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0ibm9uZSIgdmlld0JveD0iMCAwIDI0IDI0IiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiPjxjaXJjbGUgY3g9IjEyIiBjeT0iMTIiIHI9IjEwIi8+PHBhdGggc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBkPSJNMTIgNnY2bDQgMiIvPjwvc3ZnPg==" width="20" height="20" alt="clock" style="display:block; margin: 8px auto;" />
                        </div>
                    </td>
                    <td style="vertical-align: middle;">
                        <span style="font-size: 22px; font-weight: 900; color: #1d232a; letter-spacing: -0.5px;">TimeTrack <span style="color: #570DF8;">Pro</span></span>
                    </td>
                </tr>
            </table>
            <p style="margin: 6px 0 0 0; font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 500;">Overtime Tracker System</p>
        </td>
    </tr>
</table>
</x-mail::header>
</x-slot:header>

{{-- Body --}}
<table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Segoe UI', Arial, sans-serif;">
    <tr>
        <td style="padding: 8px 0 4px 0;">

            {{-- Greeting --}}
            @if (! empty($greeting))
            <h1 style="font-size: 22px; font-weight: 800; color: #1d232a; margin: 0 0 16px 0; line-height: 1.3;">{{ $greeting }}</h1>
            @else
                @if ($level === 'error')
                <h1 style="font-size: 22px; font-weight: 800; color: #e11d48; margin: 0 0 16px 0; line-height: 1.3;">@lang('Whoops!')</h1>
                @else
                <h1 style="font-size: 22px; font-weight: 800; color: #1d232a; margin: 0 0 16px 0; line-height: 1.3;">@lang('Hello!')</h1>
                @endif
            @endif

            {{-- Intro Lines --}}
            @foreach ($introLines as $line)
            <p style="font-size: 15px; color: #4b5563; line-height: 1.7; margin: 0 0 12px 0;">{{ $line }}</p>
            @endforeach

            {{-- Action Button --}}
            @isset($actionText)
            <?php
                $color = match ($level) {
                    'success' => '#16a34a',
                    'error'   => '#e11d48',
                    default   => '#570DF8',
                };
                $shadow = match ($level) {
                    'success' => 'rgba(22,163,74,0.25)',
                    'error'   => 'rgba(225,29,72,0.25)',
                    default   => 'rgba(87,13,248,0.25)',
                };
            ?>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
                <tr>
                    <td align="center">
                        <a href="{{ $actionUrl }}"
                           style="
                               display: inline-block;
                               background: {{ $color }};
                               color: #ffffff;
                               font-size: 14px;
                               font-weight: 700;
                               text-decoration: none;
                               padding: 13px 32px;
                               border-radius: 8px;
                               letter-spacing: 0.02em;
                               box-shadow: 0 4px 14px {{ $shadow }};
                           "
                        >
                            {{ $actionText }} &rarr;
                        </a>
                    </td>
                </tr>
            </table>
            @endisset

            {{-- Outro Lines --}}
            @foreach ($outroLines as $line)
            <p style="font-size: 15px; color: #4b5563; line-height: 1.7; margin: 0 0 12px 0;">{{ $line }}</p>
            @endforeach

            {{-- Salutation --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 28px; border-top: 1px solid #f3f4f6; padding-top: 20px;">
                <tr>
                    <td>
                        @if (! empty($salutation))
                        <p style="font-size: 14px; color: #6b7280; margin: 0;">{{ $salutation }}</p>
                        @else
                        <p style="font-size: 14px; color: #6b7280; margin: 0;">
                            @lang('Regards,')
                        </p>
                        <p style="font-size: 15px; font-weight: 700; color: #570DF8; margin: 4px 0 0 0;">{{ config('app.name') }}</p>
                        @endif
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>

{{-- Subcopy --}}
@isset($actionText)
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px;">
    <tr>
        <td style="
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
        ">
            <p style="font-size: 12px; color: #9ca3af; margin: 0 0 6px 0; line-height: 1.6;">
                @lang("If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your web browser:", ['actionText' => $actionText])
            </p>
            <p style="font-size: 11px; color: #570DF8; word-break: break-all; margin: 0;">
                <a href="{{ $actionUrl }}" style="color: #570DF8; text-decoration: none;">{{ $displayableActionUrl }}</a>
            </p>
        </td>
    </tr>
</table>
@endisset

<p style="font-size: 12px; color: #d1d5db; text-align: center; margin: 0;">
    &copy; {{ date('Y') }} TimeTrack Pro &mdash; Overtime Tracker System. All rights reserved.
</p>

</x-mail::footer>
</x-slot:footer>
</x-mail::layout>