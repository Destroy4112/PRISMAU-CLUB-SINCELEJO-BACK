<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PagoEmail extends Mailable
{
   use Queueable, SerializesModels;

   public function __construct(
      public readonly string $estado,
      public readonly string $periodo,
      public readonly float $monto
   ) {}

   public function envelope(): Envelope
   {
      return new Envelope(
         from: new Address(
            'clubsincelejo.prismau@gmail.com',
            'Club Sincelejo'
         ),
         subject: 'Notificación de estado del pago'
      );
   }

   public function content(): Content
   {
      return new Content(
         view: 'pagosEmail',
         with: [
            'fecha'   => now()->format('d/m/Y'),
            'estado'  => $this->estado,
            'periodo' => $this->periodo,
            'monto'   => $this->monto,
         ]
      );
   }

   public function attachments(): array
   {
      return [];
   }
}
