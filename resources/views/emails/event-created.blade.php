<x-emails.email-layout heading="¡Evento creado exitosamente!" subheading="Tu evento ya está listo para recibir asistencias">

    <p style="margin:0 0 20px;color:#3f3f46;font-size:15px;line-height:1.6;">
        Hola <strong>{{ $user->name }}</strong>, tu evento ha sido creado correctamente.
    </p>

    {{-- Detalles del evento --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e4e4e7;border-radius:12px;overflow:hidden;margin-bottom:24px;">
        <tr>
            <td style="padding:20px;">
                <h2 style="margin:0 0 16px;color:#18181b;font-size:18px;font-weight:700;">
                    {{ $event->title }}
                </h2>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    @php
                        $details = [
                            ['📅', 'Fecha', \Carbon\Carbon::parse($event->date)->isoFormat('dddd, D [de] MMMM [de] YYYY')],
                            ['🕐', 'Hora', \Carbon\Carbon::parse($event->start_time)->format('h:i A') . ' — ' . \Carbon\Carbon::parse($event->end_time)->format('h:i A')],
                            ['📍', 'Lugar', $event->location],
                        ];
                        if ($event->dependency) {
                            $details[] = ['🏢', 'Dependencia', $event->dependency->name];
                        }
                        if ($event->area) {
                            $details[] = ['📂', 'Área', $event->area->name];
                        }
                        if ($event->description) {
                            $details[] = ['📝', 'Descripción', $event->description];
                        }
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

    {{-- Botones --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
        <tr>
            <td align="center" style="padding-bottom:12px;">
                <a href="{{ $eventLink }}"
                   style="display:inline-block;background:linear-gradient(135deg,#cc5e50,#b84a3d);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:10px;font-size:14px;font-weight:700;">
                    🔗 Enlace de asistencia
                </a>
            </td>
        </tr>
        <tr>
            <td align="center">
                <a href="{{ $showLink }}"
                   style="display:inline-block;background:#f4f4f5;color:#52525b;text-decoration:none;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid #e4e4e7;">
                    Ver detalles del evento
                </a>
            </td>
        </tr>
    </table>

    {{-- Enlaces como texto plano --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;background-color:#f9fafb;border:1px solid #e4e4e7;border-radius:8px;overflow:hidden;">
        <tr>
            <td style="padding:12px 16px;">
                <p style="margin:0 0 6px;color:#71717a;font-size:11px;font-weight:600;text-transform:uppercase;">Enlace de asistencia</p>
                <p style="margin:0 0 12px;word-break:break-all;font-size:12px;">
                    <a href="{{ $eventLink }}" style="color:#cc5e50;text-decoration:none;">{{ $eventLink }}</a>
                </p>
                <p style="margin:0 0 6px;color:#71717a;font-size:11px;font-weight:600;text-transform:uppercase;">Detalles del evento</p>
                <p style="margin:0;word-break:break-all;font-size:12px;">
                    <a href="{{ $showLink }}" style="color:#62a9b6;text-decoration:none;">{{ $showLink }}</a>
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0;color:#a1a1aa;font-size:12px;text-align:center;line-height:1.5;">
        Comparte el enlace de asistencia con los participantes para que registren su asistencia al evento.
    </p>

</x-emails.email-layout>