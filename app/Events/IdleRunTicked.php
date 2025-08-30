<?php

namespace App\Events;

use App\Models\IdleRun;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class IdleRunTicked implements ShouldBroadcastNow
{
    public function __construct(public array $payload) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('idle.run'.$this->payload['id']);
    }
    public function broadcastAs(): string
    {
        return 'IdleRunTicked';
    }
}
