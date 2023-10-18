<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnviarCotizacion extends Mailable
{
    use Queueable, SerializesModels;
    protected $pdfContent, $id, $nombre, $referencia, $agente, $empresa;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pdfContent, $id, $nombre, $referencia, $agente, $empresa)
    {
        $this->pdfContent = $pdfContent;
        $this->id = $id;
        $this->nombre = $nombre;
        $this->referencia = $referencia;
        $this->agente = $agente;
        $this->empresa = $empresa;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('page.modulos.comercial.mail.cotizacion')
        ->subject($this->empresa . ' - Cotización # ' . $this->id)
        ->attachData($this->pdfContent, 'cotización.pdf', [
            'mime' => 'application/pdf',
        ])
            ->with([
                'cliente' => $this->nombre,
                'referencia' => $this->referencia,
                'agente' => $this->agente,
                'empresa' => $this->empresa,
            ]);
    }
}
