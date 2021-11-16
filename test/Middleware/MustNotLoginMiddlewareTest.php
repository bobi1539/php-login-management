<?php
namespace ProgrammerZamanNow\Belajar\PHP\MVC\Middleware {

    require_once __DIR__ . '/../Helper/helper.php';

    use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;

    use PHPUnit\Framework\TestCase;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\Session;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Service\SessionService;

    class MustNotLoginMiddlewareTest extends TestCase
    {

        private MustNotLoginMiddleware $middleware;
        private SessionRepository $sessionRepository;
        private UserRepository $userRepository;

        protected function setUp(): void
        {
            $this->middleware = new MustNotLoginMiddleware();
            $this->userRepository = new UserRepository(Database::getConnection());
            $this->sessionRepository = new SessionRepository(Database::getConnection());

            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();



            putenv("mode=test");
        }

        public function testBeforeGuest()
        {
            $this->middleware->before();

            $this->expectOutputString("");
        }

        public function testBeforeLoginUser()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("bobi", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;


            $this->middleware->before();


            $this->expectOutputRegex("[Location: /]");
        }
    }
}
