<?php

namespace App\Notifications;

use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VentaCreadaEmpleado extends Notification
{
    use Queueable;

    protected $venta;

    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nueva OPERACIÓN REGISTRADA - Constructora A&C')
            ->view('emails.venta-creada', [
                'venta' => $this->venta,
                'tipo' => 'empleado'
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'venta_id' => $this->venta->id_venta,
        ];
    }
}