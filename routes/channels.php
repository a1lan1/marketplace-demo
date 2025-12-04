<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', fn (User $user, int $id): bool => $user->id === $id);

Broadcast::channel('orders.{order}', fn (User $user, Order $order) => $user->can('view', $order));

Broadcast::channel('chat.{order}', fn (User $user, Order $order) => $user->can('viewChat', $order));
