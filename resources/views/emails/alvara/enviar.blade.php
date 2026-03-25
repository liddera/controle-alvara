<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #2c5282;">Olá, {{ $destinatarioNome }}!</h2>
    
    <p>Segue em anexo o documento referente ao alvará da empresa <strong>{{ $alvara->empresa->nome }}</strong>.</p>
    
    <div style="background-color: #f7fafc; padding: 15px; border-left: 4px solid #4a5568; margin: 20px 0;">
        <h4 style="margin-top: 0; margin-bottom: 10px; color: #4a5568;">Detalhes do Alvará:</h4>
        <ul style="margin: 0; padding-left: 20px;">
            <li><strong>Tipo:</strong> {{ $alvara->tipoAlvara?->nome ?? $alvara->tipo }}</li>
            @if($alvara->numero)
                <li><strong>Número/Protocolo:</strong> {{ $alvara->numero }}</li>
            @endif
            <li><strong>Data de Vencimento:</strong> {{ $alvara->data_vencimento->format('d/m/Y') }}</li>
        </ul>
    </div>

    @if($mensagemPersonalizada)
        <div style="margin: 20px 0; padding: 15px; border: 1px solid #e2e8f0; border-radius: 5px;">
            <strong>Mensagem:</strong><br>
            {!! nl2br(e($mensagemPersonalizada)) !!}
        </div>
    @endif

    <p>Os documentos estão anexados a este email.</p>

    <p style="margin-top: 30px; font-size: 0.9em; color: #718096;">
        Atenciosamente,<br>
        Equipe {{ config('app.name') }}
    </p>
</body>
</html>
