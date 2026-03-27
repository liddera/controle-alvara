<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: 'Helvetica Neue', Arial, sans-serif; line-height: 1.7; color: #1f2937; max-width: 680px; margin: 0 auto; padding: 28px; background: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background: #ffffff; border-radius: 16px; box-shadow: 0 10px 40px rgba(31,41,55,0.08); padding: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 6px 0; font-size: 14px; letter-spacing: 0.04em; text-transform: uppercase; color: #6b7280;">Aviso de Alvará</p>
                <h2 style="margin: 0 0 12px 0; color: #0f172a; font-size: 24px;">Olá, {{ $destinatarioNome }}!</h2>
                <p style="margin: 0 0 14px 0; font-size: 15px; color: #334155;">
                    Confira abaixo os dados do alvará da empresa <strong>{{ $alvara->empresa->nome }}</strong>.
                </p>

                <div style="margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                    <div style="background: #0ea5e9; color: #f8fafc; padding: 12px 16px; font-weight: 700; font-size: 14px;">
                        Detalhes do Alvará
                    </div>
                    <div style="padding: 16px; background: #f8fafc;">
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="font-size: 14px; color: #0f172a;">
                            <tr>
                                <td style="padding: 6px 0; width: 36%;">Tipo</td>
                                <td style="padding: 6px 0; font-weight: 600;">{{ $alvara->tipoAlvara?->nome ?? $alvara->tipo }}</td>
                            </tr>
                            @if($alvara->numero)
                            <tr>
                                <td style="padding: 6px 0;">Número/Protocolo</td>
                                <td style="padding: 6px 0; font-weight: 600;">{{ $alvara->numero }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding: 6px 0;">Data de Vencimento</td>
                                <td style="padding: 6px 0;">
                                    <span style="display:inline-block; padding:6px 10px; border-radius:10px; background:#fef3c7; color:#92400e; font-weight:700;">
                                        {{ $alvara->data_vencimento->format('d/m/Y') }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($alvara->documentos->isNotEmpty())
                    <div style="margin: 18px 0 22px 0;">
                        <p style="margin: 0 0 12px 0; font-size: 14px; color: #334155;">
                            Baixe o documento pelo botão abaixo:
                        </p>

                        @foreach($alvara->documentos as $documento)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($documento->caminho) }}"
                                target="_blank"
                                style="display: inline-block; margin: 0 10px 10px 0; padding: 12px 18px; border-radius: 12px; background: #0ea5e9; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700;">
                                {{ $alvara->documentos->count() > 1 ? 'Baixar ' . $documento->nome_arquivo : 'Baixar documento' }}
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($mensagemPersonalizada)
                    <div style="margin: 18px 0; padding: 14px 16px; border-left: 4px solid #0ea5e9; background: #f0f9ff; border-radius: 10px;">
                        <p style="margin: 0 0 6px 0; font-weight: 700; color: #0f172a;">Mensagem adicional</p>
                        <p style="margin: 0; color: #334155; white-space: pre-line;">{!! nl2br(e($mensagemPersonalizada)) !!}</p>
                    </div>
                @endif

                <p style="margin: 24px 0 0 0; font-size: 13px; color: #6b7280; line-height: 1.5;">
                    Atenciosamente,<br>
                    Equipe {{ config('app.name') }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
