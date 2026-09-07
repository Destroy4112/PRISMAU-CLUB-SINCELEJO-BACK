<?php

namespace App\Jobs;

use Exception;
use App\Models\User;
use App\Models\Noticia;
use App\Notifications\NotificacionExpo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionNoticia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $rolId,
        protected int $noticiaId
    ) {}

    public function handle(): void
    {
        try {
            $noticia = Noticia::find($this->noticiaId);
            if (!$noticia) {
                Log::warning("⚠️ Noticia {$this->noticiaId} no encontrada");
                return;
            }
            User::where('Rol', $this->rolId)
                ->whereHas('expoTokens', fn($q) => $q->where('enabled', true))
                ->chunk(100, function ($usuarios) use ($noticia) {
                    foreach ($usuarios as $u) {
                        $u->notify(new NotificacionExpo($noticia->Titulo));
                        usleep(50000); // suaviza picos
                    }
                });
        } catch (Exception $e) {
            Log::error('Error enviando notificación:', ['message' => $e->getMessage()]);
        }
    }
}
