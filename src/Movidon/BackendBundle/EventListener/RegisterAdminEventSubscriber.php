<?php

namespace Movidon\BackendBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Movidon\BackendBundle\Entity\AdminUser;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class RegisterAdminEventSubscriber implements EventSubscriber
{
    private $encoderFactory;

    public function __construct(EncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function getSubscribedEvents()
    {
        return array('prePersist');
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if(!$entity instanceof AdminUser) {
            return;
        }
        $this->createSalt($entity);
    }

    private function createSalt(AdminUser $user)
    {
        if (!$user->getSalt()) {
            $user->setSalt(md5(time() . AdminUser::AUTH_SALT));
            $encoder = $this->encoderFactory->getEncoder($user);
            $passwordEncoded = $encoder->encodePassword($user->getPassword(), $user->getSalt());
            $user->setPassword($passwordEncoded);
        }
    }
}