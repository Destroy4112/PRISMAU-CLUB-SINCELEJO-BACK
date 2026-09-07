<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EstadosMail extends Mailable
{
    use Queueable, SerializesModels;

    public $estadoString;
    public $motivo;

    /**
     * Create a new message instance.
     */
    public function __construct($estadoString, $motivo)
    {
        $this->estadoString = $estadoString;
        $this->motivo = $motivo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notificaciones Club Sincelejo',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'estadosEmail',
            with: [
                'fecha' => now()->format('d/m/Y'),
                'estado' => $this->estadoString,
                'motivo' => $this->motivo
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject('Notificación de Estado')
            ->from('clubsincelejo.prismau@gmail.com', 'Club Sincelejo')
            ->view('estadosEmail')
            ->with([
                'fecha' => now()->format('d/m/Y'),
                'estado' => $this->estadoString,
                'motivo' => $this->motivo,
            ]);
    }
}
