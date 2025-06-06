<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\OAuth2Service;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Github;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;
use League\OAuth2\Client\Provider\Google;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher,
        private ?string $additional_role = null
    )
    {
    }

    /**
     * Register user with form on website
     *
     * @param Request $request
     * @param Security $security
     * @return Response
     */
    public function register(Request $request, Security $security): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $this->saveUser($user, $plainPassword);

            return $security->login($user);
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Register user via OAuth2 access
     *
     * @param Request $request
     * @param Security $security
     * @param string $service
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function oauth2(Request $request, Security $security, string $service): Response
    {
        /** @var Session $session */
        $session = $request->getSession();

        $provider = $this->loadOAuth2Data($service);

        // Got an error, probably user denied access
        if (!empty($request->get('error'))) {
            exit('Got error: ' . htmlspecialchars($request->get('error'), ENT_QUOTES, 'UTF-8'));
        }
        // If we don't have an authorization code then get one
        elseif (!$request->get('code')) {
            $authUrl = $provider->getAuthorizationUrl();
            $session->set('oauth2state', $provider->getState());
            header('Location: ' . $authUrl);
            exit;
        }
        // Check given state against previously stored one to mitigate CSRF attack
        elseif (empty($request->get('state')) || ($request->get('state') !== $session->get('oauth2state'))) {
            $session->remove('oauth2state');
            exit('Invalid state');
        }
        // Try to get an access token (using the authorization code grant)
        else {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $request->get('code')
            ]);

            try {
                // get email from user account
                $ownerDetails = $provider->getResourceOwner($token);
                $email = $ownerDetails->getEmail();

                // check if the user is already registered
                $user = $this->entityManager->getRepository(User::class)->findOneBy([
                    'email' => $email
                ]);

                // no user found, use email to register
                if (!$user) {
                    $password = ByteString::fromRandom(32);

                    $user = new User();
                    $this->saveUser($user, $password, $email);
                }

                $this->entityManager->getRepository(User::class)->updateUserLogin($user);

                // login user
                return $security->login($user);
            }
            catch (Exception $e) {
                exit('Something went wrong: ' . $e->getMessage());
            }
        }
    }

    /**
     * Returns the OAuth2 service provider
     *
     * @param $service
     * @return AbstractProvider
     */
    private function loadOAuth2Data($service): AbstractProvider
    {
        // GitHub OAuth2
        if ($service === OAuth2Service::GitHub->value) {
            $this->additional_role = 'ROLE_' . strtoupper($service);

            return new Github([
                'clientId' => 'Ov23licUpQEg2lXRnaQZ',
                'clientSecret' => $this->getParameter('app.github_client_secret'),
                'redirectUri' => 'http://localhost/travel/public/register/oauth2/github',
            ]);
        }
        // Google OAuth2
        elseif ($service === OAuth2Service::Google->value) {
            $this->additional_role = 'ROLE_' . strtoupper($service);

            return new Google([
                'clientId' => '101270530173-oa9n91b0jvc470khpb3cg9gd4a3mj3eb.apps.googleusercontent.com',
                'clientSecret' => $this->getParameter('app.google_client_secret'),
                'redirectUri' => 'http://localhost/travel/public/register/oauth2/google'
            ]);
        }
        else {
            exit('Unsupported OAuth2 service requested!');
        }
    }

    /**
     * Save user to database
     *
     * @param User $user
     * @param string $password
     * @param string|null $email
     * @return void
     */
    private function saveUser(User $user, string $password, ?string $email = null): void
    {
        if ($email) {
            $user->setEmail($email);
        }

        $roles = ['ROLE_USER'];
        if ($this->additional_role) {
            $roles[] = $this->additional_role;
            $this->additional_role = null;
        }

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
        $user->setRoles($roles);
        $user->setCreated(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
