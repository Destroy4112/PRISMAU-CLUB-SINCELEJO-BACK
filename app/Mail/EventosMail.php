<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EventosMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $titulo;
    public $descripcion;

    /**
     * Create a new message instance.
     */
    public function __construct($titulo, $descripcion)
    {
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
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
            view: 'eventosEmail',
            with: [
                'fecha' => now()->format('d/m/Y'),
                'rol' => 'Socio(a)',
                'titulo' => $this->titulo,
                'descripcion' => $this->descripcion,
                'fechaEvento' => "15 de agosto de 2025",
                'horaEvento' => "9:00 a.m.",
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
                'estado' => $this->titulo,
                'descripcion' => $this->descripcion,
            ]);
    }
}
