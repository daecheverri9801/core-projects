<?php

namespace App\Notifications;

use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VentaCreadaAdministrativo extends Notification
{
    use Queueable;

    protected Venta $venta;

    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva OPERACIÓN REGISTRADA - Constructora A&C')
            ->view('emails.venta-creada', [
                'venta' => $this->venta,
                'tipo'  => 'administrativo',
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'venta_id' => $this->venta->id_venta,
        ];
    }
}