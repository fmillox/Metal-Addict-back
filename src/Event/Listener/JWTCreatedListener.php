<?php

namespace App\Event\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    /**
     * Replaces the data in the generated
     *
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();
        $payload = $event->getData();
        $payload['id'] = $user->getId();
        $payload['nickname'] = $user->getNickname();
        $payload['biography'] = $user->getBiography();
        $payload['avatar'] = $user->getAvatar();

        $event->setData($payload);
    }
}