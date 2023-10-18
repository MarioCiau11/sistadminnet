<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class EnviarCorreo extends Mailable
{
    use Queueable, SerializesModels;
    protected $ruta_xml, $ruta_pdf, $nombre , $fecha, $movimiento;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ruta_xml, $ruta_pdf, $nombre, $fecha, $movimiento)
    {
        $this->ruta_xml = $ruta_xml;
        $this->ruta_pdf = $ruta_pdf;
        $this->nombre = $nombre;
        $this->fecha = $fecha;
        $this->movimiento = $movimiento;
    }
    

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('page.modulos.comercial.mail.notificacion')
            ->subject('Comprobante de Facturación')
            ->attachFromStorageDisk('empresas', $this->ruta_xml)
            ->attachFromStorageDisk('empresas', $this->ruta_pdf)
            ->with([
                'cliente' => $this->nombre,
                'fecha' => $this->fecha,
                'movimiento' => $this->movimiento,
                'logo' => $this->obtenerLogo(),
            ]);
    }

    public function obtenerLogo()
    {
        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = Storage::disk('empresas')->get(session('company')->companies_logo);
        }


        if ($logoFile == null) {
            $logoFile = Storage::disk('empresas')->get('default.png');

            if ($logoFile == null) {
                $logoBase64 = '';
            } else {
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
            }
        } else {
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
        }

        return $logoBase64;
    }


}
