<?php

namespace App\Mail;

use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VentaNotificacionMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $venta;
    public $tipo; // 'cliente', 'empleado', 'admin'

    public function __construct(Venta $venta, $tipo)
    {
        $this->venta = $venta;
        $this->tipo = $tipo;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('REPORTE DE OPERACIÓN - Olize Constructora')
                    ->view('emails.venta-creada')
                    ->with([
                        'venta' => $this->venta,
                        'tipo' => $this->tipo,
                    ]);
    }
}