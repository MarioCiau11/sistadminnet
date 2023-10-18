<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .header {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }

        .content {
            padding: 20px;
        }

        .signature {
            padding-top: 30px;
            text-align: center;
            border-top: 1px solid #ccc;
            margin-top: 20px;
        }

        .contact-info {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
        }

        .contact-info p {
            margin: 0;
        }

        .contact-info strong {
            font-weight: bold;
        }

        .contact-info a {
            color: #007BFF;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Cotización</h2>
            <p><strong>Estimado/a 
                @if ($referencia !== null)
                {{ $referencia }}:
                @else
                {{ $cliente }}:
                @endif
                </strong></p>
            <p>Se anexa la cotización de nuestros productos o servicios que amablemente solicitó.</p>
            <p>Cualquier duda o aclaración puede comunicarse con nosotros.</p>
        </div>
        <div class="content">
            <!-- Aquí va el contenido de la cotización -->
        </div>
        <div class="signature">
            <p><strong>Atentamente,</strong></p>
            <p><strong>{{ $agente }}</strong></p>
        </div>
        <div class="contact-info">
            <p><strong>{{ session('company')->companies_name }}</strong></p>
            <p>{{ session('company')->companies_suburb . ' ' . session('company')->companies_cp . ' ' . session('company')->companies_city . ' ' . session('company')->companies_country }}</p>
            <p><strong>Teléfono:</strong> {{ session('company')->companies_phone1 }}</p>
            <p><strong>Celular:</strong> {{ session('company')->companies_phone2 }}</p>
            <p><strong>Celular:</strong> {{ session('company')->companies_phone3 }}</p>
            <p><strong>Correo:</strong> <a href="mailto:{{ session('company')->companies_mail }}">{{ session('company')->companies_mail }}</a></p>
        </div>
    </div>
</body>

</html>
