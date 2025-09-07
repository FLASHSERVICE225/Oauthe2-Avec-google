<?php
namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $em
    ) {}

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?\Symfony\Component\HttpFoundation\Response
    {
        // Redirect to homepage or any other page
        return new \Symfony\Component\HttpFoundation\RedirectResponse('/');
    }

    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception): ?\Symfony\Component\HttpFoundation\Response
    {
        // Redirect to login page or show error
        return new \Symfony\Component\HttpFoundation\RedirectResponse('/login');
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient('google');
        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUser();

        $email = $googleUser->getEmail();
        $name = $googleUser->getName();          // nom complet
        $avatar = $googleUser->getAvatar();      // URL de la photo

        return new SelfValidatingPassport(
            new UserBadge($email, function () use ($email, $name, $avatar) {
                $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setFullName($name);   // Assure-toi d'avoir un champ fullName dans User
                    $user->setAvatar($avatar);   // Assure-toi d'avoir un champ avatar dans User
                    $user->setPassword('');      // inutile pour OAuth
                    $this->em->persist($user);
                    $this->em->flush();
                }

                return $user;
            })
        );
    }

    
}
