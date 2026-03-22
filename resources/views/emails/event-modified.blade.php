<x-emails.email-layout heading="Evento modificado" subheading="Se han realizado cambios en uno de tus eventos">

    <p style="margin:0 0 20px;color:#3f3f46;font-size:15px;line-height:1.6;">
        Hola <strong>{{ $user->name }}</strong>, tu evento ha sido actualizado.
    </p>

    {{-- Cambios realizados --}}
    @if(!empty($changes))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fefce8;border:1px solid #fde68a;border-radius:12px;overflow:hidden;margin-bottom:20px;">
            <tr>
                <td style="padding:16px 20px;">
                    <p style="margin:0 0 12px;color:#92400e;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                        ⚡ Cambios realizados
                    </p>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        @php
                            $labels = [
                                'title'         => 'Título',
                                'description'   => 'Descripción',
                                'date'          => 'Fecha',
                                'start_time'    => 'Hora de inicio',
                                'end_time'      => 'Hora de fin',
                                'location'      => 'Ubicación',
                                'dependency_id' => 'Dependencia',
                                'area_id'       => 'Área',
                            ];
                        @endphp
                        @foreach($changes as $field => $change)
                            <tr>
                                <td style="padding:4px 0;color:#78716c;font-size:12px;width:100px;vertical-align:top;">
                                    {{ $labels[$field] ?? $field }}
                                </td>
                                <td style="padding:4px 0;font-size:12px;">
                                    <span style="color:#991b1b;text-decoration:line-through;">{{ $change['old'] ?? '—' }}</span>
                                    &nbsp;→&nbsp;
                                    <span style="color:#166534;font-weight:600;">{{ $change['new'] ?? '—' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    @endif

    {{-- Datos actuales del evento --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e4e4e7;border-radius:12px;overflow:hidden;margin-bottom:24px;">
        <tr>
            <td style="padding:20px;">
                <p style="margin:0 0 12px;color:#71717a;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                    Datos actuales del evento
                </p>
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

    {{-- Botón --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <a href="{{ $showLink }}"
                   style="display:inline-block;background:linear-gradient(135deg,#62a9b6,#4d94a0);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:10px;font-size:14px;font-weight:700;">
                    Ver evento actualizado
                </a>
            </td>
        </tr>
    </table>

    {{-- Enlace como texto plano --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;background-color:#f9fafb;border:1px solid #e4e4e7;border-radius:8px;overflow:hidden;">
        <tr>
            <td style="padding:12px 16px;">
                <p style="margin:0 0 6px;color:#71717a;font-size:11px;font-weight:600;text-transform:uppercase;">Ver evento actualizado</p>
                <p style="margin:0;word-break:break-all;font-size:12px;">
                    <a href="{{ $showLink }}" style="color:#62a9b6;text-decoration:none;">{{ $showLink }}</a>
                </p>
            </td>
        </tr>
    </table>

</x-emails.email-layout>