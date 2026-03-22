<x-emails.email-layout heading="¡Asistencia registrada!" subheading="Tu participación ha sido confirmada">

    <p style="margin:0 0 20px;color:#3f3f46;font-size:15px;line-height:1.6;">
        Hola <strong>{{ $participant->first_name }} {{ $participant->last_name }}</strong>,
        tu asistencia ha sido registrada correctamente.
    </p>

    {{-- Detalles del evento --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e4e4e7;border-radius:12px;overflow:hidden;margin-bottom:20px;">
        <tr>
            <td style="padding:20px;">
                <p style="margin:0 0 12px;color:#71717a;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                    Evento
                </p>
                <h2 style="margin:0 0 16px;color:#18181b;font-size:18px;font-weight:700;">
                    {{ $event->title }}
                </h2>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    @php
                        $details = [
                            ['📅', 'Fecha', \Carbon\Carbon::parse($event->date)->isoFormat('dddd, D [de] MMMM [de] YYYY')],
                            ['🕐', 'Hora', \Carbon\Carbon::parse($event->start_time)->format('h:i A') . ' — ' . \Carbon\Carbon::parse($event->end_time)->format('h:i A')],
                            ['📍', 'Lugar', $event->location ?? '—'],
                        ];
                    @endphp

                    @foreach($details as [$icon, $label, $value])
                        <tr>
                            <td style="padding:6px 0;color:#71717a;font-size:13px;width:110px;vertical-align:top;">
                                {{ $icon }} {{ $label }}
                            </td>
                            <td style="padding:6px 0;color:#27272a;font-size:13px;font-weight:500;">
                                {{ $value }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    {{-- Detalles del registro --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;overflow:hidden;margin-bottom:24px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 10px;color:#065f46;font-size:13px;font-weight:700;">
                    ✅ Registro confirmado
                </p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:4px 0;color:#047857;font-size:12px;width:110px;">Documento</td>
                        <td style="padding:4px 0;color:#065f46;font-size:12px;font-weight:600;">{{ $participant->document }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:#047857;font-size:12px;width:110px;">Registrado a las</td>
                        <td style="padding:4px 0;color:#065f46;font-size:12px;font-weight:600;">{{ $attendance->created_at->format('h:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:#047857;font-size:12px;width:110px;">Fecha de registro</td>
                        <td style="padding:4px 0;color:#065f46;font-size:12px;font-weight:600;">{{ $attendance->created_at->isoFormat('D [de] MMMM [de] YYYY') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin:0;color:#a1a1aa;font-size:12px;text-align:center;line-height:1.5;">
        Guarda este correo como comprobante de tu asistencia.
    </p>

</x-emails.email-layout>